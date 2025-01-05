<?php


namespace mcms\mcms\payments\components\autopayments;


use DateTime;
use mcms\common\helpers\ArrayHelper;
use mcms\payments\components\UserBalance;
use mcms\payments\models\UserBalanceInvoice;
use mcms\payments\models\UserPayment;
use mcms\payments\models\UserPaymentForm;
use mcms\payments\models\UserPaymentSetting;
use mcms\payments\models\UserWallet;
use mcms\user\models\User;
use mcms\payments\Module as PaymentsModule;
use Yii;
use yii\base\Exception;
use yii\helpers\Json;

/**
 * Компонент для создания автовыплаты на партнера
 */
class Partner
{
  /** @var User */
  private $_partner;
  /** @var UserPaymentSetting */
  private $_paymentSetting;
  /** @var UserWallet */
  private $_autopayWallet;
  /** @var DateTime[] */
  private $_autopayDates;
  /** @var float */
  private $_paymentSum;
  /** @var string */
  private $_log;

  /**
   * Partner constructor.
   * @param User $partner
   */
  public function __construct(User $partner)
  {
    $this->_partner = $partner;
    $this->_paymentSetting = UserPaymentSetting::fetch($partner->id);
    // TRICKY: кошелек должен быть не только помеченым для автовыплат, но и в текущей валюте партнера
    $this->_autopayWallet = $partner->getUserAutopayWallet()
      ->andWhere(['currency' => $this->_paymentSetting->getCurrentCurrency()])->one();
  }

  /**
   * Если разрешено создание выплат, автовыплату не создаем
   * Если отсутствует кошелек для автовыплат, автовыплату не создаем
   * Если сегодня не день для автовыплат партнера, автовыплату не создаем
   *
   * @return bool
   * @throws Exception
   * @throws \mcms\payments\components\exceptions\UserBalanceException
   */
  public function validate()
  {
    if ($this->_paymentSetting->isPaymentsEnabled()) {
      $this->_log = 'Payment creating is enabled';
      return false;
    }
    if ($this->_autopayWallet === null) {
      $this->_log = 'Wallet to autopayments is missing';
      return false;
    }
    if ($this->_partner->getInvoicingCycle() === PaymentsModule::SETTING_DEFAULT_INVOICING_CYCLE_OFF) {
      $this->_log = 'Invoicing cycle off';
      return false;
    }
    if ($this->getPaymentSum() <= 0) {
      $this->_log = 'There is no money :( Current balance is ' .
        $this->getPaymentSum() . ' ' .
        $this->_paymentSetting->getCurrentCurrency();
      return false;
    }
    return true;
  }

  /**
   * @return bool
   * @throws Exception
   * @throws \mcms\payments\components\exceptions\UserBalanceException
   */
  public function createPayment()
  {
    if (!$this->validate()) {
      return false;
    }

    $model = new UserPaymentForm(['scenario' => UserPayment::SCENARIO_AUTOPAYOUT]);
    $model->user_id = $this->_partner->id;
    $model->user_wallet_id = $this->_autopayWallet->id;
    $model->invoice_amount = $this->getPaymentSum();
    $model->invoiceType = UserBalanceInvoice::TYPE_PAYMENT;

    $model->from_date = $this->getAutopayDates()['from'];
    $model->to_date = $this->getAutopayDates()['to'];
    $model->currency = $this->_autopayWallet->currency;

    if (!$model->save()) {
      $this->_log = Json::encode($model->getErrors());
      return false;
    }
    $this->_log = 'Payment successfully created';

    return true;
  }

  /**
   * @return string
   */
  public function getLog()
  {
    return $this->_log ? 'Partner #' . $this->_partner->id . '. ' . $this->_log : '';
  }

  /**
   * Сумма для выплаты
   * TRICKY: может быть отрицательной или 0
   * @return float
   * @throws \mcms\payments\components\exceptions\UserBalanceException
   * @throws Exception
   */
  protected function getPaymentSum()
  {
    if ($this->_paymentSum !== null) {
      return $this->_paymentSum;
    }

    $balance = new UserBalance(['userId' => $this->_partner->id]);

    $allBalance = $balance->getMain(false);
    $this->_paymentSum = $balance->getMain(false, $this->getAutopayDates()['from'], $this->getAutopayDates()['to']);
    // TRICKY: Если были выплаты > баланса до указанного промежутка, может быть такой сценарий
    // Некоторым партнерам делаются выплаты даже при отрицательном балансе
    if ($this->_paymentSum > $allBalance) {
      $this->_paymentSum = $allBalance;
    }

    return $this->_paymentSum;
  }

  /**
   * @return DateTime[]
   * @throws Exception
   */
  protected function getAutopayDates()
  {
    if ($this->_autopayDates !== null) {
      return $this->_autopayDates;
    }
    $today = (int)Yii::$app->formatter->asDate('now', 'php:j');
    $currentMonth = Yii::$app->formatter->asDate('now', 'php:Y-m');
    $lastMonth = Yii::$app->formatter->asDate('last day of last month', 'php:Y-m');
    $lastDayOfLastMonth = Yii::$app->formatter->asDate('last day of last month', 'php:Y-m-d');

    switch ($this->_partner->getInvoicingCycle()) {
      case PaymentsModule::SETTING_DEFAULT_INVOICING_CYCLE_MONTHLY:
        $this->_autopayDates = [
          'from' => Yii::$app->formatter->asDate('first day of last month', 'php:Y-m-d'),
          'to' => $lastDayOfLastMonth
        ];
        return $this->_autopayDates;

      case PaymentsModule::SETTING_DEFAULT_INVOICING_CYCLE_BIWEEKLY:
        $this->_autopayDates = [
          'from' => $today > 15 ? $currentMonth . '-01' : $lastMonth . '-16',
          'to' => $today > 15 ? $currentMonth . '-15' : $lastDayOfLastMonth,
        ];
        return $this->_autopayDates;

      case PaymentsModule::SETTING_DEFAULT_INVOICING_CYCLE_WEEKLY:
        if ($today > 21) {
          $this->_autopayDates = [
            'from' => $currentMonth . '-15',
            'to' => $currentMonth . '-21',
          ];
          return $this->_autopayDates;
        }
        if ($today > 14) {
          $this->_autopayDates = [
            'from' => $currentMonth . '-08',
            'to' => $currentMonth . '-14',
          ];
          return $this->_autopayDates;
        }
        if ($today > 7) {
          $this->_autopayDates = [
            'from' => $currentMonth . '-01',
            'to' => $currentMonth . '-07',
          ];
          return $this->_autopayDates;
        }
        $this->_autopayDates = [
          'from' => $lastMonth . '-22',
          'to' => $lastDayOfLastMonth,
        ];
        return $this->_autopayDates;
      default:
        // По идее, сюда не должно заходить никогда
        // т.к. партнеры без диапазона (или с отключенной настройкой) не пройдут валидацию
        throw new Exception('Партнер ' . $this->_partner->id . ' не имеет корректный диапазон автовыплат');
    }
  }

  /**
   * @return bool
   */
  protected function isLastDayOfMonth()
  {
    return Yii::$app->formatter->asDate('last day of this month', 'php:Y-m-d') === Yii::$app->formatter->asDate('now', 'php:Y-m-d');
  }
}