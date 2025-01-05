<?php

namespace mcms\payments\models;

use mcms\common\exceptions\ModelNotSavedException;
use mcms\payments\components\invoice\UserPaymentInvoiceGenerator;
use mcms\payments\components\UserBalance;
use mcms\payments\models\wallet\AbstractWallet;
use mcms\payments\models\wallet\Wallet;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class UserPaymentForm
 * @package mcms\payments\models
 *
 * @property Wallet paymentAccount
 * @property int balanceMain
 * @property int balanceHold
 * @property string currencyLabel
 *
 * TRICKY Используйте save() вместо insert() и update(), иначе инвойс не создастся
 */
class UserPaymentForm extends UserPayment
{
  public $autoPayComment;

  /** @var  UserPaymentSetting */
  protected $userPaymentSetting;

  const SCENARIO_MANUAL = 'manual';
  const SCENARIO_DELAY = 'delay';

  /**
   * @inheritdoc
   */
  public function rules()
  {
    // TRICKY Обязательность акта и квитнации не должна проверяться при создании выплаты
    // tricky НЕ УБИРАТЬ ПРОВЕРКУ isNewRecord, иначе падает создание новой выплаты
    $isInvoiceRequired = !$this->isNewRecord && $this->walletModel->is_invoice_file_required
      && !$this->invoice_file;
    // tricky НЕ УБИРАТЬ ПРОВЕРКУ isNewRecord, иначе падает создание новой выплаты
    $isCheckRequired = !$this->isNewRecord && $this->walletModel->is_check_file_required
      && !$this->cheque_file;

    return array_merge([
      ['pay_period_end_date', 'required', 'on' => self::SCENARIO_DELAY],
      ['pay_period_end_date', 'compare', 'operator' => '>=', 'compareValue' => $this->getMinDelayTo(true), 'on' => self::SCENARIO_DELAY],
      ['user_id', 'checkUserAvailableReseller', 'on' => self::SCENARIO_RESELLER_CREATE],
      ['user_id', 'exist',
        'targetClass' => UserPaymentSetting::class,
        'targetAttribute' => 'user_id',
        'message' => Yii::_t('payments.user-payments.error-payment-settings'),
      ],
      ['user_wallet_id', 'checkPaymentSystem', 'on' => [
        self::SCENARIO_ADMIN_CREATE,
        self::SCENARIO_CREATE,
        self::SCENARIO_UPDATE,
        self::SCENARIO_SEND_TO_EXTERNAL,
        self::SCENARIO_RESELLER_CREATE,
      ]],
      ['user_wallet_id', 'checkPaymentAccount', 'on' => [
        self::SCENARIO_ADMIN_CREATE,
        self::SCENARIO_CREATE,
        self::SCENARIO_UPDATE,
        self::SCENARIO_SEND_TO_EXTERNAL,
        self::SCENARIO_RESELLER_CREATE,
      ]],
      ['user_id', 'checkUserBalance', 'on' => [
        self::SCENARIO_CREATE,
        self::SCENARIO_UPDATE,
        self::SCENARIO_SEND_TO_EXTERNAL,
      ]],

      [['invoiceType', 'user_wallet_id'], 'required'],
      ['invoiceType', 'in', 'range' => array_keys(UserBalanceInvoice::getPaymentInvoiceTypes())],
      ['invoiceType', 'in', 'range' => [UserBalanceInvoice::TYPE_PAYMENT, UserBalanceInvoice::TYPE_EARLY_PAYMENT],
        'on' => [self::SCENARIO_RESELLER_CREATE, self::SCENARIO_RESELLER_UPDATE], 'skipOnEmpty' => false],

      ['invoiceType', 'filter', 'filter' => function () {
        return ArrayHelper::getValue($this->invoices, [0, 'type']);
      }, 'on' => [self::SCENARIO_UPDATE, self::SCENARIO_SEND_TO_EXTERNAL]],

      ['type', 'filter', 'filter' => function () {
        return self::TYPE_RESELLER_MANUAL;
      }, 'on' => [self::SCENARIO_RESELLER_CREATE, self::SCENARIO_RESELLER_UPDATE], 'skipOnEmpty' => false],

      ['autoPayComment', 'safe', 'on' => self::SCENARIO_AUTOPAY],
      ['reseller_paysystem_percent', 'number', 'on' => self::SCENARIO_UPDATE],

      ['from_date', 'required', 'except' => [self::SCENARIO_CREATE_RESELLER_PAYMENT]],

      [
        'to_date',
        'filter',
        'filter' => function ($value) {
          if ($this->getInvoiceType() == UserBalanceInvoice::TYPE_EARLY_PAYMENT) {
            return $this->from_date;
          }
          return $value;
        },
        'skipOnEmpty' => false
      ],
      [
        'to_date',
        'required',
        'when' => function () {
          return $this->getInvoiceType() == UserBalanceInvoice::TYPE_PAYMENT;
        },
        'whenClient' => "function (attribute, value) {
          return $('#userpaymentform-invoicetype').val() == '" . UserBalanceInvoice::TYPE_PAYMENT . "';
        }",
        'except' => [self::SCENARIO_CREATE_RESELLER_PAYMENT]
      ],
      ['status', 'filter', 'filter' => 'intval'],
      ['user_wallet_id', 'integer'],
      ['invoice_file', 'required', 'when' => function () use ($isInvoiceRequired) {
        return in_array($this->scenario, [self::SCENARIO_DELAY, self::SCENARIO_MANUAL]) && $isInvoiceRequired;
      }, 'whenClient' => "function (attribute, value) {
        return " . (string)$isInvoiceRequired . ";
      }"],
      ['cheque_file', 'required', 'when' => function () use ($isCheckRequired) {
        return $this->scenario == self::SCENARIO_MANUAL && $isCheckRequired;
      }, 'whenClient' => "function (attribute, value) {
        return " . (string)$isCheckRequired . ";
      }"],
    ], parent::rules());
  }

  /**
   * @inheritdoc
   */
  public function scenarios()
  {
    $parentScenarios = parent::scenarios();
    $parentScenarios[self::SCENARIO_AUTOPAY][] = 'autoPayComment';
    $parentScenarios[self::SCENARIO_CREATE][] = 'invoiceType';
    $parentScenarios[self::SCENARIO_ADMIN_CREATE][] = 'invoiceType';
    $parentScenarios[self::SCENARIO_AUTOPAYOUT][] = 'invoiceType';
    $parentScenarios[self::SCENARIO_AUTO_INVOICE][] = 'invoiceType';
    $parentScenarios[self::SCENARIO_RESELLER_CREATE][] = 'invoiceType';
    $parentScenarios[self::SCENARIO_RESELLER_UPDATE][] = 'invoiceType';
    $parentScenarios[self::SCENARIO_MANUAL] = array_filter([
      'status', 'processing_type',
      // tricky Если уже загружен акт, то больше перезаписывать его нельзя!
      ($this->invoice_file ? null : 'invoice_file'),
      'cheque_file', 'payed_at',
    ]);
    return $parentScenarios;
  }

  /**
   * @param bool $insert
   * @param array $changedAttributes
   */
  public function afterSave($insert, $changedAttributes)
  {
    parent::afterSave($insert, $changedAttributes);

    // Генерация инвойса при автовыплате
    if ($insert && in_array($this->scenario, [static::SCENARIO_AUTOPAYOUT])) {
      $this->generateInvoiceFile();
    }
  }

  /**
   * Описание выплаты
   * TRICKY Описание дожно быть до 255 символов. Этого требует API WebMoney
   * @return string
   */
  public function generateDescription()
  {
    $text = Yii::_t('payments.payments.description-merchant-payment', [
      'type' => $this->getInvoices()->one()->getTypeName(),
      'id' => $this->id,
      'period' => $this->getPeriod(),
      'userId' => $this->user_id,
      'projectName' => Yii::$app->getModule('partners')->getProjectName(),
      'autoPayComment' => $this->autoPayComment,
    ]);
    return  str_replace(['0000-00-00','Коментарий: ""','Comment: ""'],[date("Y.m.d"),"",""],$text);
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return array_merge(parent::attributeLabels(), [
      'invoiceType' => self::translate('attribute-invoice-type'),
      'user_wallet_id' => self::translate('attribute-wallet-id'),
      'pay_period_end_date' => self::translate('attribute-delayed_to'),
      'autoPayComment' => self::translate('attribute-auto-pay-comment'),
      'reseller_paysystem_percent' => self::translate('attribute-reseller_paysystem_percent')
    ]);
  }

  /**
   * @inheritdoc
   */
  public function beforeValidate()
  {
    if ($this->isNewRecord) {
      $this->status = static::STATUS_AWAITING;
    }

    return parent::beforeValidate();
  }

  /**
   * @param $attribute
   */
  public function checkPaymentSystem($attribute)
  {
    $paymentSystem = $this->getWalletTypeLabel() ?: null;
    if ($paymentSystem === null) {
      $this->addError($attribute, Yii::_t('payments.user-payments.error-payment-system'));
    }
  }

  /**
   * @param $attribute
   */
  public function checkPaymentAccount($attribute)
  {
    $paymentAccount = $this->getPaymentAccount();
    if ($paymentAccount === null) {
      $this->addError($attribute, Yii::_t('payments.user-payments.error-payment-account'));
    }
  }

  /**
   * @param $attribute
   */
  public function checkUserBalance($attribute)
  {
    $userBalance = $this->getUserBalance();
    if ($userBalance->getMain() <= 0) {
      $this->addError($attribute, Yii::_t('payments.user-payments.error-balance-main'));
    }
  }

  /**
   * @inheritDoc
   */
  public function save($runValidation = true, $attributeNames = null)
  {
    $isInsert = $this->isNewRecord;
    $oldStatus = $isInsert ? null : $this->oldAttributes['status'];

    if ($this->status == self::STATUS_COMPLETED) {
      $this->payed_at = time();
    }
    Yii::debug("Before save ".__METHOD__.PHP_EOL);
    Yii::debug($this->attributes);

    $transaction = Yii::$app->db->beginTransaction();
    try {
      if (!parent::save()) {
        throw new ModelNotSavedException();
      }

      $invoice = $isInsert ? UserBalanceInvoice::createInvoice($this) : null;

      if ($invoice !== null && $this->scenario === static::SCENARIO_AUTOPAYOUT) {
        $invoice->date = $this->to_date;
      }

      $invoiceRollback = null;

      // если выплата отмененная, до добавляем инвойс отмены операции
      if (($isInsert || $oldStatus !== self::STATUS_CANCELED) && $this->status === self::STATUS_CANCELED) {
        $invoiceRollback = UserBalanceInvoice::createInvoice($this, true);
      }

      // если меняем статус выплаты с отмененной на другую, то добавляем инвойс
      if ($oldStatus === self::STATUS_CANCELED && $this->status !== self::STATUS_CANCELED) {
        $invoiceRollback = UserBalanceInvoice::createInvoice($this);
      }

      if (($invoice && !$invoice->save()) || ($invoiceRollback && !$invoiceRollback->save())) {
        throw new ModelNotSavedException();
      }

      $transaction->commit();
      return true;

    } catch (\Exception $e) {
      $transaction->rollBack();
      Yii::error('Не удалось сохранить выплату #' . $this->id, __METHOD__);
      return false;
    }
  }

  /**
   * @return float
   */
  public function getBalanceMain()
  {
    return (double)$this->getUserBalance($this->getUserPaymentSetting()->getCurrentCurrency())->getMain();
  }

  /**
   * @return float
   */
  public function getBalanceHold()
  {
    return (double)$this->getUserBalance($this->getUserPaymentSetting()->getCurrentCurrency())->getHold();
  }

  /**
   * @return AbstractWallet|null
   */
  public function getPaymentAccount()
  {
    if ($wallet = $this->getWallet()) {
      /** @var AbstractWallet $walletAccount */
      $walletAccount = Wallet::getObject($wallet->wallet_type);
      $walletAccount->load([$walletAccount->formName() => json_decode($wallet->wallet_account, true)]);
      return $walletAccount;
    }
    return null;
  }

  /**
   * @inheritdoc
   */
  public function getCurrencyName()
  {
    if ($currency = parent::getCurrencyName()) {
      return $currency;
    }
    if ($this->getUserPaymentSetting()) {
      $this->currency = $this->userPaymentSetting->getCurrentCurrency();
      return $this->getCurrencyName();
    }
    return null;
  }

  /**
   * @return int|mixed
   */
  public function getInvoiceType()
  {
    if (!$this->invoiceType) {
      if ($this->isNewRecord) {
        $this->invoiceType = UserBalanceInvoice::TYPE_PAYMENT;
      } else {
        $invoice = $this->getInvoices()->one();
        $this->invoiceType = $invoice ? $invoice->type : UserBalanceInvoice::TYPE_PAYMENT;
      }
    }
    return $this->invoiceType;
  }

  /**
   * @return UserPaymentSetting
   */
  public function getUserPaymentSetting()
  {
    if ($this->userPaymentSetting !== null) {
      return $this->userPaymentSetting;
    }

    $this->userPaymentSetting = $this->fetchUserPaymentSetting();
    return $this->userPaymentSetting->setParentPayment($this);
  }

  /**
   * @param $attribute
   */
  public function checkUserAvailableReseller($attribute)
  {
    $notAvailableUserIds = Yii::$app->getModule('users')->api('notAvailableUserIds', [
      'userId' => Yii::$app->getUser()->id, 'skipCurrentUser' => true
    ])->getResult();

    if (
      ArrayHelper::getValue($notAvailableUserIds, $this->getAttribute($attribute)) ||
      $this->getAttribute($attribute) == Yii::$app->getUser()->id
    ) {

      $this->addError($attribute, Yii::_t('payments.user-payments.error-payment-settings'));
    }
  }

  /**
   * Совпадает ли валюта выплаты с текущей
   * @return bool
   */
  private function matchesWithCurrentCurrency()
  {
    return $this->fetchUserPaymentSetting()->getCurrentCurrency() === $this->invoice_currency;
  }

  /**
   * Пометить выплату как отмененную
   * @param null $message
   * @return bool
   */
  public function cancel($message = null)
  {
    if (!$this->matchesWithCurrentCurrency()) {
      return false;
    }
    $this->status = self::STATUS_CANCELED;
    $this->processing_type = self::PROCESSING_TYPE_SELF;
    $this->description = $message ?: $this->description;
    $this->processed_by = Yii::$app->user->id;

    return $this->save();
  }

  /**
   * Пометить выплату как отложенную
   * @return bool
   */
  public function delay()
  {
    if (!$this->isAvailableDelay()) return false;

    $this->scenario = static::SCENARIO_DELAY;
    $this->status = self::STATUS_DELAYED;

    return $this->save();
  }

  /**
   * Пометить выплату как анулированную
   * @param null $message
   * @return bool
   */
  public function annul($message = null)
  {
    $this->status = self::STATUS_ANNULLED;
    $this->processing_type = self::PROCESSING_TYPE_SELF;
    $this->description = $message ?: $this->description;
    $this->processed_by = Yii::$app->user->id;

    return $this->save();
  }

  /**
   * Пометить выплату как выплаченную в ручную
   * @return bool
   */
  public function updateProcessToManual()
  {
    if (!$this->isPayable()) return false;

    $this->status = self::STATUS_COMPLETED;
    $this->processing_type = self::PROCESSING_TYPE_SELF;
    $this->processed_by = Yii::$app->user->id;

    $transaction = Yii::$app->db->beginTransaction();
    try {
      if (!$this->save()) {
        throw new ModelNotSavedException;
      }

      $transaction->commit();
      return true;
    } catch (\Exception $e) {
      $transaction->rollBack();
      return false;
    }
  }

  /**
   * Пометить выплату как отправленную на внешнюю обработку в мгмп
   * Заметим что помимо инвойса получателя выплаты, который создается автоматически правда не тут,
   * а в дочерней модели UserPaymentForm для сценария SCENARIO_UPDATE,
   * тут так же в ручную создается инвойс для реса
   * TRICKY не забывайте, что сценарий модели должен быть UserPayment::SCENARIO_SEND_TO_EXTERNAL,
   * иначе валидация криво работать будет
   *
   * @return bool
   */
  public function sendProcessToExternal()
  {
    $balance = new UserBalance(['userId' => UserPayment::getResellerId(), 'currency' => $this->currency]);
    $canSendToMgmp = ($balance->getResellerBalance() - $this->amount) > 0;

    if (!$canSendToMgmp) {
      $this->addError('amount', Yii::_t('payments.user-payments.error-balance-insufficient'));
      return false;
    }
    if ($this->status === self::STATUS_PROCESS) {
      $this->addError('status', Yii::_t('Payment already in process'));
      return false;
    }

    $this->status = self::STATUS_PROCESS;
    $this->processing_type = self::PROCESSING_TYPE_EXTERNAL;
    $this->processed_by = Yii::$app->user->id;
    $this->invoiceType = UserBalanceInvoice::TYPE_RGK_PAYMENT;

    $success = $this->save();

    $resellerId = UserPayment::getResellerId();
    // Когда создается новая выплата автоматом создается invoice
    // Соответственно нам нужно создавать инвойс для реселлера только в том случае, если выплата идет для партнера
    // т.к. будет создан только партнерский инвойс
    if ($resellerId !== $this->user_id) {
      UserBalanceInvoice::createResellerToPartnerInvoice($this, false, $resellerId)->save();
    }

    return $success;
  }

  /**
   * Обрабатываем информацию из мгмп, и если выплата отмененная в ручную создаем реверт инвойс для реса,
   * см. $this->sendProcessToExternal
   * @return bool
   */
  public function handleExternalProcess()
  {
    $resellerId = UserPayment::getResellerId();
    $success = $this->save();
    // TRICKY при отмене на MGMP выплата в MCMS получает статус "Ошибка выплаты"
    if ($this->status === self::STATUS_ERROR && $resellerId !== $this->user_id) {
      UserBalanceInvoice::createResellerToPartnerInvoice($this, true, $resellerId)->save();
    }
    return $success;
  }

  /**
   * Поставить выплату в статус "В процессе" для автоматических выплат
   * @return bool
   */
  public function setStatusToProcessApi()
  {
    $this->status = self::STATUS_PROCESS;
    $this->processing_type = self::PROCESSING_TYPE_API;
    return $this->validate();
  }

  /**
   * @return string
   */
  public function getDefaultDelayTo()
  {
    return Yii::$app->formatter->asDate('today + 10 days', 'php:Y-m-d');
  }

  /**
   * @param bool $asTimestamp
   * @return int|string
   */
  public function getMinDelayTo($asTimestamp = false)
  {
    if ($asTimestamp) return Yii::$app->formatter->asTimestamp('today');
    return Yii::$app->formatter->asDate('today', 'php:Y-m-d');
  }
}