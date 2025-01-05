<?php

namespace mcms\user\models;

use mcms\common\rbac\AuthItemsManager;
use mcms\promo\components\api\UserPromoSettings;
use mcms\user\components\events\EventStatusChanged;
use mcms\user\components\events\EventUserApprovedWithoutReferrals;
use mcms\user\components\events\EventUserCreated;
use mcms\user\components\events\EventUserUpdated;
use mcms\user\components\events\EventUserBlocked;
use mcms\user\components\events\EventUserApproved;
use Yii;
use yii\base\DynamicModel;
use mcms\user\Module;
use yii\helpers\ArrayHelper;
use mcms\common\validators\UrlValidator;

/**
 * Class UserForm
 * @property integer $is_allowed_source_redirect
 * @property integer $is_disable_buyout
 * @package mcms\user\models
 */
class UserForm extends DynamicModel
{
  /**
   * @var User
   */
  private $user;
  private $roles;
  public $partner_type;
  private $_newUser;
  public $moderationReason;
  public $referrer_id;
  public $is_label_stat_enabled;
  public $invoicing_cycle;
  public $telegram_id;

  /**
   * @var bool
   */
  public $is_allowed_source_redirect;

  /**
   * @var bool
   */
  public $is_disable_buyout;

  /** @var  bool */
  public $is_fake_revshare_enabled;

  /** @var UserPromoSettings */
  public $userPromoSettingsApi;

  static $blockedStatuses = [
    User::STATUS_BLOCKED,
    User::STATUS_DELETED,
    User::STATUS_INACTIVE
  ];

  public function rules()
  {
    $validationRules = [
      [['topname'], 'filter', 'filter' => 'trim'],
      ['email', 'filter', 'filter' => 'trim'],
      [['email','username'], 'required'],
      ['email', 'email'],
      ['email', 'string', 'max' => 255],
      ['language', 'string', 'max' => 2],
      ['email', 'unique', 'targetClass' => '\mcms\user\models\User', 'message' => Yii::_t('forms.user_email_not_unique'), 'when' => function() {
        return $this->user->getAttribute('email') != $this->email;
      }],

      ['status', 'required'],
      ['status', function($attribute, $params) {
        if (!in_array($this->$attribute, array_keys(User::$availableStatuses))) {
          $this->addError($attribute, Yii::_t('forms.user_wrong_status'));
        }
      }],

      ['moderationReason', 'string'],

      ['referrer_id', function($attribute) {
        if (!$this->user->canHaveReferrer()) {
          $this->addError($attribute, Yii::_t('forms.cant_have_referrer_error'));
          return;
        }
        if ($this->user->id == $this->$attribute) {
          $this->addError($attribute, Yii::_t('forms.referrer_cant_refer_yourself'));
          return;
        }
        /** @var User $referrer */
        $referrer = User::findOne(['id' => $this->$attribute]);
        if (!$referrer) {
          $this->addError($attribute, Yii::_t('forms.wrong_referrer_name_error'));
          return;
        }
        if ($this->user->hasUserReferral($referrer->id)) {
          $this->addError($attribute, Yii::_t('forms.referrer_link_each_other_error'));
          return;
        }
        if (!$referrer->canHaveReferral()) {
          $this->addError($attribute, Yii::_t('forms.referrer_cant_have_referral'));
        }
      }],

      ['comment', 'string'],
      [['is_label_stat_enabled', 'is_fake_revshare_enabled',
        'notify_browser_system', 'notify_browser_news',
        'notify_email', 'notify_email_system', 'notify_push_system', 'notify_email_news', 'notify_telegram_news',
        'notify_email_categories', 'notify_telegram_categories', 'notify_push_news', 'notify_browser_categories', 'is_allowed_source_redirect', 'notify_telegram_system', 'notify_push_categories', 'is_disable_buyout', 'invoicing_cycle'], 'safe'],
      ['notify_email', 'email'],
      [['notify_browser_system', 'notify_telegram_system', 'notify_push_system', 'notify_push_news', 'notify_browser_news', 'notify_email_system', 'notify_email_news', 'notify_telegram_news'], 'default', 'value' => 1],
      [['roles'], 'default', 'value' => [Module::PARTNER_ROLE => Module::PARTNER_ROLE], 'skipOnEmpty' => false],
      [['roles'], 'required', 'when' => function () {
        return $this->canUpdateUserRoles();
      }],
      ['roles', 'in', 'range' => self::getRolesList(), 'allowArray' => true, 'when' => function () {
        return $this->canUpdateUserRoles();
      }],
      ['roles', 'onlyRole'],
    ];

    $validationRules[] = ['password', 'default'];
    $validationRules[] = ['password', 'string', 'min' => 6];

    if (!Yii::$app->user->can(Module::PERMISSION_CAN_CHANGE_MANAGER_ALL_USERS)
      && !Yii::$app->user->can(Module::PERMISSION_CAN_MANAGE_ALL_USERS)
    ) {
      // Если менеджер может управлять только пользователями привязанными к себе,
      // то при изменении данных о пользователе нужно обязательно быть его менеджером
      $validationRules[] = ['manager_id', function () {
        if ($this->manager_id != Yii::$app->user->id) {
          $this->addError('manager_id', Yii::_t(
            $this->user->manager_id
              ? 'forms.unbind_user_contact_admin'
              : 'forms.not_manager_of_this_user'
          ));
          return false;
        }
        return true;
      }];
    }

    if (Yii::$app->user->identity->canChangeManager($this->user)) {
      $validationRules[] = [['manager_id'], function ($attribute) {
        if (!$this->$attribute) return true;

        $manager = User::findOne(['id' => $this->$attribute]);

        if (!$manager) {
          $this->addError($attribute, Yii::_t('forms.user_not_found'));
          return false;
        }

        /** @var Module $usersModule */
        $usersModule = Yii::$app->getModule('users');
        if (!in_array($manager->getRole()->one()->name, $usersModule->getManagerRoles())) {
          $this->addError($attribute, Yii::_t('forms.user_invalid_role'));
          return false;
        }
      }, 'skipOnEmpty' => true];
    }

    if ($this->canUserDelegateResellerHidePromo()) {
      $validationRules[] = ['reseller_can_hide_promo', 'boolean', 'skipOnEmpty' => false];
    }

    if ($this->canResellerHidePromo() && !$this->canUserDelegateResellerHidePromo()) {
      $validationRules[] = ['partner_hide_promo', 'boolean', 'skipOnEmpty' => false];
    }

    if ($this->isPartner()) {
      $validationRules[] = [['postback_url', 'complains_postback_url'], UrlValidator::class, 'enableIDN' => true];
      $validationRules[] = [['postback_url', 'complains_postback_url'], $this->userPromoSettingsApi->getGlobalPostbackValidator(), 'skipOnEmpty' => false, 'userId' => $this->getUser()->id];
    }

    $additionalFieldModel = $this->user->getAdditionalFieldsModel();

    $validationRules = array_merge($validationRules, $additionalFieldModel->getActiveValidators());

    return $validationRules;
  }

  public function afterValidate()
  {
    $this->username = $this->email;
    parent::afterValidate();
  }

  public function onlyRole($attribute, $params)
  {
    if (count($this->$attribute) > 1) {
      $this->addError($attribute, $params['message'] ?: Yii::_t('forms.user_single_role'));
    }
  }

  private function isNewUser()
  {
    return $this->_newUser;
  }

  public function __construct(User $user, $config = [])
  {
    $this->user = $user;
    $this->_newUser = $user->id === null;
    $this->user->scenario = $this->isNewUser() ? 'adminCreate' : 'adminEdit';
    if ($referrer = $this->user->referrer) {
      $this->referrer_id = $referrer->id;
    }

    $this->userPromoSettingsApi = Yii::$app->getModule('promo')->api('userPromoSettings');

    $userAttributes = $user->activeAttributes();

    foreach ($userAttributes as $fieldName) {
      $config[$fieldName] = $this->user->getAttribute($fieldName);
    }

    if ($this->canUserDelegateResellerHidePromo()) {
      $config['reseller_can_hide_promo'] = $this->canResellerHidePromo($this->getUser()->id);
    }

    if ($this->canResellerHidePromo() && !$this->canUserDelegateResellerHidePromo()) {
      $config['partner_hide_promo'] = $this->canPartnerViewPromo();
    }

    if ($this->isPartner()) {
      $config['postback_url'] = $this->userPromoSettingsApi->getGlobalPostbackUrl($this->getUser()->id);
      $config['complains_postback_url'] = $this->userPromoSettingsApi->getGlobalComplainsPostbackUrl($this->getUser()->id);
    }

    parent::__construct($config);
  }

  public function attributeLabels()
  {
    return [
      'email' => Yii::_t('forms.user_email'),
      'status' => Yii::_t('forms.user_status'),
      'password' => Yii::_t('forms.user_password'),
      'language' => Yii::_t('forms.user_language'),
      'phone' => Yii::_t('forms.user_phone'),
      'skype' => Yii::_t('forms.user_skype'),
      'show_promo_modal' => Yii::_t('forms.user_show_promo_modal'),
      'hide_promo' => Yii::_t('forms.user_hide_promo'),
      'topname' => Yii::_t('forms.user_topname'),
      'reseller_can_hide_promo' => Yii::_t('forms.reseller_can_hide_promo'),
      'partner_hide_promo' => Yii::_t('forms.user_hide_promo'),
      'moderationReason' => Yii::_t('forms.user_moderation_reason'),
      'referrer_id' => Yii::_t('forms.referrer_id'),
      'manager_id' => Yii::_t('forms.manager_id'),
      'comment' => Yii::_t('forms.comment'),
      'is_label_stat_enabled' => Yii::_t('forms.is_label_stat_enabled'),
      'invoicing_cycle' => Yii::_t('forms.invoicing_cycle'),
      'is_fake_revshare_enabled' => Yii::_t('forms.is_fake_revshare_enabled'),
      'roles' => Yii::_t('forms.user_roles'),
      'notify_browser_system' => Yii::_t('forms.notify_browser_system'),
      'is_allowed_source_redirect' => Yii::_t('forms.is_allowed_source_redirect'),
      'is_disable_buyout' => Yii::_t('forms.is_disable_buyout'),
      'notify_browser_news' => Yii::_t('forms.notify_browser_news'),
      'notify_email' => Yii::_t('forms.notify_email'),
      'notify_email_system' => Yii::_t('forms.notify_email_system'),
      'notify_email_news' => Yii::_t('forms.notify_email_news'),
      'notify_email_categories' => Yii::_t('forms.notify_email_categories'),
      'notify_browser_categories' => Yii::_t('forms.notify_browser_categories'),
      'notify_telegram_system' => Yii::_t('forms.notify_telegram_system'),
      'notify_telegram_news' => Yii::_t('forms.notify_telegram_news'),
      'notify_telegram_categories' => Yii::_t('forms.notify_telegram_categories'),
      'notify_push_system' => Yii::_t('forms.notify_telegram_system'),
      'notify_push_news' => Yii::_t('forms.notify_telegram_news'),
      'notify_push_categories' => Yii::_t('forms.notify_telegram_categories'),
      'postback_url' => Yii::_t('promo.settings.postback_url'),
      'complains_postback_url' => Yii::_t('promo.settings.complains_postback_url'),
    ];
  }


  public function createUser()
  {
    $connection = \Yii::$app->db;
    $transaction = $connection->beginTransaction();
    $attributes = $this->getAttributes();
    $partnerCanViewPromo = ArrayHelper::getValue($attributes, 'partner_hide_promo');
    /** @var Module $usersModule */
    $usersModule = Yii::$app->getModule('users');
    unset($attributes['reseller_can_hide_promo'], $attributes['partner_hide_promo']);
    if (array_key_exists('password', $attributes) && empty($attributes['password'])) {
      unset($attributes['password']);
    }
    if (Yii::$app->user->identity->canChangeManager($this->user)) {
      if (!$attributes['manager_id']) $attributes['manager_id'] = null;
    }

    try {
      foreach ($attributes as $name => $value) {
        $this->user->{$name} = $value;
      }

      $invitation = $this->user->isNewRecord
        ? UserInvitation::findByCredentials($this->user->email)
        : null;

      if ($invitation) {
        $this->user->password = $invitation->password;
      }

      $oldStatus = $this->user->getOldAttribute('status');
      if ($this->user->save()) {
        $this->user->saveReferrer($this->referrer_id);

        if ($invitation) {
          $invitation->setUser($this->user, UserInvitation::STATUS_SIGNUP_BY_ADMIN);
          $invitation->save();
        }

        if ($partnerCanViewPromo !== null) {
          /** @var \mcms\promo\components\api\PartnerCanViewPromo $partnerCanViewPromoApi */
          $partnerCanViewPromoApi = Yii::$app->getModule('promo')->api('partnerCanViewPromo');
          $partnerCanViewPromo
            ? $partnerCanViewPromoApi->assign($this->getUser()->id)
            : $partnerCanViewPromoApi->revoke($this->getUser()->id)
          ;
        }

        $transaction->commit();

        $roles = $this->canUpdateUserRoles()
          ? $this->roles
          : ($this->isNewUser() ? [$usersModule::PARTNER_ROLE] : [])
        ;

        count($roles) && $this->assignRole($roles);

        if ($this->isNewUser()) {
          $this->user->generateAuthKey();
          $this->user->save();

          (new EventUserCreated($this->user, $this->password))->trigger();
          return ;
        } else {
          (new EventUserUpdated($this->user, $this->password))->trigger();
        }

        if ($oldStatus == $this->user->status) return ;

        if (
          in_array($this->user->status, static::$blockedStatuses)
          && $oldStatus == User::STATUS_ACTIVE
        ) {
          (new EventUserBlocked($this->user))->trigger();
          return ;
        }

        if (
          $this->user->isActive()
          && $oldStatus == User::STATUS_ACTIVATION_WAIT_HAND
        ) {
          if (!$usersModule->isRegistrationWithReferrals()) {
            (new EventUserApprovedWithoutReferrals($this->user))->trigger();
            return ;
          }
          (new EventUserApproved($this->user, $this->user->getReferralLink()))->trigger();
          return ;
        }

        (new EventStatusChanged($this->user))->trigger();
      }
    } catch (\Exception $e) {
      if ($transaction) $transaction->rollBack();
      throw $e;
    }
  }

  /**
   * @return User
   */
  public function getUser()
  {
    return $this->user;
  }

  public function canUserDelegateResellerHidePromo()
  {
    return array_key_exists('root', Yii::$app->authManager->getRolesByUser(Yii::$app->user->id))
      && array_key_exists('reseller', Yii::$app->authManager->getRolesByUser($this->getUser()->id))
      ;
  }

  public function canResellerHidePromo($resellerId = null)
  {
    return Yii::$app->getModule('promo')->isResellerCanHidePromo($resellerId ? : Yii::$app->user->id);
  }

  public function canPartnerViewPromo()
  {
    return Yii::$app->getModule('promo')->api('partnerCanViewPromo')->isPromoHidden($this->getUser()->id);
  }

  /**
   * @return bool
   */
  public function canAddReferrer()
  {
    return !$this->isNewUser() && $this->user->canHaveReferrer();
  }

  public function getRoles()
  {
    $roles = ArrayHelper::map($this->user->getRoles()->all(), 'name', 'name');
    return $this->user->isNewRecord && !$roles ? [Module::PARTNER_ROLE => Module::PARTNER_ROLE] : $roles;
  }

  public static function getRolesList()
  {
    return array_filter(Yii::$app->getModule('users')->api('roles', ['removeGuest'])->getResult(), function($role) {
      $authManager = new AuthItemsManager;
      return Yii::$app->user->can($authManager->getRolePermissionName($role));
    });
  }

  public function canUpdateUserRoles()
  {
    return Yii::$app->user->can('UsersUsersUpdateUserRoles');
  }

  private function assignRole($roles)
  {
    if (!is_array($roles)) {
      $roles = [$roles];
    }
    $authManager = Yii::$app->authManager;

    foreach ($this->user->getRoles()->all() as $role) {
      $authManager->revoke($role, $this->user->id);
    }

    foreach ($roles as $role) {
      if (!$authRole = $authManager->getRole($role)) continue;
      $authManager->assign($authRole, $this->user->id);
    }
  }

  /**
   * @param mixed $roles
   * @return UserForm
   */
  public function setRoles($roles)
  {
    $this->roles = $roles;
    return $this;
  }

  /**
   * @return array
   */
  public static function getModulesDropDown()
  {
    $modulesEventList = [];

    foreach (['statistic', 'notifications', 'promo', 'payments', 'pages', 'support', 'users', 'alerts', 'credits'] as $moduleId) {

      $moduleApiResult = Yii::$app->getModule('modmanager')
        ->api('moduleById', ['moduleId' => $moduleId])
        ->getResult()
      ;
      if ($moduleApiResult) $modulesEventList[$moduleApiResult->id] = Yii::_t('forms.module-' . $moduleId);
    }

    return $modulesEventList;
  }

  public function isPartner()
  {
    return array_key_exists('partner', Yii::$app->authManager->getRolesByUser($this->getUser()->id));
  }

  /**
   * Получить текущее значение поля из UserPromoSettings
   * TRICKY: ТОЛЬКО из UserPromoSettings. Используется в @see \mcms\promo\validators\GlobalPBValidator::validateAttribute()
   * @param $attribute
   * @return mixed
   */
  public function getOldAttribute($attribute)
  {
    return $this->userPromoSettingsApi->getModel($this->getUser()->id)->$attribute;
  }
}