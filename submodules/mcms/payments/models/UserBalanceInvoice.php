<?php

namespace mcms\payments\models;

use mcms\common\traits\Translate;
use mcms\payments\components\api\UserSettingsData;
use mcms\payments\components\events\UserBalanceInvoiceCreated;
use mcms\payments\components\UserBalance;
use mcms\user\models\User;
use mcms\user\Module;
use Yii;
use yii\behaviors\BlameableBehavior;
use rgk\utils\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\BatchQueryResult;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "user_balance_invoices".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $user_payment_id
 * @property string $currency
 * @property string $amount
 * @property string $description
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $mgmp_id
 * @property integer $type
 * @property string $file
 * @property string $date
 *
 * @property UserPayment[] $userPayments
 * @property User $user
 */
class UserBalanceInvoice extends \yii\db\ActiveRecord
{
  use Translate;
  const LANG_PREFIX = 'payments.user-balance-invoices.';

  // TRICKY Эти ID типов используются и в MGMP
  // Штатная выплата
  const TYPE_PAYMENT = 0;
  // Компенсация
  const TYPE_COMPENSATION = 1;
  // Досрочная выплата
  const TYPE_EARLY_PAYMENT = 2;
  // Покупка домена
  const TYPE_BUY_DOMAIN = 5;
  // Штраф
  const TYPE_PENALTY = 6;
  // Выплата через RGK
  const TYPE_RGK_PAYMENT = 9;
  // Увеличение баланса при конвертации
  const TYPE_CONVERT_INCREASE = 10;
  // Уменьшение баланса при конвертации
  const TYPE_CONVERT_DECREASE = 11;
  // Списание с баланса, для старых юзеров
  const TYPE_WRITE_OFF = 12;

  /** @var int Зачисление кредитных средств на баланс реселлера */
  const TYPE_CREDIT_ACCRUE_AMOUNT = 12;
  /** @var int Списание ежемесячной кредитной комиссии */
  const TYPE_CREDIT_MONTHLY_FEE = 13;
  /** @var int Выплата по кредиту с баланса реселлера */
  const TYPE_CREDIT_BALANCE_PAYMENT = 14;

  const HOLD_INVOICES = 'hold_invoices';
  const UNHOLD_INVOICES = 'unhold_invoices';

  const SCENARIO_GENERATE_PAYMENTS = 'scenarioGeneratePayments';
  const SCENARIO_GENERATE_AUTO_PAYMENTS = 'scenarioGenerateAutoPayments';
  const SCENARIO_PENALTY = 'scenario_penalty  ';
  const SCENARIO_COMPENSATION = 'scenario_compensation';
  const SCENARIO_RESELLER_PENALTY = 'scenario_reseller_penalty';
  const SCENARIO_RESELLER_COMPENSATION = 'scenario_reseller_compensation';
  const SCENARIO_CONVERT_INCREASE = 'scenario_convert_increase';
  const SCENARIO_CONVERT_DECREASE = 'scenario_convert_decrease';
  const SCENARIO_MGMP_IMPORT = 'scenario_mgmp_import';

  protected static $currencyList;

  /** @var  Module */
  private $userModule;

  /**
   * @inheritdoc
   */
  public function behaviors()
  {
    return [
      [
        'class' => TimestampBehavior::class,
        'skipOnChanged' => true,
      ],
      [
        'class' => BlameableBehavior::class,
        'createdByAttribute' => 'created_by',
        'updatedByAttribute' => false,
      ]
    ];
  }

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'user_balance_invoices';
  }

  /**
   * @inheritDoc
   */
  public function __construct($config = [])
  {
    if ($this->userModule === null) {
      $this->userModule = Yii::$app->getModule('users');
    }
    parent::__construct($config);
  }

  /**
   * @inheritdoc
   */
  public function formName()
  {
    return parent::formName() . '-' . substr(md5($this->scenario), 0, 5);
  }

  /**
   * @inheritDoc
   */
  public function scenarios()
  {
    return array_merge(parent::scenarios(), [
      self::SCENARIO_GENERATE_PAYMENTS => [
        'id', 'user_id', 'user_payment_id', 'currency', 'amount', 'description', 'created_at', 'type', 'date'
      ],
      self::SCENARIO_GENERATE_AUTO_PAYMENTS => [
        'id', 'user_id', 'user_payment_id', 'currency', 'amount', 'description', 'created_at', 'type', 'date'
      ],
      self::SCENARIO_PENALTY => [
        'user_id', 'currency', 'amount', 'description', 'created_at', 'type', 'date'
      ],
      self::SCENARIO_CONVERT_DECREASE => [
        'user_id', 'currency', 'amount', 'description', 'created_at', 'type', 'date', 'country_id'
      ],
      self::SCENARIO_COMPENSATION => [
        'user_id', 'currency', 'amount', 'description', 'created_at', 'type', 'date'
      ],
      self::SCENARIO_CONVERT_INCREASE => [
        'user_id', 'currency', 'amount', 'description', 'created_at', 'type', 'date', 'country_id'
      ],
      self::SCENARIO_RESELLER_PENALTY => [
        'user_id', 'currency', 'amount', 'description', 'created_at', 'type', 'date'
      ],
      self::SCENARIO_RESELLER_COMPENSATION => [
        'user_id', 'currency', 'amount', 'description', 'created_at', 'type', 'date'
      ],
      self::SCENARIO_MGMP_IMPORT => [
        'user_id', 'currency', 'amount', 'description', 'created_at', 'updated_at', 'type', 'file', 'date', 'mgmp_id'
      ],
    ]);
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['type'], 'default', 'value' => self::TYPE_CONVERT_DECREASE, 'on' => self::SCENARIO_CONVERT_DECREASE, 'skipOnEmpty' => false],
      [['type'], 'default', 'value' => self::TYPE_CONVERT_INCREASE, 'on' => self::SCENARIO_CONVERT_INCREASE, 'skipOnEmpty' => false],

      [['type'], 'default', 'value' => self::TYPE_PENALTY, 'on' => [
        self::SCENARIO_PENALTY, self::SCENARIO_RESELLER_PENALTY
      ], 'skipOnEmpty' => false],
      [['type'], 'default', 'value' => self::TYPE_COMPENSATION, 'on' => [
        self::SCENARIO_COMPENSATION, self::SCENARIO_RESELLER_COMPENSATION
      ], 'skipOnEmpty' => false],
      [['type'], 'default', 'value' => self::TYPE_PAYMENT, 'on' => self::SCENARIO_GENERATE_PAYMENTS, 'skipOnEmpty' => false],
      [['type'], 'default', 'value' => self::TYPE_PAYMENT, 'on' => self::SCENARIO_GENERATE_AUTO_PAYMENTS, 'skipOnEmpty' => false],

      [['user_id', 'amount', 'type'], 'required'],
      ['user_id', 'checkUserAvailableReseller', 'on' => [
        self::SCENARIO_RESELLER_COMPENSATION,
        self::SCENARIO_RESELLER_PENALTY
      ]],
      ['user_id', 'exist',
        'targetClass' => UserPaymentSetting::class,
        'targetAttribute' => 'user_id',
        'message' => Yii::_t('payments.user-payments.error-payment-settings'),
      ],

      [['user_payment_id', 'created_at', 'type'], 'integer'],
      [
        ['user_payment_id'], 'exist', 'skipOnError' => true,
        'targetClass' => UserPayment::class, 'targetAttribute' => ['user_payment_id' => 'id']
      ],
      [['currency'], 'required', 'message' => Yii::_t('payments.user-payment-settings.cant_get_user_currency_error')],
      [['currency'], 'in', 'range' => array_keys($this->getCurrencyList()), 'skipOnEmpty' => false],

      [['amount'], 'number'],
      [['amount'], 'compare', 'compareValue' => 0, 'operator' => '>', 'on' => [
        self::SCENARIO_PENALTY, self::SCENARIO_CONVERT_DECREASE, self::SCENARIO_RESELLER_PENALTY,
        self::SCENARIO_COMPENSATION, self::SCENARIO_CONVERT_INCREASE, self::SCENARIO_RESELLER_COMPENSATION,
      ]],
      [['amount'], 'filter', 'filter' => function ($value) {
        return -1 * round(abs($value), 3);
      }, 'on' => [
        self::SCENARIO_PENALTY, self::SCENARIO_CONVERT_DECREASE, self::SCENARIO_RESELLER_PENALTY
      ]],
      [['amount'], 'filter', 'filter' => function ($value) {
        return round(abs($value), 3);
      }, 'on' => [
        self::SCENARIO_COMPENSATION, self::SCENARIO_CONVERT_INCREASE, self::SCENARIO_RESELLER_COMPENSATION
      ]],
      [['description'], 'string', 'max' => 255],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'id' => 'ID',
      'user_id' => self::translate('attribute-user-id'),
      'currency' => Yii::_t('payments.main.attribute-currency'),
      'amount' => self::translate('attribute-amount'),
      'description' => self::translate('attribute-description'),
      'created_at' => self::translate('attribute-created-at'),
      'date' => self::translate('attribute-date'),
      'type' => self::translate('attribute-type'),
    ];
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getUserPayments()
  {
    return $this->hasMany(UserPayment::class, ['user_balance_invoice_id' => 'id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getUserPaymentSettings()
  {
    return $this->hasOne(UserPaymentSetting::class, ['user_id' => 'user_id']);
  }

  /**
   * @return UserPaymentSetting
   */
  public function fetchUserPaymentSettings()
  {
    return (new UserSettingsData(['userId' => $this->user_id]))->getResult();
  }

  /**
   * @param null $type
   * @return array|string
   */
  public static function getTypes($type = null)
  {
    $typeList = [
      self::TYPE_PAYMENT => self::translate('type-payment'),
      self::TYPE_COMPENSATION => self::translate('type-compensation'),
      self::TYPE_CONVERT_INCREASE => self::translate('type-convert-increase'),
      self::TYPE_EARLY_PAYMENT => self::translate('type-early-payment'),
      self::TYPE_BUY_DOMAIN => self::translate('type-buy-domain'),
      self::TYPE_PENALTY => self::translate('type-penalty'),
      self::TYPE_CONVERT_DECREASE => self::translate('type-convert-decrease'),
      self::TYPE_RGK_PAYMENT => self::translate('type-rgk-payment'),
    ];
    return $type === null ? $typeList : ArrayHelper::getValue($typeList, $type);
  }

  /**
   * Тип инвойса.
   * Штатный/досрочный
   * @return array
   */
  public static function getPaymentInvoiceTypes()
  {
    return [
      self::TYPE_PAYMENT => self::t('type-payment'),
      self::TYPE_EARLY_PAYMENT => self::t('type-early-payment'),
    ];
  }

  /**
   * @param $userId
   * @param string|null $currency
   * @param string|null $dateFrom
   * @param string|null $dateTo
   * @return float[]
   */
  public static function getInvoice($userId, $currency = null, $dateFrom = null, $dateTo = null)
  {
    $currency = $currency ?: UserPaymentSetting::fetch($userId)->getCurrency();

    $amount = (new Query())
      ->select([
        'SUM(IF((pcu.last_unhold_date IS NULL OR pcu.last_unhold_date < ' . self::tableName() . '.date) && ' . self::tableName() . '.country_id <> 0, amount, 0)) as ' . self::HOLD_INVOICES,
        'SUM(IF(pcu.last_unhold_date >= ' . self::tableName() . '.date || ' . self::tableName() . '.country_id = 0, amount, 0)) as ' . self::UNHOLD_INVOICES,
      ])
      ->from(self::tableName())
      // TODO: Заменить строку partner_country_unhold на PartnerCountryUnhold::tableName()
      ->leftJoin('partner_country_unhold pcu', self::tableName() . '.country_id=pcu.country_id AND ' . self::tableName() . '.user_id=pcu.user_id')
      ->where([self::tableName() . '.user_id' => $userId])
      ->andFilterWhere([self::tableName() . '.currency' => $currency])
      ->andFilterWhere(['>=', self::tableName() . '.date', $dateFrom])
      ->andFilterWhere(['<=', self::tableName() . '.date', $dateTo])
      ->one();

    // Делаем float и возвращаем
    return array_map(function($v) { return (float)$v; }, $amount);
  }

  /**
   * Возвращает профиты пользователя, находящиеся в холде, сгруппированные по дате и стране
   * Используется при конвертации, для создания соответствующих инвойсов
   * TRICKY: Аналогичный метод есть в @see \mcms\payments\models\UserBalancesGroupedByDay::getHoldProfit
   * @param $userId
   * @param null $currency
   * @return array
   */
  public static function getHoldInvoices($userId, $currency = null)
  {
    $currency = $currency ?: UserPaymentSetting::fetch($userId)->getCurrency();

    $holds = (new Query())
      ->select([
        'SUM(amount) as amount',
        self::tableName() . '.date',
        self::tableName() . '.country_id',
      ])
      ->from(self::tableName())
      // TODO: Заменить строку partner_country_unhold на PartnerCountryUnhold::tableName()
      ->innerJoin('partner_country_unhold pcu', self::tableName() . '.country_id=pcu.country_id AND ' . self::tableName() . '.user_id=pcu.user_id AND pcu.last_unhold_date < ' . self::tableName() . '.date')
      ->where([self::tableName() . '.user_id' => $userId])
      ->andFilterWhere([self::tableName() . '.currency' => $currency])
      ->groupBy([self::tableName() . '.date', self::tableName() . '.country_id'])
      ->all();

    // Делаем amount float и возвращаем
    return array_map(function ($v) {
      $v['amount'] = (float)$v['amount'];
      return $v;
    }, $holds);
  }

  /**
   * @param $userIds
   * @param integer|null $dateFrom
   * @param integer|null $dateTo
   * @param null $currency
   * @return BatchQueryResult
   */
  public static function getUsersInvoiceSumList($userIds, $dateFrom = null, $dateTo = null, $currency = null)
  {
    return self::find()
      ->select([
        'SUM(amount) as amount',
        'user_id',
        'currency',
      ])
      ->where(['user_id' => $userIds])
      ->andFilterWhere(['currency' => $currency])
      ->andFilterWhere(['>', 'created_at', $dateFrom])
      ->andFilterWhere(['<=', 'created_at', $dateTo])
      ->groupBy(['user_id', 'currency'])
      ->each();
  }

  /**
   * @return string
   */
  public function getTypeName()
  {
    return static::getTypes($this->type);
  }

  /**
   * @inheritDoc
   */
  public function beforeValidate()
  {
    if ($this->user_id) {
      /** @var UserPaymentSetting $userPaymentSettings */
      $userPaymentSettings = $this->fetchUserPaymentSettings();

      $this->currency = $this->currency ?: $userPaymentSettings->getCurrentCurrency();
    }

    return parent::beforeValidate();
  }

  /**
   * @inheritDoc
   */
  public function beforeSave($insert)
  {
    if (!parent::beforeSave($insert)) {
      return false;
    }

    if ($this->scenario === self::SCENARIO_GENERATE_AUTO_PAYMENTS) {
      $this->type = self::TYPE_RGK_PAYMENT;
    }

    if (!$this->date) {
      $this->date = Yii::$app->formatter->asDate($this->created_at, 'php:Y-m-d');
    }

    return true;
  }

  /**
   * @inheritDoc
   * @see \mcms\payments\models\UserPayment::afterSave() Там тоже тригерятся события
   */
  public function afterSave($insert, $changedAttributes)
  {
    if ($insert) {
      (new UserBalanceInvoiceCreated($this))->trigger();
    }

    (new UserBalance([
      'userId' => $this->user_id,
      'currency' => $this->currency,
    ]))->invalidateCache();

    parent::afterSave($insert, $changedAttributes);
  }

  /**
   * @return ActiveQuery
   */
  public function getUser()
  {
    /** @var Module $userModule */
    $userModule = Yii::$app->getModule('users');
    /** @var \mcms\user\components\api\User $api */
    $api = $userModule->api('user', ['getRelation' => true]);
    return $api->hasOne($this, 'user_id');
  }

  /**
   * @return array
   */
  public function getReplacements()
  {
    return [
      'id' => [
        'value' => $this->id,
        'help' => [
          'label' => 'ID'
        ]
      ],
      'user' => [
        'value' => $this->isNewRecord ? null : $this->getUser()->one()->getReplacements(),
        'help' => [
          'class' => Yii::$app->user->identityClass,
          'label' => self::translate('replacement-user'),
        ]
      ],
      'currency' => [
        'value' => $this->currency,
        'help' => [
          'label' => Yii::_t('payments.main.attribute-currency')
        ]
      ],
      'amount' => [
        'value' => $this->amount,
        'help' => [
          'label' => self::translate('attribute-amount')
        ]
      ],
      'description' => [
        'value' => $this->description,
        'help' => [
          'label' => self::translate('attribute-description')
        ]
      ],
      'type' => [
        'value' => $this->isNewRecord ? null : $this->getTypeName(),
        'help' => [
          'label' => self::translate('attribute-type')
        ]
      ],
    ];
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getUserPayment()
  {
    return $this->hasOne(UserPayment::class, ['id' => 'user_payment_id']);
  }

  /**
   * @return array
   */
  public static function getCurrencyList()
  {
    if (static::$currencyList === null) {
      static::$currencyList = UserBalance::getCurrencies();
    }
    return static::$currencyList;
  }

  /**
   * @return string
   */
  public function getCurrencyName()
  {
    return static::$currencyList[$this->currency];
  }

  /**
   * @return float
   */
  public function getAmount()
  {
    return $this->getAttribute('amount_' . $this->currency);
  }

  /**
   * @param $userId
   * @return int|string
   */
  public static function userInvoiceCount($userId)
  {
    return self::find()->where(['user_id' => $userId])->count();
  }

  /**
   * @param $attribute
   */
  public function checkUserAvailableReseller($attribute)
  {
    if (ArrayHelper::getValue(
        Yii::$app->getModule('users')->api('notAvailableUserIds', ['userId' => Yii::$app->getUser()->id])->getResult(),
        $this->getAttribute($attribute)
      ) ||
      $this->getAttribute($attribute) == Yii::$app->getUser()->id ||
      !$this->getUser()->one()->isActive()
    ) {
      $this->addError($attribute, Yii::_t('payments.user-payments.error-payment-settings'));
    }
  }

  /**
   * TODO
   * @param UserPayment $payment
   * @param $amount
   * @param $currency
   * @param bool $rollback
   * @param null $userId
   * @return UserBalanceInvoice
   */
  private static function createInvoiceInternal(UserPayment $payment, $amount, $currency, $rollback = false, $userId = null)
  {
    return new UserBalanceInvoice([
      'user_id' => $userId ?: $payment->user_id,
      'user_payment_id' => $payment->id,
      'currency' => $currency,
      'amount' => $amount * ($rollback ? 1 : -1),
      'type' => $payment->invoiceType,
      'description' =>
        $rollback
          ? Yii::_t('payments.user-balance-invoices.description-payment-canceled', ['id' => $payment->id])
          : ''
    ]);
  }

  /**
   * Создает инвойс для выплаты
   * @param UserPayment $payment
   * @param null $userId
   * @param bool $rollback
   * @return UserBalanceInvoice
   */
  public static function createInvoice(UserPayment $payment, $rollback = false, $userId = null)
  {
    return self::createInvoiceInternal($payment, $payment->invoice_amount, $payment->invoice_currency, $rollback, $userId);
  }

  /**
   * Создает инвойс для выплаты реселлера
   * Для реселлера при выплате партнеру в мгмп нужно создвать инвойс в валюте выплаты c учетом процентов коммиссии за
   * процессинг, разницы процентов профита платежных систем
   * @param UserPayment $payment
   * @param null $userId
   * @param bool $rollback
   * @return UserBalanceInvoice
   */
  public static function createResellerToPartnerInvoice(UserPayment $payment, $rollback = false, $userId = null)
  {
    $requestAmount = (new self())->calcResellerPaymentAmount($payment);
    return self::createInvoiceInternal($payment, $requestAmount, $payment->currency, $rollback, $userId);
  }


  /**
   * Комиссия для выплаты !реселлеру.
   * @param $payment UserPayment
   * @return float|int
   */
  public function getResellerPaymentCommission($payment)
  {

    return $payment->getActualProcessingPercent() + ($payment->walletModel->getDefaultProfitPercent() -
        $payment->reseller_paysystem_percent + $payment->early_payment_percent);
  }

  /**
   * Сумма с наложенными процентами для выплаты !реселлеру
   * Аналог @see UserPayment::amount адаптированный для реса, так как для реса комиссия считается по-другому
   * TRICKY $payment->amount и результат этого метода - это разные значения
   * @param $payment UserPayment
   * @return float|int
   */
  public function calcResellerPaymentAmount($payment)
  {
    return round($payment->request_amount * (1 - $this->getResellerPaymentCommission($payment) / 100), 3);
  }

  /**
   * @return string
   */
  public function getFileDownloadUrl()
  {
    return ['reseller-invoices/download-file', 'invoiceId' => $this->id];
  }
}

