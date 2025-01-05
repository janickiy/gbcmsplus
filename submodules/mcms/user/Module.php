<?php

namespace mcms\user;


use mcms\common\helpers\ArrayHelper;
use Yii;
use yii\console\Application as ConsoleApplication;
use yii\filters\AccessControl;
use yii\helpers\BaseInflector;

/**
 * Class Module
 * @package mcms\user
 */
class Module extends \mcms\common\module\Module
{
  public $controllerNamespace = 'mcms\user\controllers';
  public $name;
  public $menu;

  private static $isEnglishOnly;

  const SETTINGS_EXPORT_LIMIT = 'users.export_limit';
  const SETTINGS_CAPTCHA_SHOW_AFTER = 'settings.captcha_show_after';

  const SETTINGS_REGISTRATION_TYPE = 'registration.type';
  const SETTINGS_REGISTRATION_TYPE_HAND = 'registration.type.hand';
  const SETTINGS_REGISTRATION_TYPE_EMAIL_CONFIRM = 'registration.type.email_confirm';
  const SETTINGS_REGISTRATION_TYPE_WITHOUT_CONFIRM = 'registration.type.without_confirm';
  const SETTINGS_REGISTRATION_TYPE_CLOSED = 'registration.type.closed';
  const SETTINGS_REGISTRATION_WITH_REFS = 'registration.with.refs';
  const SETTINGS_REGISTRATION_BY_INVITATIONS = 'registration.by_invitations';

  const SETTINGS_RESTORE_PASSWORD = 'restore_password';
  const SETTINGS_RESTORE_PASSWORD_LINK = 'restore_password.link';
  const SETTINGS_RESTORE_PASSWORD_SEND_NEW_PASSWORD = 'restore_password.send_new_password';
  const SETTINGS_RESTORE_PASSWORD_SUPPORT = 'restore_password.support';

  const SETTINGS_USER_LANGUAGE = 'user_default_language';
  const SETTINGS_REGISTRATION_WITH_LANGUAGE = 'registration.with.lannguage';
  const SETTINGS_USER_CURRENCY = 'user_default_currency';
  const SETTINGS_REGISTRATION_WITH_CURRENCY = 'registration.with.currency';

  const SETTINGS_MANAGER_ROLES = 'manager_roles';

  /** @const string Настройка Параметры языков */
  const SETTINGS_LANGUAGE_OPTIONS = 'settings.language_options';
  /** @const string Значение настройки Параметры языков rus_eng */
  const SETTINGS_LANGUAGE_OPTION_RUS_ENG = 'rus_eng';
  /** @const string Значение настройки Параметры языков eng_new */
  const SETTINGS_LANGUAGE_OPTION_ENG_NEW = 'eng_new';
  /** @const string Значение настройки Параметры языков eng */
  const SETTINGS_LANGUAGE_OPTION_ENG = 'eng';
  /** @var string Если включено, включается капча при регистрации */
  const SETTINGS_ENABLE_CAPTCHA_REGISTRATION = 'registration.enable_captcha';

  const EVENT_USERS_AUTH_REGISTERED = 'event.users.auth.registered';
  const EVENT_USERS_STATUS_CHANGED = 'event.users.status.changed';
  const EVENT_USERS_ACTIVATION_CODE_SENDED = 'event.users.activation.code.sended';
  const EVENT_USERS_PASSWORD_SENDED = 'event.users.password.sended';
  const EVENT_USERS_PASSWORD_GENERATE_LINK_SENDED = 'event.users.password.generate.link.sended';
  const EVENT_USERS_PASSWORD_CHANGED = 'event.users.password.changed';
  const EVENT_USERS_AUTH_LOGGED_IN = 'event.users.auth.logged.in';
  const EVENT_USERS_AUTH_LOGGED_OUT = 'event.users.auth.logged.out';
  const EVENT_USERS_CREATED = 'event.users.created';
  const EVENT_USERS_UPDATED = 'event.users.updated';

  const OWNER_ROLE = 'owner';

  const GUEST_ROLE = 'guest';
  const ROOT_ROLE = 'root';
  const ADMIN_ROLE = 'admin';
  const MANAGER_ROLE = 'manager';
  const RESELLER_ROLE = 'reseller';
  const PARTNER_ROLE = 'partner';

  const PERMISSION_CAN_VIEW_EDIT_FORM = 'UsersCanViewEditForm';

  const PERMISSION_CAN_VIEW_ADMIN_CABINET = 'UsersUsersViewAdminCabinet';
  const PERMISSION_CAN_VIEW_PARTNER_CABINET = 'UsersUsersViewPartnerCabinet';

  const PERMISSION_CAN_HAVE_REFERRER = 'UsersHaveReferrer';
  const PERMISSION_CAN_HAVE_REFERRAL = 'UsersHaveReferral';

  const PERMISSION_CAN_MANAGE_ALL_USERS = 'UserCanManageAllUsers';
  const PERMISSION_CAN_CHANGE_MANAGER_ALL_USERS = 'UserCanChangeManagerAllUsers';
  const PERMISSION_CAN_CHANGE_MANAGER_TO_OWNSELF_USERS_WITHOUT_MANAGER = 'UserCanChangeManagerToOwnselfUsersWithoutManager';
  const PERMISSION_CAN_CHANGE_EMAIL = 'UserCanChangeEmail';
  const PERMISSION_CAN_EXPORT_USERS = 'UsersUsersExport';

  public function init()
  {
    parent::init();

    Yii::$app->getUrlManager()->addRules([
      'refid/<refId:\w+>' => 'users/site/refid',
    ]);

    if (Yii::$app instanceof ConsoleApplication) {
      $this->controllerNamespace = 'mcms\user\commands';
    } else {
      $this->modules = [
        'admin' => [
          'class' => 'mcms\user\admin\Module',
          'viewPath' => '@mdm/admin/views',
          'layout' => 'left-menu',
          'mainLayout' => '@admin/views/layouts/main.php',
          'as access' => [
            'class' => AccessControl::class,
            'rules' => [
              [
                'allow' => true,
                'roles' => ['UsersViewYiiAdmin']
              ]
            ]
          ],
          'menus' => [
            'tree' => [
              'url' => ['/users/admin/tree']
            ],
          ],
        ],
      ];
    }

    if ($this->isInvitationsEnabled()) {
      Yii::$app->getUrlManager()->addRules([
        [
          'pattern' => 'i/<hash:[\d\w]+>',
          'route' => 'users/api/invite',
          'suffix' => '',
        ]
      ]);
    }
  }

  /**
   * @return mixed|null
   */
  public function captchaShowAfterFailLogin()
  {
    return $this->settings->getValueByKey(self::SETTINGS_CAPTCHA_SHOW_AFTER, 0);
  }

  /**
   * @return int|null
   */
  public function getExportLimit()
  {
    return $this->settings->getValueByKey(self::SETTINGS_EXPORT_LIMIT);
  }

  /**
   * @return mixed|null
   */
  private function getRegistrationType()
  {
    return $this->settings->getValueByKey(self::SETTINGS_REGISTRATION_TYPE);
  }

  /**
   * @return bool
   */
  public function isRegistrationTypeByHand()
  {
    return $this->getRegistrationType() == self::SETTINGS_REGISTRATION_TYPE_HAND;
  }

  /**
   * @return bool
   */
  public function isRegistrationTypeEmailConfirm()
  {
    return $this->getRegistrationType() == self::SETTINGS_REGISTRATION_TYPE_EMAIL_CONFIRM;
  }

  /**
   * @return bool
   */
  public function isRegistrationWithoutConfirm()
  {
    return $this->getRegistrationType() == self::SETTINGS_REGISTRATION_TYPE_WITHOUT_CONFIRM;
  }

  /**
   * @return bool
   */
  public function isRegistrationTypeClosed()
  {
    return $this->getRegistrationType() == self::SETTINGS_REGISTRATION_TYPE_CLOSED;
  }

  /**
   * @return mixed|null
   */
  private function getRestorePassword()
  {
    return $this->settings->getValueByKey(self::SETTINGS_RESTORE_PASSWORD);
  }

  /**
   * Получить список ролей соответствующих менеджерам.
   * @return array
   */
  public function getManagerRoles()
  {
    /** @var Module $userModule */
    $userModule = Yii::$app->getModule('users');

    return array_map('trim', explode(',', $userModule->settings->getValueByKey(static::SETTINGS_MANAGER_ROLES, self::MANAGER_ROLE)));
  }

  /**
   * @return bool
   */
  public function isRestorePasswordByLink()
  {
    return $this->getRestorePassword() == self::SETTINGS_RESTORE_PASSWORD_LINK;
  }

  /**
   * @return bool
   */
  public function isRestorePasswordSendNewPassword()
  {
    return $this->getRestorePassword() == self::SETTINGS_RESTORE_PASSWORD_SEND_NEW_PASSWORD;
  }

  /**
   * @return bool
   */
  public function isRestorePasswordSupport()
  {
    return $this->getRestorePassword() == self::SETTINGS_RESTORE_PASSWORD_SUPPORT;
  }

  /**
   * @return bool
   */
  public function isRegistrationWithReferrals()
  {
    return $this->settings->getValueByKey(self::SETTINGS_REGISTRATION_WITH_REFS, false);
  }

  /**
   * @return bool
   */
  public function isInvitationsEnabled()
  {
    return true;
    return !$this->settings->getValueByKey(self::SETTINGS_REGISTRATION_BY_INVITATIONS, false);
  }

  /**
   * Язык партнера по умолчанию
   * @return mixed|null
   */
  public function languageUser()
  {
    // TRICKY: Если партнеру разрешено менять язык, то язык по умолчанию из настроек, иначе хардкодим английский
    return $this->canPartnerChangeLanguage()
      ? $this->settings->getValueByKey(self::SETTINGS_USER_LANGUAGE)
      : 'en';
  }

  /**
   * Значение настройки Параметры языков
   * self::SETTINGS_LANGUAGE_OPTION_RUS_ENG = rus_eng - и русский и английский языки включены
   * self::SETTINGS_LANGUAGE_OPTION_ENG_NEW = eng_new - новые пользователи регистрируются только с англ языком, в параметрах нельзя выбрать язык
   * self::SETTINGS_LANGUAGE_OPTION_ENG = eng - все пользователи принудительно переводятся на англ язык, в параметрах нельзя выбрать язык
   * @return string
   */
  public function getLanguageOption()
  {
    return $this->settings->getValueByKey(self::SETTINGS_LANGUAGE_OPTIONS);
  }

  /**
   * Может ли партнер менять язык (согласно настройке Параметры языков)
   * @return bool
   */
  public function canPartnerChangeLanguage()
  {
    return $this->getLanguageOption() === self::SETTINGS_LANGUAGE_OPTION_RUS_ENG;
  }
  /**
   * Доступен только английский? (согласно настройке Параметры языков)
   * @return bool
   */
  public function isEnglishOnly()
  {
    if (self::$isEnglishOnly !== null) return self::$isEnglishOnly;
    self::$isEnglishOnly = $this->getLanguageOption() === self::SETTINGS_LANGUAGE_OPTION_ENG;
    return self::$isEnglishOnly;
  }

  /**
   * @return mixed|null
   */
  public function registrationWithLanguage()
  {
    return $this->settings->getValueByKey(self::SETTINGS_REGISTRATION_WITH_LANGUAGE) &&
      $this->canPartnerChangeLanguage();
  }

  /**
   * @return mixed|null
   */
  public function currencyUser()
  {
    return $this->settings->getValueByKey(self::SETTINGS_USER_CURRENCY);
  }

  /**
   * @return mixed|null
   */
  public function registrationWithCurrency()
  {
    return $this->settings->getValueByKey(self::SETTINGS_REGISTRATION_WITH_CURRENCY);
  }

  /**
   * @return bool
   */
  public function isCaptchaEnabledRegistration()
  {
    return (bool) $this->settings->getValueByKey(self::SETTINGS_ENABLE_CAPTCHA_REGISTRATION);
  }

  /**
   * Редактирование своего Email
   * @return bool
   */
  public function canChangeEmail()
  {
    return Yii::$app->user->can(self::PERMISSION_CAN_CHANGE_EMAIL);
  }

  /**
   * Экспортировать пользователей
   * @return bool
   */
  public function canExportUsers()
  {
    return Yii::$app->user->can(self::PERMISSION_CAN_EXPORT_USERS);
  }

  /**
   * @return mixed|string
   */
  public function getUrlCabinet()
  {
    $hostInfo = Yii::$app->request->hostInfo;

    if (Yii::$app->user->isGuest) {
      return $hostInfo . '/users/site/login/';
    }

    if (
      Yii::$app->user->can(self::PERMISSION_CAN_VIEW_ADMIN_CABINET) &&
      Yii::$app->user->can('AppBackendDefaultIndex')
    ){
      return $hostInfo . '/admin/';
    }

    if (Yii::$app->user->can(self::PERMISSION_CAN_VIEW_PARTNER_CABINET)){
      return $hostInfo . '/partners/statistic/index/';
    }

    return $hostInfo . '/admin/users/site/logout/';
  }
}
