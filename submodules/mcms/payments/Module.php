<?php

namespace mcms\payments;

use mcms\payments\components\exchanger\GeoApiExchanger;
use mcms\payments\components\mgmp\send\ApiMgmpSender;
use mcms\payments\components\mgmp\send\FakeMgmpSender;
use mcms\payments\components\resellerStatistic\PaymentsStatFetcher;
use mcms\promo\components\api\MainCurrenciesWidget;
use mcms\statistic\models\resellerStatistic\PaymentsStatFetchInterface;
use Yii;
use yii\console\Application as ConsoleApplication;
use yii\console\Controller;
use yii\helpers\ArrayHelper;

/**
 * Модуль выплат
 * Class Module
 * @package mcms\payments
 */
class Module extends \mcms\common\module\Module
{
  const SETTINGS_MGMP_RESELLER_ID = 'settings.mgmp.reseller_id';
  const SETTINGS_MGMP_URL = 'settings.mgmp.url';
  const SETTINGS_MGMP_SECRET_KEY = 'settings.mgmp.secret_key';
  public $params;
  public $controllerNamespace = 'mcms\payments\controllers';

  const SETTINGS_PROJECT_ID = 'settings.project_id';
  const SETTINGS_REFERRAL_PERCENT_PROFIT = 'settings.referral_percent_profit';
  const SETTINGS_VISIBLE_REFERRAL_PERCENT_PROFIT = 'settings.visible_referral_percent_profit';
  const SETTINGS_EARLY_PAYMENT_PERCENT = 'settings.early_payment_percent';
  const SETTINGS_CURRENCY = 'settings.currency';

  /** @const string Дата левой границы для рассчета реселлера. Берется из params-local */
  const SETTINGS_LEFT_BORDER_DATE = 'leftBorderDate';
  const SETTINGS_ALTERNATIVE_PAYMENTS_GRID_VIEW = 'settings.alternative_payments_grid_view';
  const SETTINGS_EXCHANGE_PERCENT_USD_RUR = 'settings.payments.exchange_percent_usd_rur';
  const SETTINGS_EXCHANGE_PERCENT_RUR_USD = 'settings.payments.exchange_percent_rur_usd';
  const SETTINGS_EXCHANGE_PERCENT_USD_EUR = 'settings.payments.exchange_percent_usd_eur';
  const SETTINGS_EXCHANGE_PERCENT_EUR_USD = 'settings.payments.exchange_percent_eur_usd';
  const SETTINGS_EXCHANGE_PERCENT_EUR_RUR = 'settings.payments.exchange_percent_eur_rur';
  const SETTINGS_EXCHANGE_PERCENT_RUR_EUR = 'settings.payments.exchange_percent_rur_eur';

  const SETTINGS_DELAY_LEVEL1 = 'settings.delay_level1';
  const SETTINGS_DELAY_LEVEL2 = 'settings.delay_level2';

  const SETTINGS_WALLETS_MANAGE_DISABLED_GLOBALLY = 'settings.wallets_manage_disabled_globally';

  const SETTING_DEFAULT_INVOICING_CYCLE = 'settings.default_invoicing_cycle';
  const SETTING_DEFAULT_INVOICING_CYCLE_OFF = 0;
  const SETTING_DEFAULT_INVOICING_CYCLE_MONTHLY = 1;
  const SETTING_DEFAULT_INVOICING_CYCLE_BIWEEKLY = 2;
  const SETTING_DEFAULT_INVOICING_CYCLE_WEEKLY = 3;

  const SETTING_IS_PAYMENTS_DISABLED = 'setting.disable_payments';

  const PERMISSION_CAN_DISABLE_USER_PAYMENTS = 'PaymentsUsersDisableUserPayments';
  const PERMISSION_CAN_ENABLE_AUTOPAY = 'PaymentsUsersCanEnableAutopay';
  const PERMISSION_CAN_CHANGE_USER_ADMIN_SETTINGS = 'PaymentsUsersCanChangeAdminSettings';
  const PERMISSION_CAN_VIEW_SYSTEM_WALLET_BALANCES = 'PaymentsViewSystemWalletBalances';
  const PERMISSION_CAN_EDIT_PAYED_PAYMENTS = 'PaymentsEditPayedPayments';
  const PERMISSION_CAN_CHANGE_PAYMENT_AMOUNT = 'PaymentsUsersCanChangeAmount';
  const PERMISSION_PAYMENTS_RESELLER_SETTINGS = 'PaymentsResellerSettings';
  const PERMISSION_EDIT_DELAY_SETTINGS = 'PaymentsEditDelaySettings';
  const PERMISSION_EDIT_MAIN_SETTINGS = 'PaymentsEditMainSettings';
  const PERMISSION_EDIT_MGMP_SETTINGS = 'PaymentsEditMgmpSettings';
  const PERMISSION_EDIT_WM_SETTINGS = 'PaymentsEditWmSettings';

  const PERMISSION_CAN_USER_HAVE_WALLETS = 'CanUserHaveWallets';
  const PERMISSION_CAN_USER_HAVE_BALANCE = 'CanUserHaveBalance';
  const PERMISSION_CAN_PROCESS_ALL_PAYMENTS = 'canProcessAllPayments';

  const PERMISSION_CAN_REQUEST_PAYMENT_WITHOUT_EARLY_COMMISSION = 'PaymentsCanRequestPaymentWithoutCreateCommission';
  const PERMISSION_CAN_CHANGE_PAYMENT_EARLY_COMMISSION = 'PaymentsCanChangePaymentCreatePercent';

  const PERMISSION_CAN_VERIFY_USER_WALLETS = 'PaymentsUsersVerifyWallet';

  const PARAM_AUTO_PAYMENT_WALLETS = 'autoPaymentWallets';
  const PARAM_WALLETS = 'wallets';

  const USD = 'usd';
  const RUB = 'rub';
  const EUR = 'eur';

  public function init()
  {
    parent::init();

    if (Yii::$app instanceof ConsoleApplication) {
      $this->controllerNamespace = 'mcms\payments\commands';
    }

    // Если не MGMP URL пустой, то только делаем вид что что-то отправляем
    $mgmpSender = $this->getMgmpUrl() ? ApiMgmpSender::class : FakeMgmpSender::class;
    Yii::$container->setSingleton('mcms\payments\components\mgmp\send\MgmpSenderInterface', $mgmpSender);

    Yii::$container->setSingleton('mcms\payments\components\RemoteWalletBalances');

    // для статистики реселлера надо из модуля статы использовать этот фетчер
    Yii::$container->set(PaymentsStatFetchInterface::class, PaymentsStatFetcher::class);
  }

  /**
   * Может ли пользователь иметь кошельки
   * @param $userId
   * @return bool
   */
  public static function canUserHaveWallets($userId)
  {
    return Yii::$app->authManager->checkAccess($userId, self::PERMISSION_CAN_USER_HAVE_WALLETS);
  }

  /**
   * Может ли пользователь иметь баланс
   * @param $userId
   * @return bool
   */
  public static function canUserHaveBalance($userId)
  {
    return Yii::$app->authManager->checkAccess($userId, self::PERMISSION_CAN_USER_HAVE_BALANCE);
  }

  /**
   * Может ли пользователь создавать выплаты без комиссии за досрочность (не только себе)
   * @param int $userId
   * @return bool
   */
  public static function canCreatePaymentWithoutEarlyCommission($userId)
  {
    return Yii::$app->authManager->checkAccess($userId, self::PERMISSION_CAN_REQUEST_PAYMENT_WITHOUT_EARLY_COMMISSION);
  }

  /**
   * @return float|null
   */
  public function getReferralPercentSettingsValue()
  {
    return $this->settings->getValueByKey(self::SETTINGS_REFERRAL_PERCENT_PROFIT);
  }

  /**
   * @return float|null
   */
  public function getVisibleReferralPercentSettingsValue()
  {
    return $this->settings->getValueByKey(self::SETTINGS_VISIBLE_REFERRAL_PERCENT_PROFIT);
  }

  /**
   * Комиссия за досроную выплату
   * @return number
   */
  public function getEarlyPercentSettingsValue()
  {
    return $this->settings->offsetExists(self::SETTINGS_EARLY_PAYMENT_PERCENT)
      ? $this->settings->getValueByKey(self::SETTINGS_EARLY_PAYMENT_PERCENT, 0) : 0;
  }

  /**
   * @return string|null
   */
  public function getProjectId()
  {
    return $this->settings->getValueByKey(self::SETTINGS_PROJECT_ID);
  }

  /**
   * @return string|null
   */
  public function getLeftBorderDate()
  {
    return Yii::$app->params[self::SETTINGS_LEFT_BORDER_DATE];
  }

  /**
   * Показывать альтернативный вид грида выплат. В редактировании партнера появляется поле Pay terms,
   * добавляется соответствующая колонка в выплатах.  Задача MCMS-1636
   * @return string|null
   */
  public function isAlternativePaymentsGridView()
  {
    return $this->settings->getValueByKey(self::SETTINGS_ALTERNATIVE_PAYMENTS_GRID_VIEW);
  }

  /**
   * Процент конвертации валюты USD/RUR
   * @return float
   */
  public function getExchangePercentUsdRur()
  {
    return (float)$this->settings->getValueByKey(self::SETTINGS_EXCHANGE_PERCENT_USD_RUR);
  }

  /**
   * Процент конвертации валюты RUR/USD
   * @return float
   */
  public function getExchangePercentRurUsd()
  {
    return (float)$this->settings->getValueByKey(self::SETTINGS_EXCHANGE_PERCENT_RUR_USD);
  }

  /**
   * Процент конвертации валюты USD/EUR
   * @return float
   */
  public function getExchangePercentUsdEur()
  {
    return (float)$this->settings->getValueByKey(self::SETTINGS_EXCHANGE_PERCENT_USD_EUR);
  }

  /**
   * Процент конвертации валюты EUR/USD
   * @return float
   */
  public function getExchangePercentEurUsd()
  {
    return (float)$this->settings->getValueByKey(self::SETTINGS_EXCHANGE_PERCENT_EUR_USD);
  }

  /**
   * Процент конвертации валюты EUR/RUR
   * @return float
   */
  public function getExchangePercentEurRur()
  {
    return (float)$this->settings->getValueByKey(self::SETTINGS_EXCHANGE_PERCENT_EUR_RUR);
  }

  /**
   * Процент конвертации валюты RUR/EUR
   * @return float
   */
  public function getExchangePercentRurEur()
  {
    return (float)$this->settings->getValueByKey(self::SETTINGS_EXCHANGE_PERCENT_RUR_EUR);
  }

  /**
   * @return string
   */
  public function getExchangerSourceclass
  {
    return GeoApiExchanger::class;
  }

  /**
   * @return null
   */
  public static function getSelectedCurrency()
  {
    if (Yii::$app->controller instanceof Controller) {
      return null;
    }
    /** @var \mcms\promo\Module $promo */
    $promo = Yii::$app->getModule('promo');
    /** @var MainCurrenciesWidget $api */
    $api = $promo->api('mainCurrenciesWidget');
    return $api->getSelectedCurrency();
  }

  /**
   * @return mixed|null
   */
  public function getMgmpUrl()
  {
    return $this->settings->offsetExists(self::SETTINGS_MGMP_URL)
      ? $this->settings->getValueByKey(self::SETTINGS_MGMP_URL)
      : null;
  }

  /**
   * @return mixed|null
   */
  public function getMgmpResellerId()
  {
    return $this->settings->getValueByKey(self::SETTINGS_MGMP_RESELLER_ID);
  }

  /**
   * @return mixed|null
   */
  public function getMgmpSecretKey()
  {
    return $this->settings->getValueByKey(self::SETTINGS_MGMP_SECRET_KEY);
  }


  /**
   * TRICKY: для реса прячем поля: название, инфо, флаг мультикошельков, выбор реквизитов апи процессинга, дисейблим поля лимитов. для админа и рута оставляем всё как есть
   *
   * То есть если есть данный пермишен, то доступно для редактирования всё. Иначе только указаные выше поля.
   * @return bool
   */
  public function isUserCanEditAllWalletFields()
  {
    return Yii::$app->user->can('PaymentsWalletEditAllFields');
  }

  /**
   * @return bool
   */
  public function isUserCanProcessAllPayments()
  {
    return Yii::$app->user->can(self::PERMISSION_CAN_PROCESS_ALL_PAYMENTS);
  }

  /**
   * @return bool
   */
  public static function isUserCanVerifyWallets()
  {
    return Yii::$app->user->can(self::PERMISSION_CAN_VERIFY_USER_WALLETS);
  }

  /**
   * Процент за процессинг
   * @return float|integer
   */
  public static function getProcessingPercent()
  {
    return ArrayHelper::getValue(Yii::$app->params, 'processingPercent', 0);
  }

  /**
   * Индивидуальный процент реселлера
   * @return float|integer
   */
  public static function getResellerPercent()
  {
    return ArrayHelper::getValue(Yii::$app->params, 'resellerPercent', 0);
  }

  /**
   * @return int|null
   */
  public function getDelayLevel1()
  {
    return $this->settings->getValueByKey(self::SETTINGS_DELAY_LEVEL1);
  }

  /**
   * @return int|null
   */
  public function getDelayLevel2()
  {
    return $this->settings->getValueByKey(self::SETTINGS_DELAY_LEVEL2);
  }

  /**
   * Разрешено ли глобально менять настройки своих кошельков
   * @return float
   */
  public function getIsWalletsManageDisabledGlobally()
  {
    return (bool)$this->settings->getValueByKey(self::SETTINGS_WALLETS_MANAGE_DISABLED_GLOBALLY);
  }

  /**
   * периодичность выплат по-умолчанию
   * @return int
   */
  public function getDefaultInvoicingCycle()
  {
    return (int)$this->settings->getValueByKey(self::SETTING_DEFAULT_INVOICING_CYCLE);
  }

  /**
   * Отключены ли выплаты глобально
   * @return bool
   */
  public function isSettingsDisabled()
  {
    return (bool)$this->settings->getValueByKey(self::SETTING_IS_PAYMENTS_DISABLED);
  }
}
