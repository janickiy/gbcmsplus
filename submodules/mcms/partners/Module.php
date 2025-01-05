<?php
namespace mcms\partners;

use mcms\partners\components\PartnerFormatter;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\console\Application as ConsoleApplication;
use yii\web\Application;

class Module extends \mcms\common\module\Module
{

  public $controllerNamespace = 'mcms\partners\controllers';
  public $name;
  public $menu;

  public static $arbitraryUrl = ['links/index', 'choose' => 1];
  public static $webmasterUrl = ['sources/index', 'choose' => 1];

  const SETTINGS_TEMPLATE = 'settings.template';

  const SETTINGS_FOOTER_MAIN_QUESTION_SKYPE = 'settings.footer.main_questions_skype';
  const SETTINGS_FOOTER_MAIN_QUESTION_EMAIL = 'settings.footer.main_questions_email';
  const SETTINGS_FOOTER_MAIN_QUESTION_ICQ = 'settings.footer.main_questions_icq';
  const SETTINGS_FOOTER_MAIN_QUESTION_TELEGRAM = 'settings.footer.main_questions_telegram';
  const SETTINGS_FOOTER_TECH_SUPPORT_SKYPE = 'settings.footer.tech_support_skype';
  const SETTINGS_FOOTER_TECH_SUPPORT_EMAIL = 'settings.footer.tech_support_email';
  const SETTINGS_FOOTER_TECH_SUPPORT_ICQ = 'settings.footer.tech_support_icq';
  const SETTINGS_FOOTER_TECH_SUPPORT_TELEGRAM = 'settings.footer.tech_support_telegram';
  const SETTINGS_FOOTER_COPYRIGHT = 'settings.footer.copyright';
  const SETTINGS_TITLE_TEMPLATE = 'settings.title_template';
  const SETTINGS_LOGO_IMAGE = 'settings.logo_image';
  const SETTINGS_ADMIN_PANEL_LOGO_IMAGE = 'settings.admin_panel_logo_image';
  const SETTINGS_LOGO_PUBLICATION = 'settings.logo_public';
  const SETTINGS_FAVICON = 'settings.favicon';
  const SETTINGS_DEFAULT_STREAM = 'settings.default_stream';
  const SETTINGS_MAX_NUMBER_OF_MONTH = 'settings.max_number_of_month';
  const SETTINGS_META_DESCRIPTION = 'settings.meta_description';
  const SETTINGS_META_KEYWORDS = 'settings.meta_keywords';
  const SETTINGS_LOGO_EMAIL_IMAGE = 'settings.logo_email_image';
  const SETTINGS_COLOR_THEME = 'settings.color_theme';
  const SETTINGS_SERVER_NAME = 'settings.server_name';
  const SETTINGS_EMAIL_SERVER_NAME = 'settings.email_server_name';
  const SETTINGS_PROJECT_NAME = 'settings.project_name';
  const SETTINGS_EMAIL_TEMPLATE = 'settings.email_template';
  const SETTINGS_PROMO_URL_HTML = 'settings.promo_url_html';
  const SETTINGS_PROMO_URL_ENABLED = 'settings.promo_url_html_enabled';
  const SETTINGS_PROMO_MODAL_ENABLED = 'settings.promo_modal_enabled';
  const SETTINGS_PROMO_MODAL_HEADER_EN = 'settings.promo_modal_header_en';
  const SETTINGS_PROMO_MODAL_HEADER_RU = 'settings.promo_modal_header_ru';
  const SETTINGS_PROMO_MODAL_BODY_EN = 'settings.promo_modal_body_en';
  const SETTINGS_PROMO_MODAL_BODY_RU = 'settings.promo_modal_body_ru';
  const SETTINGS_PROMO_MODAL_SHOW_ONETIME = 'settings_promo_modal_show_onetime';
  const SETTINGS_DISABLE_COLOR_THEME_CHOOSE = 'settings.disable_color_theme_choose';
  const SETTINGS_AUTO_SUBMIT = 'partners.auto_submit';
  const SETTINGS_SHOW_RATIO = 'settings.partners.show_ratio';
  const SETTINGS_SHOW_PERSONAL_MANAGER = 'settings.show_personal_manager';
  const SETTINGS_ENABLE_ALIVE_SUBSCRIPTIONS = 'enable_alive_subscriptions';
  const SETTINGS_MERGE_LANDINGS = 'settings.partners.is_merge_landings';

  const PERMISSION_RESELLER_SETTINGS = 'CanResellerEditSettingsPartners';
  const PERMISSION_EDIT_MODULE_SETTINGS_PROMO_URL = 'EditModuleSettingsPromoUrl';
  const URL_NOTIFICATION_SETTINGS = '/partners/profile/notifications/';
  const PARTNER_ID_COOKIE_KEY = 'pid';

  public function init()
  {
    parent::init();

    if (Yii::$app instanceof ConsoleApplication) {
      $this->controllerNamespace = 'mcms\partners\commands';
    }

    if (!YII_ENV_TEST && Yii::$app instanceof Application) {
      $pid = ArrayHelper::getValue($_COOKIE, self::PARTNER_ID_COOKIE_KEY);
      if ($pid === null || base64_decode($pid) != Yii::$app->user->id) {
        setcookie(
          self::PARTNER_ID_COOKIE_KEY,
          base64_encode(Yii::$app->user->id),
          time() + 86400 * 360,
          '/',
          '.' . Yii::$app->request->serverName
        );
      }
    }
  }

  /**
   * @return mixed|null
   */
  public function getFooterMainQuestionSkype()
  {
    return $this->settings->getValueByKey(self::SETTINGS_FOOTER_MAIN_QUESTION_SKYPE);
  }
  /**
   * @return mixed|null
   */
  public function getFooterMainQuestionEmail()
  {
    return $this->settings->getValueByKey(self::SETTINGS_FOOTER_MAIN_QUESTION_EMAIL);
  }
  /**
   * @return mixed|null
   */
  public function getFooterMainQuestionIcq()
  {
    return $this->settings->getValueByKey(self::SETTINGS_FOOTER_MAIN_QUESTION_ICQ);
  }

  /**
   * @return mixed|null
   */
  public function getFooterMainQuestionTelegram()
  {
    return $this->settings->getValueByKey(self::SETTINGS_FOOTER_MAIN_QUESTION_TELEGRAM);
  }

  /**
   * @return mixed|null
   */
  public function getFooterTechSupportSkype()
  {
    return $this->settings->getValueByKey(self::SETTINGS_FOOTER_TECH_SUPPORT_SKYPE);
  }
  /**
   * @return mixed|null
   */
  public function getFooterTechSupportEmail()
  {
    return $this->settings->getValueByKey(self::SETTINGS_FOOTER_TECH_SUPPORT_EMAIL);
  }
  /**
   * @return mixed|null
   */
  public function getFooterTechSupportIcq()
  {
    return $this->settings->getValueByKey(self::SETTINGS_FOOTER_TECH_SUPPORT_ICQ);
  }

  /**
   * @return mixed|null
   */
  public function getFooterTechSupportTelegram()
  {
    return $this->settings->getValueByKey(self::SETTINGS_FOOTER_TECH_SUPPORT_TELEGRAM);
  }

  /**
   * @return mixed|null
   */
  public function getFooterCopyright()
  {
    return $this->settings->getValueByKey(self::SETTINGS_FOOTER_COPYRIGHT);
  }

  /**
   * @return string|null
   */
  public function getDefaultStream()
  {
    return $this->settings->getValueByKey(self::SETTINGS_DEFAULT_STREAM);
  }

  /**
   * @return string|null
   */
  public function getMaxNumberOfMonth()
  {
    return $this->settings->getValueByKey(self::SETTINGS_MAX_NUMBER_OF_MONTH);
  }

  /**
   * @return boolean
   */
  public function isShownPersonalManager()
  {
    return $this->settings->getValueByKey(self::SETTINGS_SHOW_PERSONAL_MANAGER);
  }

  /**
   * @return string|null
   */
  public function getServerName()
  {
    return $this->settings->getValueByKey(self::SETTINGS_SERVER_NAME);
  }

  /**
   * @return string|null
   */
  public function getServerNameForEmail()
  {
    return $this->settings->getValueByKey(self::SETTINGS_EMAIL_SERVER_NAME);
  }

  /**
   * @return string|null
   */
  public function getFilledServerNameForEmail()
  {
    return $this->getServerNameForEmail() ?: $this->getServerName();
  }

  public function getProjectName()
  {
    return $this->settings->getValueByKey(self::SETTINGS_PROJECT_NAME);
  }

  public function getColorTheme()
  {
    return $this->settings->getValueByKey(self::SETTINGS_COLOR_THEME);
  }

  /**
   * @return mixed|null
   */
  public function getFooterContactValues()
  {
    $contactValues = ['copyright' => $this->getFooterCopyright()];
    ($mqSkype = $this->getFooterMainQuestionSkype()) && $contactValues['mainQuestions']['skype'] = $mqSkype;
    ($mqEmail = $this->getFooterMainQuestionEmail()) && $contactValues['mainQuestions']['email'] = $mqEmail;
    ($mqIcq = $this->getFooterMainQuestionIcq()) && $contactValues['mainQuestions']['icq'] = $mqIcq;
    ($mqTelegram = $this->getFooterMainQuestionTelegram()) && $contactValues['mainQuestions']['telegram'] = $mqTelegram;
    ($tsSkype = $this->getFooterTechSupportSkype()) && $contactValues['techSupport']['skype'] = $tsSkype;
    ($tsEmail = $this->getFooterTechSupportEmail()) && $contactValues['techSupport']['email'] = $tsEmail;
    ($tsIcq = $this->getFooterTechSupportIcq()) && $contactValues['techSupport']['icq'] = $tsIcq;
    ($tsTelegram = $this->getFooterTechSupportTelegram()) && $contactValues['techSupport']['telegram'] = $tsTelegram;


    return $contactValues;
  }

  /**
   * @return string|null
   */
  public function getNotificationSettingsUrl()
  {
    return Url::to(self::URL_NOTIFICATION_SETTINGS, true);
  }

  /**
   * @return bool
   */
  public function isPromoUrlEnabled()
  {
    return $this->settings->getValueByKey(self::SETTINGS_PROMO_URL_ENABLED);
  }

  /**
   * @return string
   */
  public function getPromoUrlHtml()
  {
    return $this->settings->getValueByKey(self::SETTINGS_PROMO_URL_HTML);
  }

  /**
   * @return bool
   */
  public function isPromoModalEnabled()
  {
    return !!$this->settings->getValueByKey(self::SETTINGS_PROMO_MODAL_ENABLED);
  }

  /**
   * Включены живые подписки
   * @return bool
   */
  public function isAliveSubscriptionsEnabled()
  {
    return $this->settings->getValueByKey(self::SETTINGS_ENABLE_ALIVE_SUBSCRIPTIONS, true);
  }

  /**
   * @return mixed|null
   */
  public function getPromoModalHeader()
  {
    return $this->settings->getValueByKey(
      Yii::$app->language === 'en'
        ? self::SETTINGS_PROMO_MODAL_HEADER_EN
        : self::SETTINGS_PROMO_MODAL_HEADER_RU
    );
  }

  /**
   * @return mixed|null
   */
  public function getPromoModalBody()
  {
    return $this->settings->getValueByKey(
      Yii::$app->language === 'en'
        ? self::SETTINGS_PROMO_MODAL_BODY_EN
        : self::SETTINGS_PROMO_MODAL_BODY_RU
    );
  }

  public function showPromoModal()
  {
    return $this->isPromoModalEnabled() &&  Yii::$app->user->getIdentity()->getParams()->show_promo_modal;
  }

  /**
   * @return bool
   */
  public function isThemeEnabled()
  {
    return !$this->settings->getValueByKey(self::SETTINGS_DISABLE_COLOR_THEME_CHOOSE);
  }

  /**
   * Автосабмит форм статистики
   * @return mixed|null
   */
  public function isAutoSubmitEnabled()
  {
    return $this->settings->getValueByKey(self::SETTINGS_AUTO_SUBMIT, true);
  }

  /**
   * Показывать ратио в статистике
   * @return bool
   */
  public function isShowRatio()
  {
    return (bool)$this->settings->getValueByKey(self::SETTINGS_SHOW_RATIO, false);
  }

  /**
   * "Схлопывать" ли ленды в ПП
   * @return bool
   */
  public function isMergeLandings()
  {
    return (bool) $this->settings->getValueByKey(self::SETTINGS_MERGE_LANDINGS, true);
  }
}
