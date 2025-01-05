<?php

namespace mcms\mcms\payments\components;

use mcms\currency\components\PartnerCurrenciesProvider;
use mcms\payments\components\UserBalance;
use mcms\payments\models\PartnerPaymentSettings;
use mcms\payments\models\UserBalanceInvoice;
use mcms\payments\models\UserPayment;
use mcms\payments\models\UserPaymentForm;
use mcms\payments\models\UserWallet;
use mcms\user\models\User;
use Yii;
use yii\base\BaseObject;
use yii\console\Exception;
use yii\helpers\Console;
use yii\helpers\Json;
use yii\helpers\VarDumper;

class AutoPaymentGenerator extends BaseObject
{
  /** @var PartnerPaymentSettings */
  private $_partnerPaymentSettings;
  /** @var User */
  private $_user;
  /** @var UserWallet Кошелек выбранный партнером для автовыплаты */
  private $_wallet;
  /** @var float Сумма указанная партнером для выплаты. Если поставил галочку «Весь баланс» то тут будет указана сумма которая есть на балансе. */
  private $_payoutAmount;
  /** @var string Валюта в которой указана сумма выплаты (та же валюта что указана в настройках партнера) */
  private $_payoutCurrency;
  /** @var string Валюта кошелька в которой мы хотим получить выплату RUB|EUR|USD */
  private $_payoutWalletCurrency;
  /** @var  array|null */
  private $_dateRange;
  /** @var array */
  private $_errors = [];
  
  /**
   * {@inheritDoc}
   * @param PartnerPaymentSettings $partnerPaymentSettings
   * @param array $config
   */
  public function __construct(PartnerPaymentSettings $partnerPaymentSettings, array $config = [])
  {
    $this->_partnerPaymentSettings = $partnerPaymentSettings;
    parent::__construct($config);
  }
  
  /**
   * {@inheritDoc}
   * @throws Exception
   */
  public function init()
  {
    if (!($this->_partnerPaymentSettings instanceof PartnerPaymentSettings)) {
      throw new Exception("PartnerPaymentSettings is not set");
    }
    
    $this->_user = $this->getPartnerPaymentSettings()->user;
    $this->_wallet = $this->getPartnerPaymentSettings()->wallet;
    
    parent::init();
  }
  
  /**
   * @return bool
   * @throws \mcms\payments\components\exceptions\UserBalanceException
   * @throws \yii\base\Exception
   */
  public function pay()
  {
    if (!$this->validate()) {
      $this->getPartnerPaymentSettings()->updateAttributes(['last_checked_at' => Yii::$app->formatter->asTimestamp('now')]);
      return false;
    }
    
    $model = new UserPaymentForm(['scenario' => UserPayment::SCENARIO_AUTO_INVOICE]);
    $model->formName();
    $model->load([
      'user_id' => $this->_user->id,
      'user_wallet_id' => $this->getWallet()->id,
      'wallet_type' => $this->getWallet()->wallet_type,
      'invoice_amount' => $this->getPayoutAmount(),
      'invoiceType' => UserBalanceInvoice::TYPE_PAYMENT,
      'description' => "Сгенерирована автовыплата",
      'from_date' => Yii::$app->formatter->asDate($this->getPreviousDate(), 'php:Y-m-d'),
      'to_date' => Yii::$app->formatter->asDate("now", 'php:Y-m-d'),
      'invoice_currency' => $this->getPayoutCurrency(),
    ], '');
    
    if (!$model->save()) {
      foreach ($model->getErrors() as $error) {
        $this->addError(reset($error));
      }
      Yii::debug("Before save ".get_class($model).PHP_EOL.VarDumper::dumpAsString($model->attributes));

      return false;
    }
    
    return true;
  }
  
  /**
   * Сумма для выплаты
   * TRICKY: может быть отрицательной или 0
   * @return float
   * @throws \mcms\payments\components\exceptions\UserBalanceException
   * @throws \yii\base\Exception
   */
  protected function getPayoutAmount()
  {
    if ($this->_payoutAmount !== null) {
      return $this->_payoutAmount;
    }
    
    //Если стоит галочка "весь баланс"
    if ($this->getPartnerPaymentSettings()->totality) {
      $this->_payoutCurrency = $this->getUserCurrency();
      $this->_payoutAmount = $this->getUserBalance();
      
    } else {
      $this->_payoutCurrency = $this->getUserCurrency();
      $this->_payoutAmount = $this->getPartnerPaymentSettings()->amount;
    }
    return $this->_payoutAmount;
  }
  
  
  /**
   * @return UserBalance
   * @throws \mcms\payments\components\exceptions\UserBalanceException
   */
  protected function getBalanceModel()
  {
    return new UserBalance(['userId' => $this->_user->id]);
  }
  
  /**
   * Баланс пользователя
   * @return float
   * @throws \mcms\payments\components\exceptions\UserBalanceException
   */
  protected function getUserBalance()
  {
    $balance = $this->getBalanceModel();
    return $balance->getMain(false);
  }
  
  /**
   * Валюта партнера выбранная в настройках
   * @return string|null
   * @throws \mcms\payments\components\exceptions\UserBalanceException
   */
  protected function getUserCurrency()
  {
    return $this->getBalanceModel()->getPaymentSettings()->getCurrentCurrency();
  }
  
  /**
   * Проверяем пришло ли времени для создания заявок на выплату
   * @return bool
   * @throws \yii\base\InvalidConfigException
   */
  protected function getTimeIsNow()
  {
    switch ($this->getPartnerPaymentSettings()->invoicing_cycle) {
      case PartnerPaymentSettings::INVOICING_CYCLE_DAILY:
        $prevDate = $this->getPreviousDate();
        $yesterday = Yii::$app->formatter->asDate($prevDate, 'php:d');
        $today = Yii::$app->formatter->asDate('now', 'php:d');
        $result = (($today) > $yesterday);
        
        break;
      
      case PartnerPaymentSettings::INVOICING_CYCLE_WEEKLY:
        /**
         * Порядковый номер дня недели в соответствии со стандартом ISO 8601
         * Проверяем или уже наступил понедельник
         */
        if (Yii::$app->formatter->asDate('now', 'php:N') == 1) {
          $result = true;
        } else {
          $result = false;
        }
        break;
      
      case PartnerPaymentSettings::INVOICING_CYCLE_MONTHLY:
        /**    День месяца без ведущего нуля */
        if (Yii::$app->formatter->asDate('now', 'php:j') == 1) {
          $result = true;
        } else {
          $result = false;
        }
        break;
      
      default://Сюда по идеи никогда не зайдет проверка
        $result = false;
        break;
    }
    return $result;
  }
  
  /**
   * Возвращает дату прошлой проверки
   * @return int
   */
  protected function getPreviousDate()
  {
    if (!empty($this->getPartnerPaymentSettings()->last_checked_at)) {
      return $this->getPartnerPaymentSettings()->last_checked_at;
    }
    //для случаев когда настройка новая и проверка еще не проводилась
    return $this->getPartnerPaymentSettings()->created_at;
  }
  
  /**
   * Расчет следующей даты выплаты
   * @return false|string
   * @throws \yii\base\InvalidConfigException
   */
  protected function nextPaymentDate()
  {
    switch ($this->getPartnerPaymentSettings()->invoicing_cycle) {
      case PartnerPaymentSettings::INVOICING_CYCLE_DAILY:
        $result = Yii::$app->formatter->asDate("tomorrow", 'php:d-m-Y');
        break;
      
      case PartnerPaymentSettings::INVOICING_CYCLE_WEEKLY:
        $result = Yii::$app->formatter->asDate("next monday", 'php:d-m-Y');
        break;
      
      case PartnerPaymentSettings::INVOICING_CYCLE_MONTHLY:
        $result = Yii::$app->formatter->asDate("first day of next month", 'php:d-m-Y');
        break;
      
      default:
        $result = false;
        break;
    }
    return $result;
  }
  
  protected function validate()
  {
    if (!$this->getTimeIsNow()) {
      $now = Yii::$app->formatter->asDate("now", 'php:d-m-Y');
      $this->addError("Время платежа еще не наступило, выплата ожидается {$this->nextPaymentDate()} а сегодня {$now}");
      return false;
    }
    
    if ($this->getUserBalance() <= 0) {
      $this->addError(Yii::_t("payments.partner-companies.there_was_not_enough_money_on_the_balance_to_pay"));
      return false;
    }
    
    return true;
  }
  
  
  /**
   * @param $text
   * @return void
   */
  protected function addError($text)
  {
    $this->_errors[] = $text;
  }
  
  /**
   * @return array
   */
  public function getErrors()
  {
    return $this->_errors;
  }
  
  public function getFirstError()
  {
    return reset($this->_errors);
  }
  
  /**
   * @return string
   */
  public function getPayoutCurrency(): string
  {
    if ($this->_payoutCurrency === null) {
      $this->getPayoutAmount();
    }
    return $this->_payoutCurrency;
  }
  
  /**
   * @return PartnerPaymentSettings
   */
  public function getPartnerPaymentSettings(): PartnerPaymentSettings
  {
    return $this->_partnerPaymentSettings;
  }
  
  /**
   * @return UserWallet
   */
  public function getWallet(): UserWallet
  {
    return $this->_wallet;
  }
  
  
}