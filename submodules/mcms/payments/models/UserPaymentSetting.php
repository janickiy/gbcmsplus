<?php

namespace mcms\payments\models;

use mcms\common\traits\Translate;
use mcms\common\traits\model\Disabled;
use mcms\payments\components\api\WalletTypes;
use mcms\payments\components\events\EarlyPaymentIndividualPercentChanged;
use mcms\payments\components\events\PartnerAutoPaymentsDisable;
use mcms\payments\components\events\PartnerAutoPaymentsEnable;
use mcms\payments\components\events\ReferralIndividualPercentChanged;
use mcms\payments\components\events\UserCurrencyChanged;
use mcms\payments\components\events\PaymentSettingAutopayDisabled;
use mcms\payments\components\rbac\ChangeCurrencyRule;
use mcms\payments\components\UserBalance;
use mcms\payments\models\wallet\AbstractWallet;
use mcms\payments\models\wallet\Wallet;
use mcms\payments\Module;
use mcms\promo\Module as PromoModule;
use mcms\user\models\User;
use Yii;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use mcms\payments\components\CurrencyChangeLogger;

/**
 * This is the model class for table "user_payment_settings".
 *
 * @property integer $user_id
 * @property integer $is_auto_payments
 * @property integer $is_disabled
 * @property integer $is_auto_payout_disabled
 * @property integer $referral_percent
 * @property integer $visible_referral_percent
 * @property float $early_payment_percent
 * @property string $currency
 * @property mixed $currencyList
 * @property integer $is_hold_autopay_enabled
 * @property string $last_generated_payment
 * @property integer $hold_program_id
 * @property integer $partner_company_id
 * @property integer $is_wallets_manage_disabled
 *
 * @property UserWallet[] $userWallets
 * @property PartnerCompany $partnerCompany
 */
class UserPaymentSetting extends \yii\db\ActiveRecord implements \Serializable, \JsonSerializable
{
  use Translate,
    Disabled;

  const LANG_PREFIX = 'payments.user-payment-settings.';

  const CACHE_PREFIX_FETCH = 'user_payment_settings__fetch_userId';

  const VALUE_IS_ENABLED = 0;
  const VALUE_AUTOPAY_IS_DISABLED = 0;
  const VALUE_AUTOPAY_IS_ENABLED = 1;

  const SCENARIO_RESELLER_CREATE = 'reseller_create';
  const SCENARIO_RESELLER_UPDATE = 'reseller_update';
  const SCENARIO_PARTNER_UPDATE = 'partner_update';
  const SCENARIO_ADMIN_CREATE = 'admin_create';
  const SCENARIO_ADMIN_UPDATE = 'admin_update';
  const SCENARIO_SCRIPT_AUTOPAY_CHECK = 'script_autopay_check';
  const SCENARIO_PARTNER_ENABLE_AUTO_PAYMENTS = 'partner_enable_auto_payments';
  const SCENARIO_PARTNER_DISABLE_AUTO_PAYMENTS = 'partner_disable_auto_payments';
  const SCENARIO_IS_AUTO_PAYOUT_DISABLE = 'is_auto_payout_disabled';
  const SCENARIO_IS_AUTO_PAYOUT_ENABLE = 'is_auto_payout_enabled';
  const SCENARIO_GENERATE_PAYMENT = 'generate_payment';
  const SCENARIO_SET_HOLD_PROGRAM_ID = 'set_hold_program_id';
  const PAY_TERMS_WEEKLY_NET5 = 1;
  const PAY_TERMS_BI_MONTHLY_NET15 = 2;
  const PAY_TERMS_BI_MONTHLY_NET30 = 3;
  const PAY_TERMS_MONTHLY_NET7 = 4;
  const PAY_TERMS_MONTHLY_NET15 = 5;
  const PAY_TERMS_MONTHLY_NET30 = 6;

  /** @var  PromoModule */
  private $promoModule;
  private $mainCurrencies;
  private $defaultCurrency;
  private $userModule;
  private $_isEnoughSubscriptions;
  private $_isAutopayAvailable;
  /** @var  UserPayment */
  private $parentPayment;

  /** @var AbstractWallet[] */
  private $walletAccountList = [];
  private $walletTypeList = [];

  private $_currentCurrency;

  /**
   * @var string|null
   * @see canChangeCurrency()
   * @see canChangeCurrencyLastError()
   */
  private $canChangeCurrencyLastError = null;

  private static $_payTermsValues = [
    self::PAY_TERMS_WEEKLY_NET5,
    self::PAY_TERMS_BI_MONTHLY_NET15,
    self::PAY_TERMS_BI_MONTHLY_NET30,
    self::PAY_TERMS_MONTHLY_NET7,
    self::PAY_TERMS_MONTHLY_NET15,
    self::PAY_TERMS_MONTHLY_NET30,
  ];

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'user_payment_settings';
  }

  /**
   * @inheritDoc
   */
  public function __construct($config = [])
  {
    parent::__construct($config);
  }

  /**
   * @inheritDoc
   */
  public function init()
  {
    parent::init();
    /** @var PromoModule $promoModule */
    $this->promoModule = Yii::$app->getModule('promo');
    $this->userModule = Yii::$app->getModule('users');

    $promoModule = $this->promoModule;
    $this->defaultCurrency = $promoModule::MAIN_CURRENCY_RUB;
  }

  /**
   * @inheritDoc
   */
  public function afterFind()
  {
    if ($this->currency === null && $currency = $this->getSelectedCurrency()) {
      $this->currency = $currency;
    }

    parent::afterFind();
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['referral_percent'], 'default', 'skipOnEmpty' => false,
        'value' => static::getReferralPercentSettingsValue(),
      ],
      [['visible_referral_percent'], 'default', 'skipOnEmpty' => false,
        'value' => static::getVisibleReferralPercentSettingsValue(),
      ],
      [['is_disabled', 'is_wallets_manage_disabled'], 'default', 'skipOnEmpty' => true, 'value' => self::VALUE_IS_ENABLED],
      [['is_hold_autopay_enabled'], 'default', 'skipOnEmpty' => true, 'value' => self::VALUE_AUTOPAY_IS_DISABLED],

      [['user_id', 'referral_percent', 'visible_referral_percent'], 'required'],
      [['is_disabled', 'is_wallets_manage_disabled', 'is_auto_payments', 'referral_percent', 'visible_referral_percent', 'is_hold_autopay_enabled',
        'is_auto_payout_disabled', 'partner_company_id'], 'integer'],

      [['early_payment_percent'], 'number'],

      [['currency'], 'default', 'value' => function () {
        return $this->defaultCurrency;
      }],
      [['currency'], function ($attribute) {
        /* @var \mcms\promo\Module $promoModule*/
        $promoModule = Yii::$app->getModule('promo');
        //TRICKY при создании юзера если валюта запрешена но в настройках по умолчанию включена нужно создавать запись
        if (!$this->isNewRecord && !$promoModule->isCurrencyAvailable($this->currency)) {
          $this->addError($attribute, Yii::_t('payments.settings.currency_change_currency_blocked', ['currency' => $this->currency]));
        }
      }],
      [['currency'], 'in', 'range' => array_keys($this->currencyList), 'except' => [self::SCENARIO_SCRIPT_AUTOPAY_CHECK]],
      [['early_payment_percent', 'referral_percent', 'visible_referral_percent'], 'compare', 'compareValue' => 100, 'operator' => '<'],
      [['early_payment_percent', 'referral_percent', 'visible_referral_percent'], 'compare', 'compareValue' => 0, 'operator' => '>='],

      ['pay_terms', 'default', 'value' => self::PAY_TERMS_MONTHLY_NET30],
      [['pay_terms'], 'required', 'when' => function () {
        return $this->user_id && Yii::$app->getModule('users')->api('rolesByUserId', ['userId' => $this->user_id])->isPartner();
      }],
      [['pay_terms'], 'in', 'range' => self::$_payTermsValues],
    ];
  }

  /**
   * @inheritDoc
   */
  public function scenarios()
  {
    return array_merge(parent::scenarios(), [
      self::SCENARIO_RESELLER_CREATE => [
        'user_id',
        'is_auto_payments', 'is_hold_autopay_enabled', 'is_disabled', 'is_auto_payout_disabled',
        'referral_percent', 'visible_referral_percent', 'early_payment_percent', 'currency', 'pay_terms', 'is_wallets_manage_disabled',
      ],
      self::SCENARIO_RESELLER_UPDATE => [
        'is_auto_payments', 'is_hold_autopay_enabled', 'is_disabled', 'is_auto_payout_disabled',
        'referral_percent', 'visible_referral_percent', 'early_payment_percent', 'currency', 'pay_terms', 'is_wallets_manage_disabled',
      ],
      self::SCENARIO_PARTNER_UPDATE => [
        'user_id', 'is_auto_payments', 'currency'
      ],
      self::SCENARIO_ADMIN_CREATE => [
        'user_id', 'is_auto_payments', 'is_disabled', 'is_wallets_manage_disabled',
        'referral_percent', 'visible_referral_percent', 'early_payment_percent', 'currency', 'is_hold_autopay_enabled',
        'is_auto_payout_disabled',
        'pay_terms'
//        'last_generated_payment'
      ],
      self::SCENARIO_ADMIN_UPDATE => [
        'is_auto_payments', 'is_disabled', 'is_wallets_manage_disabled',
        'referral_percent', 'visible_referral_percent', 'early_payment_percent', 'currency', 'is_hold_autopay_enabled',
        'is_auto_payout_disabled'
      ],
      self::SCENARIO_SCRIPT_AUTOPAY_CHECK => [
        'user_id', 'is_auto_payments', 'is_hold_autopay_enabled',
      ],
      self::SCENARIO_PARTNER_ENABLE_AUTO_PAYMENTS => [
        'is_auto_payments'
      ],
      self::SCENARIO_PARTNER_DISABLE_AUTO_PAYMENTS => [
        'is_auto_payments'
      ],
      self::SCENARIO_IS_AUTO_PAYOUT_DISABLE => [
        'is_auto_payout_disabled'
      ],
      self::SCENARIO_IS_AUTO_PAYOUT_ENABLE => [
        'is_auto_payout_disabled'
      ],
      self::SCENARIO_SET_HOLD_PROGRAM_ID => [
        'hold_program_id'
      ],
//      self::SCENARIO_GENERATE_PAYMENT => [
//        'last_generated_payment'
//      ]
    ]);
  }

  /**
   * @inheritDoc
   */
  public function load($data, $formName = null)
  {
    if (!parent::load($data, $formName)) {
      return false;
    }

    return true;
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return array_merge(self::translateAttributeLabels([
      'user_id',
      'is_auto_payments',
      'referral_percent',
      'visible_referral_percent',
      'early_payment_percent',
      'is_disabled',
      'is_wallets_manage_disabled',
      'is_auto_payout_disabled',
      'is_hold_autopay_enabled',
      'pay_terms',
    ]), [
        'currency' => Yii::_t('payments.main.attribute-currency')
      ]
    );
  }

  /**
   * @inheritDoc
   */
  public function attributeHints()
  {
    return [
      'early_payment_percent' => static::t('early_payment_percent_hint'),
    ];
  }

  /**
   * @inheritDoc
   */
  public function beforeSave($insert)
  {
    if (!parent::beforeSave($insert)) {
      return false;
    }
    if ($this->scenario == self::SCENARIO_PARTNER_ENABLE_AUTO_PAYMENTS) {
      $this->is_auto_payments = true;
    }
    if ($this->scenario == self::SCENARIO_PARTNER_DISABLE_AUTO_PAYMENTS) {
      $this->is_auto_payments = false;
    }

    if (!$this->is_hold_autopay_enabled) {
      if ($this->scenario == self::SCENARIO_IS_AUTO_PAYOUT_DISABLE) {
        $this->is_auto_payout_disabled = false;
      }
      if ($this->scenario == self::SCENARIO_IS_AUTO_PAYOUT_ENABLE) {
        $this->is_auto_payout_disabled = true;
      }
    }

    if ($this->isAttributeChanged('early_payment_percent', false)) {
      (new EarlyPaymentIndividualPercentChanged($this))->trigger();
    }
    if ($this->isAttributeChanged('visible_referral_percent', false)) {
      (new ReferralIndividualPercentChanged($this))->trigger();
    }
    if ($this->isAttributeChanged('currency', false)) {
      (new UserCurrencyChanged($this))->trigger();
    }

    if (!$this->isNewRecord && !$this->canChangeCurrency($this->currency)) {
      $this->currency = $this->getOldAttribute('currency');
    }

    return true;
  }

  public function getCurrency()
  {
    return $this->currency ?: $this->defaultCurrency;
  }

  /**
   * Получить валюту партнера
   * Возвращается старая валюте в случае, если партнер сменил ее, но еще не вывел балланс со старого счета.
   * @return string
   */
  public function getCurrentCurrency()
  {
    $user = $this->getUser()->one();
    if (!$user->hasRole('partner')) return null;
    if ($this->_currentCurrency) return $this->_currentCurrency;

    foreach (['rub', 'usd', 'eur'] as $currency) {
      if ($currency == $this->currency) continue;

      $userBalance = new UserBalance(['userId' => $this->user_id, 'currency' => $currency]);
      // Если есть незавершенные выплаты или баланс не обнулен, берем старую валюту
      // TODO При мерже учесть, что теперь наличие выплат игнорируется
      if ($userBalance->getBalance() > 0) {
        return $this->_currentCurrency = $currency;
      }
    }
    return $this->_currentCurrency = $this->currency;
  }

  /**
   * Вернет true, если партнер сменил валюту, но еще не вывел весь балланс со старой
   * @return bool
   */
  public function isCurrencyChanged()
  {
    return $this->currency !== $this->getCurrentCurrency();
  }

  public function getCurrencyLabel()
  {
    return ArrayHelper::getValue($this->currencyList, $this->getSelectedCurrency());
  }

  public function getCurrencyList()
  {
    if ($this->mainCurrencies !== null) {
      return $this->mainCurrencies;
    }
//    $userModule = $this->userModule;

    $currencyApi = 'mainCurrencies';


//    if (
//      $this->scenario !== self::SCENARIO_SCRIPT_AUTOPAY_CHECK &&
//      $this->isReseller()
//    ) {
//      $currencyApi = 'resellerCurrencies';
//    }

    return $this->mainCurrencies = $this->promoModule->api($currencyApi)->setMapParams(['code', 'symbol'])->getMap();
  }

  /**
   * @return User
   */
  public function getUser()
  {
    return $this->hasOne(User::class, ['id' => 'user_id']);
  }

  public function isReseller()
  {
    $userModule = $this->userModule;
    return $this->user->hasRole($userModule::RESELLER_ROLE);
  }

  public function getReplacements()
  {
    return [
      'user' => [
        'value' => $this->isNewRecord ? null : $this->getUser()->one()->getReplacements(),
        'helper' => [
          'class' => Yii::$app->user->identityClass,
          'label' => self::translate('attribute-user-id'),
        ]
      ],
      'is_auto_payments' => [
        'value' => $this->is_auto_payments,
        'helper' => [
          'label' => self::translate('attribute-is-auto-payments')
        ]
      ],
      'is_hold_autopay_enabled' => [
        'value' => $this->is_hold_autopay_enabled,
        'helper' => [
          'label' => self::translate('attribute-is_hold_autopay_enabled')
        ]
      ],
      'is_disabled' => [
        'value' => $this->is_disabled,
        'helper' => [
          'label' => self::translate('attribute-is_disabled')
        ]
      ],
      'is_wallets_manage_disabled' => [
        'value' => $this->is_wallets_manage_disabled,
        'helper' => [
          'label' => self::translate('attribute-is_wallets_manage_disabled')
        ]
      ],
      'is_auto_payout_disabled' => [
        'value' => $this->is_auto_payout_disabled,
        'helper' => [
          'label' => self::translate('attribute-is_auto_payout_disabled')
        ]
      ],
      'referral_percent' => [
        'value' => $this->visible_referral_percent,
        'helper' => [
          'label' => self::translate('attribute-referral-percent')
        ]
      ],
      'early_payment_percent' => [
        'value' => $this->getEarlyPercent(),
        'helper' => [
          'label' => self::translate('attribute-early-payment-percent')
        ]
      ],
      'currency' => [
        'value' => $this->currency,
        'helper' => [
          'label' => Yii::_t('payments.main.attribute-currency')
        ]
      ]
    ];
  }

  /**
   * @param $user_id
   * @return UserPaymentSetting
   */
  public static function fetch($user_id)
  {
    $key = self::CACHE_PREFIX_FETCH . $user_id;

    if (($fromCache = Yii::$app->cache->get($key)) !== false) {
      $model = new UserPaymentSetting($fromCache);
      $model->isNewRecord = false;
      return $model;
    }

    if (!$model = static::findOne(['user_id' => $user_id])) {
      $model = new UserPaymentSetting([
        'user_id' => $user_id,
        'scenario' => self::SCENARIO_ADMIN_CREATE
      ]);
      $model->validate();
    }

    Yii::$app->cache->set($key, $model->attributes, 60 * 5);

    return $model;
  }

  /**
   * @inheritDoc
   */
  public function serialize()
  {
    $data = $this->getAttributes();
    $serialized = serialize($data);

    return $serialized ?: null;
  }

  /**
   * @inheritDoc
   */
  public function unserialize($serialized)
  {
    $data = unserialize($serialized);

    $this::populateRecord($this, array_intersect_key($data, array_flip($this->attributes())));
    $this->init();

    if ($this->canUseMultipleCurrenciesBalance() && $currency = $this->getSelectedCurrency()) {
      $this->currency = $currency;
    }
  }


  /**
   * Получение значения для реферального процента из настроек модуля
   * @return type
   */
  public static function getReferralPercentSettingsValue()
  {
    return Yii::$app->getModule('payments')->getReferralPercentSettingsValue();
  }

  /**
   * Получение значения для реферального процента из настроек модуля
   * @return type
   */
  public static function getVisibleReferralPercentSettingsValue()
  {
    return Yii::$app->getModule('payments')->getVisibleReferralPercentSettingsValue();
  }

  /**
   * Процент за досрочную выплату.
   * Если для пользователя указан свой процент, использует его. Иначе используется процент указанный в настройках модуля.
   * TRICKY Исторически сложилось, что процент всегда положительный, но при этом сумма выплаты уменьшается на указанный процент,
   * а не увеличивается
   *
   * Как учитывается процент за создание выплаты?
   * - в партнерке при передаче ПС во вьюху к проценту реселлера накидывается процент за создание
   * - при создании выплаты проверяется, может ли пользователь создавать выплаты без процента за создания,
   * если нет, процент применяется на сумму и записывается в выплату в отдельное поле
   * - учитывается при отображении в списке, деталке и прочем в методе @see UserPayment::calcResellerCommission()
   * @return number
   */
  public function getEarlyPercent()
  {
    // Если пользователь может создавать выплату без досрочной комиссии, то комиссия равна нулю
    if (Module::canCreatePaymentWithoutEarlyCommission($this->user_id)) {
      return 0;
    }

    /** @var Module $module */
    $module = Yii::$app->getModule('payments');
    return is_null($this->early_payment_percent)
      ? $module->getEarlyPercentSettingsValue()
      : $this->early_payment_percent;
  }

  public function afterSave($insert, $changedAttributes)
  {
    parent::afterSave($insert, $changedAttributes);

    // Если сменили валюту, логируем в currency_log
    $currencyChanged = ArrayHelper::getValue($changedAttributes, 'currency');
    if ($currencyChanged) {
      (new CurrencyChangeLogger($this))->log($currencyChanged);
    }

    if ($this->scenario === self::SCENARIO_SCRIPT_AUTOPAY_CHECK) {
      if ($this->is_auto_payments === self::VALUE_AUTOPAY_IS_DISABLED
        && $changedAttributes['is_auto_payments'] === self::VALUE_AUTOPAY_IS_ENABLED
      ) {
        (new PaymentSettingAutopayDisabled($this))->trigger();
      }
    }

    if ($this->scenario == self::SCENARIO_PARTNER_DISABLE_AUTO_PAYMENTS) {
      (new PartnerAutoPaymentsDisable($this))->trigger();
    }
    if ($this->scenario == self::SCENARIO_PARTNER_ENABLE_AUTO_PAYMENTS) {
      (new PartnerAutoPaymentsEnable($this))->trigger();
    }

    Yii::$app->cache->delete(self::CACHE_PREFIX_FETCH . $this->user_id);
  }

  /**
   * @param UserWallet $userWallet
   * @return bool
   */
  public function canAddPayment(UserWallet $userWallet)
  {
    if (!$userWallet || empty($userWallet->wallet_type)) return false;

    $walletAccount = $userWallet->getAccountObject();
    return $walletAccount instanceof AbstractWallet && !$walletAccount->isEmpty();
  }

  /**
   * @param UserWallet $userWallet
   * @return bool
   */
  public function canRequestPayments(UserWallet $userWallet)
  {
    return
      $this->isPaymentsEnabled() &&
      $this->canAddPayment($userWallet);
  }

  private static $_canUseMultipleCurrenciesBalanaceCache = [];
  public function canUseMultipleCurrenciesBalance()
  {
    if (!isset(self::$_canUseMultipleCurrenciesBalanaceCache[$this->user_id])) {
      self::$_canUseMultipleCurrenciesBalanaceCache[$this->user_id] = Yii::$app->user->can('PaymentsCanUseMultipleCurrenciesBalance', ['model' => $this]);
    }
    return self::$_canUseMultipleCurrenciesBalanaceCache[$this->user_id];
  }

  /**
   * Есть ли возможность изменить валюту
   * @see ChangeCurrencyRule
   * @return bool
   */
  public function canChangeCurrency($currency = null)
  {
    $params = ['userId' => $this->user_id, 'currency' => $currency];
    $hasAccess = Yii::$app->user->can('PaymentsCanChangeCurrency', $params);

    $error = null;
    if (!$hasAccess) {
      $error = (new ChangeCurrencyRule)->getLastError($params);
      // Если правило не вернуло ошибку, значит проверка не пройдена еще на этапе проверки пермишина
      if (!$error) $error = Yii::_t('payments.settings.currency_change_insufficient_rights');
    }

    $this->canChangeCurrencyLastError = $hasAccess ? null : $error;

    return $hasAccess;
  }

  /**
   * Причина отказа доступа к смене валюты
   * @see canChangeCurrency()
   * @return null|string
   */
  public function canChangeCurrencyLastError()
  {
    return $this->canChangeCurrencyLastError;
  }

  public function canChangeWallet()
  {
    return Yii::$app->user->can('PaymentsCanChangeWallet', ['userId' => $this->user_id]);
  }

  /**
   * Запрещено ли редактирование своих кошельков
   * @return bool
   */
  public function getIsWalletsManageDisabled()
  {
    /** @var Module $paymentModule */
    $paymentModule = Yii::$app->getModule('payments');

    return $paymentModule->getIsWalletsManageDisabledGlobally()
      || $this->is_wallets_manage_disabled
      || !$this->canChangeWallet();
  }

  /**
   * @return null
   */
  public function getSelectedCurrency()
  {
    if ($this->canUseMultipleCurrenciesBalance()) {
      return Module::getSelectedCurrency();
    }

    return null;
  }

  // TODO Убедится, что мы реализовали подобную логику

  /**
   * @param mixed $parentPayment
   * @return $this
   */
  public function setParentPayment(&$parentPayment)
  {
    if (!$parentPayment instanceof UserPayment) {
      return $this;
    }

    $this->parentPayment = $parentPayment;

    // TODO: НАХЕР ЭТА СТРОКА? ЭТО Ж КАПЕЦ КАКОЙ-ТО! Зачем юзеру присваиваем валюту платежа?
    $this->currency = $this->parentPayment->currency ?: $this->currency;

    return $this;
  }

  /**
   * @return UserPayment
   */
  public function getParentPayment()
  {
    return $this->parentPayment;
  }

  public function jsonSerialize()
  {
    return ArrayHelper::toArray($this);
  }

  /**
   * @return int
   */
  public function isDisabled()
  {
    return $this->is_disabled;
  }

  /**
   * Учитывает глобальную настройку
   * @return bool
   */
  public function isPaymentsEnabled()
  {
    return !$this->is_disabled && !Yii::$app->getModule('payments')->isSettingsDisabled();
  }

  /**
   * @return bool
   */
  public function isEmptyWalletAccount()
  {
    return (!$this->wallet_account instanceof AbstractWallet) || $this->wallet_account->isEmpty();
  }

  /**
   * @param bool $filterByCurrency
   * @return ActiveQuery
   */
  public function getUserWallets($filterByCurrency = true)
  {
    $link = ['user_id' => 'user_id'];
    if ($filterByCurrency) $link[] = ['currency' => 'currency'];

    return $this->hasMany(UserWallet::class, $link);
  }

  /**
   * @return ActiveQuery
   */
  public function getPartnerCompany()
  {
    return $this->hasOne(PartnerCompany::class, ['id' => 'partner_company_id']);
  }

  /**
   * @return ActiveQuery
   */
  public function getResellerCompany()
  {
    return $this->hasOne(Company::class, ['id' => 'reseller_company_id'])
      ->via('partnerCompany');
  }

  /**
   * Сценарий для редактирования настроек
   * @return string
   */
  public static function getUpdateSettingsScenario()
  {
    $scenario = Yii::$app->getUser()->can(Module::PERMISSION_CAN_CHANGE_USER_ADMIN_SETTINGS)
      ? UserPaymentSetting::SCENARIO_ADMIN_CREATE
      : UserPaymentSetting::SCENARIO_RESELLER_CREATE;

    return $scenario;
  }

  /**
   * Возвращает массив типов payTerms
   * @return array
   */
  public static function getPayTerms($key = null)
  {
    $result = [
      self::PAY_TERMS_WEEKLY_NET5 => Yii::_t('payments.user-payment-settings.pay_terms_weekly_net5'),
      self::PAY_TERMS_BI_MONTHLY_NET15 => Yii::_t('payments.user-payment-settings.pay_terms_bi_monthly_net15'),
      self::PAY_TERMS_BI_MONTHLY_NET30 => Yii::_t('payments.user-payment-settings.pay_terms_bi_monthly_net30'),
      self::PAY_TERMS_MONTHLY_NET7 => Yii::_t('payments.user-payment-settings.pay_terms_monthly_net7'),
      self::PAY_TERMS_MONTHLY_NET15 => Yii::_t('payments.user-payment-settings.pay_terms_monthly_net15'),
      self::PAY_TERMS_MONTHLY_NET30 => Yii::_t('payments.user-payment-settings.pay_terms_monthly_net30'),
    ];
    return $key ? ArrayHelper::getValue($result, $key) : $result;
  }

  /**
   * Возвращает подпись текущего значения payTerms
   * @return array
   */
  public function getPayTermValue()
  {
    return self::getPayTerms($this->pay_terms);
  }
}
