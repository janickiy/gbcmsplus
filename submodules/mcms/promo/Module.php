<?php

namespace mcms\promo;

use mcms\promo\components\PartnerProgramSync;
use mcms\promo\components\provider_instances_sync\api_drivers\ApiDriverInterface;
use mcms\promo\components\provider_instances_sync\api_drivers\HttpApiDriver;
use mcms\promo\models\Currency;
use mcms\promo\models\PartnerProgramItem;
use mcms\promo\models\UserPromoSetting;
use Yii;
use mcms\common\module\Module as CommonModule;
use yii\console\Application as ConsoleApplication;
use yii\db\ActiveRecord;

class Module extends CommonModule
{
  public $controllerNamespace = 'mcms\promo\controllers';

  /**
   * @var array разрешенные доменные зоны
   */
  public $acceptedDomainZones;

  const SETTINGS_USE_MAIN_REBILL_PERCENT_AS_PERSONAL_FOR_NEW_USERS = 'settings.use_default_rebill_percent_for_new_users';
  const SETTINGS_USE_MAIN_BUYOUT_PERCENT_AS_PERSONAL_FOR_NEW_USERS = 'settings.use_default_buyout_percent_for_new_users';
  const SETTINGS_MAIN_REBILL_PERCENT_FOR_PARTNER = 'settings.main_rebill_percent_for_partner';
  const SETTINGS_MAIN_BUYOUT_PERCENT_FOR_PARTNER = 'settings.main_buyout_percent_for_partner';
  const SETTINGS_LANDING_RATING_FOR_WEBMASTER_SOURCE = 'settings.use_default_landing_rating_for_webmaster_source';
  const SETTINGS_CAN_MANAGE_PERSONAL_CPA_PRICE = 'settings.can_manage_personal_cpa_price';
  const SETTINGS_ALLOW_SOURCE_REDIRECT = 'settings.is_allowed_source_redirect';

  const SETTINGS_DOMAIN_IP = 'settings.domain_ip';

  const SETTINGS_LAND_CONVERT_TEST_MIN_RATING = 'settings.land_convert_test_min_rating';
  const SETTINGS_LAND_CONVERT_TEST_MAX_HITS = 'settings.land_convert_test_max_hits';

  const SETTINGS_API_HANDLER_CLEAR_CACHE_URL_PATH = 'settings.api_handler_clear_cache_url_path';
  const SETTINGS_API_HANDLER_PATH = 'settings.api_handler_path';
  const SETTINGS_API_HANDLER_CLEAR_CACHE_TYPE = 'settings.api_handler_clear_cache_type';
  const SETTINGS_API_HANDLER_CLEAR_CACHE_TYPE_URL = 'url';
  const SETTINGS_API_HANDLER_CLEAR_CACHE_TYPE_CONSOLE = 'console';

  const SETTINGS_MOBLEADERS_ID = 'mobleaders_user_id';
  const SETTINGS_RESELLER_HIDE_PROMO = 'reseller_hide_promo';

  const SETTINGS_ARBITRARY_LINK_MODERATION = 'settings.arbitrary_link_moderation';

  const SETTINGS_MAX_REBILL_CONDITIONS_PERCENT = 'settings.max_rebill_conditions_percent';

  const SETTINGS_DEFAULT_TB_URL = 'settings.default_tb_url';

  const SETTINGS_ENABLE_TB_SELL = 'settings.enable_tb_sell';
  const SETTINGS_DISABLE_CHECK_DOMAIN = 'settings.disable_check_domain';
  const SETTINGS_CLICK_N_CONFIRM_TEXT_RU = 'settings.click_n_conform_text_ru';
  const SETTINGS_CLICK_N_CONFIRM_TEXT_EN = 'settings.click_n_conform_text_en';

  const SETTINGS_LINKS_REPLACEMENT_CLASS = 'settings.links_replacement_class';

  // Настройка включения выкупа для партнеров
  const SETTINGS_ENABLE_BUYOUT_FOR_PARTNERS = 'settings.enable_buyout_for_partners';

  // Настройка Среднее количество ребиллов на подписку
  const SETTINGS_AVR_NUMBER_REBILL_PER_SUBSCRIPTION = 'settings.avr_number_rebill_per_subscription';

  const MAIN_CURRENCY_USD = 'usd';
  const MAIN_CURRENCY_EUR = 'eur';
  const MAIN_CURRENCY_RUB = 'rub';

  const PERMISSION_CAN_VIEW_BLOCKED_LANDINGS = 'PromoCanViewBlockedLandings';
  const PERMISSION_CAN_VIEW_PERSONAL_PROFITS_WIDGET = 'PromoCanViewPersonalProfitsWidget';
  const PERMISSION_CAN_VIEW_OWN_PERSONAL_PROFITS_WIDGET = 'PromoCanViewOwnPersonalProfitsWidget';

  const PERMISSION_CAN_RESELLER_HIDE_PROMO = 'PromoCanResellerHidePromo';
  const PERMISSION_CAN_PARTNER_VIEW_PROMO = 'PromoPartnerHidePromo';
  const PERMISSION_CAN_VIEW_REBILL_CONDITIONS_WIDGET = 'PromoCanViewRebillConditionsWidget';

  const PERMISSION_USE_NOT_PERSONAL_PERCENT = 'PromoUseNotPersonalPercent';
  const PERMISSION_APPLY_PERSONAL_PERCENT_AS_RESELLER = 'PromoApplyPersonalPercentsAsReseller';
  const PERMISSION_APPLY_PERSONAL_PERCENT_AS_ROOT = 'PromoApplyPersonalPercentsAsRoot';

  const PERMISSION_PROMO_MANAGE_PERSONAL_CPA_PRICE = 'PromoManagePersonalCPAPrice';
  const PERMISSION_PROMO_MANAGE_PERSONAL_CPA_PRICE_IF_ENABLED = 'PromoManagePersonalCPAPriceIfEnabled';

  const PERMISSION_CAN_EDIT_MAIN_SETTINGS = 'PromoCanEditMainSettings';
  const PERMISSION_CAN_EDIT_FAKE_REVSHARE_SETTINGS = 'PromoCanEditFakeRevshareSettings';
  const PERMISSION_CAN_EDIT_INDIVIDUAL_FAKE_SETTINGS = 'PromoCanEditIndividualFakeSettings';
  const PERMISSION_CAN_EDIT_LANDINGS_SETTINGS = 'PromoCanEditLandingsSettings';
  const PERMISSION_CAN_EDIT_DEFAULT_TB_URL = 'PromoCanEditDefaultTBUrlSettings';

  const PERMISSION_CAN_EDIT_DEFAULT_CLICK_N_CONFIRM_TEXT = 'PromoCanEditDefaultClickNConfirmText';
  const PERMISSION_CAN_EDIT_LINKS_REPLACEMENT_PERCENT = 'PromoCanEditLinksReplacementPercent';
  const PERMISSION_CAN_EDIT_LINKS_REPLACEMENT_CLASS = 'PromoCanEditLinksReplacementClass';

  const SETTINGS_FAKE_ADD_AFTER_SUBSCRIPTIONS = 'promo.settings.fake.add_after_subscriptions';
  const SETTINGS_FAKE_ADD_SUBSCRIPTION_PERCENT = 'promo.settings.fake.add_subscription_percent';
  const SETTINGS_FAKE_OFF_SUBSCRIPTION_DAYS = 'promo.settings.fake.off_subscription_days';
  const SETTINGS_FAKE_OFF_SUBSCRIPTION_PERCENT_BEFORE_DAYS = 'promo.settings.fake_off_subscriptions_percent_before_days';
  const SETTINGS_FAKE_ADD_CPA_SUBSCRIPTION_PERCENT = 'promo.settings.fake.add_cpa_subscription_percent';
  const SETTINGS_FAKE_OFF_SUBSCRIPTION_MAX_REJECTION = 'promo.settings.fake.fake_off_subscriptions_max_rejection';
  const SETTINGS_GLOBAL_ENABLE_FAKE_TO_USERS = 'promo.settings.global_enable_fake_to_users';
  const SETTINGS_INDIVIDUAL_FAKE_SETTINGS_ENABLE = 'promo.settings.individual_fake_settings_enable';
  const PERMISSION_CAN_EDIT_USER_FAKE_REVSHARE_FLAG = 'PromoCanEditUserFakeRevshareFlag';

  const PERMISSION_CAN_EDIT_STATIC_PAGES_CONFIG = 'PromoCanEditStaticPagesConfig';

  const SETTINGS_GLOBAL_ALLOW_FORCE_OPERATOR = 'settings.global_allow_force_operator';

  const SETTINGS_IS_LANDINGS_AUTO_ROTATION_GLOBAL_ENABLED = 'settings.is_landings_auto_rotation_global_enabled';
  const SETTINGS_MIN_COUNT_HITS_ON_LANDING = 'settings.min_count_landings_in_source';
  const SETTINGS_NEW_LANDINGS_CHANCE = 'settings.new_landings_chance';

  const SETTINGS_AFTER_N_HITS = 'settings.sell_tb.after_n_hits';
  const SETTINGS_AFTER_N_HOURS_DAYS = 'settings.sell_tb.after_n_hours_days';
  const SETTINGS_HOURS_DAYS = 'settings.sell_tb.hours_days';
  const SETTINGS_AND_OR = 'settings.sell_tb.and_or';

  const SETTING_AVAILABLE_RUB = 'settings.partner_available_currencies.rub';
  const SETTING_AVAILABLE_USD = 'settings.partner_available_currencies.usd';
  const SETTING_AVAILABLE_EUR = 'settings.partner_available_currencies.eur';

  const SETTINGS_PROVIDERS_API_TOKEN = 'settings.providers_api_token';
  const SETTINGS_KP_USER_ID = 'settings.kp_user_id';
  const SETTINGS_KP_SECRET_KEY = 'settings.kp_secret_key';
  const SETTINGS_POSTBACK_URL = 'settings.postback_url';
  const SETTINGS_TRAFFICBACK_URL = 'settings.trafficback_url';
  const SETTINGS_COMPLAINS_URL = 'settings.complains_url';
  const SETTINGS_KP_API_USER_AUTH_EMAIL = 'settings.kp_api_user_auth_email';
  const SETTINGS_KP_API_USER_AUTH_HASH = 'settings.kp_api_user_auth_hash';

  const PERMISSION_CAN_CHANGE_OPERATOR_SHOW_SERVICE_URL = 'CanChangeOperatorShowServiceUrl';

  public function init()
  {
    parent::init();

    if (Yii::$app instanceof ConsoleApplication) {
      $this->controllerNamespace = 'mcms\promo\commands';
    }

    \yii\base\Event::on(PartnerProgramItem::class, ActiveRecord::EVENT_AFTER_DELETE, [self::class, 'onPartnerProgramItemSync']);
    \yii\base\Event::on(PartnerProgramItem::class, ActiveRecord::EVENT_AFTER_INSERT, [self::class, 'onPartnerProgramItemSync']);
    \yii\base\Event::on(PartnerProgramItem::class, ActiveRecord::EVENT_AFTER_UPDATE, [self::class, 'onPartnerProgramItemSync']);
    \yii\base\Event::on(UserPromoSetting::class, ActiveRecord::EVENT_AFTER_INSERT, [self::class, 'onPartnerProgramSync']);
    \yii\base\Event::on(UserPromoSetting::class, ActiveRecord::EVENT_AFTER_UPDATE, [self::class, 'onPartnerProgramSync']);

    Yii::$container->set(ApiDriverInterface::class, [
      'class' => HttpApiDriver::class,
    ]);
  }

  public static function onPartnerProgramItemSync(\yii\base\Event $event)
  {
    $userIds = $event->sender->getPartnerProgram()->one()->getAutoSyncUserIds();
    foreach ($userIds as $userId) {
      PartnerProgramSync::runAsync($userId);
    }
  }

  public static function onPartnerProgramSync(\yii\base\Event $event)
  {
    if ($event->sender->scenario == UserPromoSetting::SCENARIO_ADD_PARTNER_PROGRAM) {
      PartnerProgramSync::runAsync($event->sender->user_id);
    }
  }

  public function getResellerCurrencies()
  {
    return [
      [
        'code' => self::MAIN_CURRENCY_RUB,
        'symbol' => Currency::findOne(['code' => self::MAIN_CURRENCY_RUB])->symbol
      ],
    ];
  }


  /**
   * TODO: Когда доберемся до рефакторинга валют, перенести в пейментс
   * Доступен ли партнеру рубль
   * @return bool
   */
  public function isRubAvailable()
  {
    return $this->settings->getValueByKey(self::SETTING_AVAILABLE_RUB);
  }

  /**
   * TODO: Когда доберемся до рефакторинга валют, перенести в пейментс
   * Доступен ли партнеру доллар
   * @return bool
   */
  public function isUsdAvailable()
  {
    return $this->settings->getValueByKey(self::SETTING_AVAILABLE_USD);
  }

  /**
   * TODO: Когда доберемся до рефакторинга валют, перенести в пейментс
   * Доступен ли партнеру евро
   * @return bool
   */
  public function isEurAvailable()
  {
    return $this->settings->getValueByKey(self::SETTING_AVAILABLE_EUR);
  }

  /**
   * Токен авторизации для главного инстанса для получения списка доступных провайдеров
   * @return string
   */
  public function getProvidersApiToken()
  {
    return $this->settings->getValueByKey(self::SETTINGS_PROVIDERS_API_TOKEN);
  }

  /**
   * ID юзера в КП
   * @return string
   */
  public function getKpUserId()
  {
    return $this->settings->getValueByKey(self::SETTINGS_KP_USER_ID);
  }

  /**
   * Секретный ключ для КП
   * @return string
   */
  public function getKpSecretKey()
  {
    return $this->settings->getValueByKey(self::SETTINGS_KP_SECRET_KEY);
  }

  /**
   * Урл КП приемщика
   * @return string
   */
  public function getPostbackUrl()
  {
    return $this->settings->getValueByKey(self::SETTINGS_POSTBACK_URL);
  }

  /**
   * Урл для приема ТБ
   * @return string
   */
  public function getTrafficbackUrl()
  {
    return $this->settings->getValueByKey(self::SETTINGS_TRAFFICBACK_URL);
  }

  /**
   * Урл примщика жалоб КП
   * @return string
   */
  public function getComplainsUrl()
  {
    return $this->settings->getValueByKey(self::SETTINGS_COMPLAINS_URL);
  }

  /**
   * TODO: Когда доберемся до рефакторинга валют, перенести в пейментс
   * Доступна ли партнеру валюта
   * @param string $currency код валюты
   * @return bool
   */
  public function isCurrencyAvailable($currency)
  {
    switch ($currency) {
      case self::MAIN_CURRENCY_RUB:
        return $this->isRubAvailable();
      case self::MAIN_CURRENCY_USD:
        return $this->isUsdAvailable();
      case self::MAIN_CURRENCY_EUR:
        return $this->isEurAvailable();
    }
    return false;
  }

  /**
   * Среднее количество ребиллов на подписку
   * @return integer
   */
  public function getAvrNumberRebillsPerSub()
  {
    return $this->settings->getValueByKey(self::SETTINGS_AVR_NUMBER_REBILL_PER_SUBSCRIPTION);
  }

  public function landingRatingForWebmasterSource()
  {
    return $this->settings->getValueByKey(self::SETTINGS_LANDING_RATING_FOR_WEBMASTER_SOURCE);
  }

  /**
   * @return mixed|null
   */
  public function getSettingsDomainIp()
  {
    return $this->settings->getValueByKey(self::SETTINGS_DOMAIN_IP);
  }

  public function isArbitraryLinkModerationActive()
  {
    return $this->settings->getValueByKey(self::SETTINGS_ARBITRARY_LINK_MODERATION);
  }

  /**
   * @return bool
   */
  public function getSettingsResellerCanHidePromo()
  {
    return $this->settings->getValueByKey(self::SETTINGS_RESELLER_HIDE_PROMO);
  }

  public function getDefaultTbUrl()
  {
    return $this->settings->getValueByKey(self::SETTINGS_DEFAULT_TB_URL);
  }

  public function isResellerCanHidePromo($resellerId)
  {
    return Yii::$app->getModule('users')->api('rolesByUserId', ['userId' => $resellerId])->isReseller()
      && $this->getSettingsResellerCanHidePromo()
      ;
  }

  public function settingsAddFakeSubscriptionsAfter($partnerId = null)
  {
    if (
      $this->isIndividualFakeSettingsEnabled() &&
      $partnerId &&
      ($setting = UserPromoSetting::findOne(['user_id' => $partnerId])) &&
      !is_null($setting->add_fake_after_subscriptions)
    ) {
      return $setting->add_fake_after_subscriptions;
    }
    return $this->settings->getValueByKey(self::SETTINGS_FAKE_ADD_AFTER_SUBSCRIPTIONS);
  }

  public function settingsFakeSubscriptionPercent($partnerId = null)
  {
    if (
      $this->isIndividualFakeSettingsEnabled() &&
      $partnerId &&
      ($setting = UserPromoSetting::findOne(['user_id' => $partnerId])) &&
      !is_null($setting->add_fake_subscription_percent)
    ) {
      return $setting->add_fake_subscription_percent;
    }
    return $this->settings->getValueByKey(self::SETTINGS_FAKE_ADD_SUBSCRIPTION_PERCENT);
  }

  /**
   * @return mixed|null
   */
  public function settingsFakeCPASubscriptionPercent($partnerId = null)
  {
    if (
      $this->isIndividualFakeSettingsEnabled() &&
      $partnerId &&
      ($setting = UserPromoSetting::findOne(['user_id' => $partnerId])) &&
      !is_null($setting->add_fake_cpa_subscription_percent)
    ) {
      return $setting->add_fake_cpa_subscription_percent;
    }
    return $this->settings->getValueByKey(self::SETTINGS_FAKE_ADD_CPA_SUBSCRIPTION_PERCENT);
  }

  public function settingsFakeOffSubscriptionDays()
  {
    return $this->settings->getValueByKey(self::SETTINGS_FAKE_OFF_SUBSCRIPTION_DAYS);
  }

  public function settingsFakeOffSubscriptionsPercentBeforeDays()
  {
    return $this->settings->getValueByKey(self::SETTINGS_FAKE_OFF_SUBSCRIPTION_PERCENT_BEFORE_DAYS);
  }

  /**
   * @return bool
   */
  public static function canEditMainSettings()
  {
    return Yii::$app->user->can(self::PERMISSION_CAN_EDIT_MAIN_SETTINGS);
  }

  /**
   * Есть ли право менять свойство оператора "Отображать URL сервиса"
   * @return bool
   */
  public static function canChangeOperatorShowServiceUrl()
  {
    return Yii::$app->user->can(self::PERMISSION_CAN_CHANGE_OPERATOR_SHOW_SERVICE_URL);
  }

  /**
   * @return bool
   */
  public static function canEditFakeRevshareSettings()
  {
    return Yii::$app->user->can(self::PERMISSION_CAN_EDIT_FAKE_REVSHARE_SETTINGS);
  }

  /**
   * @return bool
   */
  public static function canEditIndividualFakeSettings()
  {
    return Yii::$app->user->can(self::PERMISSION_CAN_EDIT_INDIVIDUAL_FAKE_SETTINGS);
  }

  /**
   * @return bool
   */
  public static function canEditLandingsSettings()
  {
    return Yii::$app->user->can(self::PERMISSION_CAN_EDIT_LANDINGS_SETTINGS);
  }

  /**
   * @return bool
   */
  public static function canEditUserFakeFlag()
  {
    return Yii::$app->user->can(self::PERMISSION_CAN_EDIT_USER_FAKE_REVSHARE_FLAG);
  }

  /**
   * @return bool
   */
  public function settingsIsFakeGloballyEnabled()
  {
    return (bool)$this->settings->getValueByKey(self::SETTINGS_GLOBAL_ENABLE_FAKE_TO_USERS);
  }

  /**
   * @return int
   */
  public function settingsFakeOffSubscriptionMaxRejection()
  {
    return (int)$this->settings->getValueByKey(self::SETTINGS_FAKE_OFF_SUBSCRIPTION_MAX_REJECTION);
  }

  /**
   * @return bool
   */
  public function isIndividualFakeSettingsEnabled()
  {
    return (bool)$this->settings->getValueByKey(self::SETTINGS_INDIVIDUAL_FAKE_SETTINGS_ENABLE);
  }

  public function isCheckDomainDisabled()
  {
    return $this->settings->getValueByKey(self::SETTINGS_DISABLE_CHECK_DOMAIN);
  }

  public function getDefaultClickNConfirmText()
  {
    return [
      'en' => $this->settings->getValueByKey(self::SETTINGS_CLICK_N_CONFIRM_TEXT_EN),
      'ru' => $this->settings->getValueByKey(self::SETTINGS_CLICK_N_CONFIRM_TEXT_RU)
    ];
  }

  /**
   * @return bool
   */
  public function isGlobalAllowForceOperator()
  {
    return (bool)$this->settings->getValueByKey(self::SETTINGS_GLOBAL_ALLOW_FORCE_OPERATOR);
  }

  /**
   * @return bool
   */
  public function getIsLandingsAutoRotationGlobalEnabled()
  {
    return $this->settings->getValueByKey(self::SETTINGS_IS_LANDINGS_AUTO_ROTATION_GLOBAL_ENABLED);
  }

  /**
   * @return int
   */
  public function getMinCountHitsOnLanding()
  {
    return (int)$this->settings->getValueByKey(self::SETTINGS_MIN_COUNT_HITS_ON_LANDING);
  }

  /**
   * @return string
   */
  public function getUserAuthEmail()
  {
    return $this->settings->getValueByKey(self::SETTINGS_KP_API_USER_AUTH_EMAIL);
  }

  /**
   * @return string
   */
  public function getUserAuthHash()
  {
    return $this->settings->getValueByKey(self::SETTINGS_KP_API_USER_AUTH_HASH);
  }
}