<?php

namespace mcms\user\models;

use mcms\common\DynamicActiveRecord;
use mcms\common\traits\model\FormAttributes;
use kartik\builder\Form;
use mcms\common\helpers\Link;
use mcms\common\widget\UserSelect2;
use mcms\payments\models\PartnerCompany;
use mcms\promo\models\SmartLink;
use mcms\promo\models\Source;
use mcms\promo\models\Stream;
use mcms\user\models\search\User as UserSearch;
use mcms\user\components\events\EventUserApproved;
use mcms\user\components\ReferralDecoder;
use mcms\user\Module;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\web\IdentityInterface;
use mcms\common\helpers\ArrayHelper;
use yii\data\ActiveDataProvider;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email_activation_code
 * @property string $email_unsubscribe_token
 * @property string $email
 * @property string $is_online
 * @property string $online_at
 * @property string $auth_key
 * @property string $access_token
 * @property string $token_expire
 * @property string $language
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $password write-only password
 * @property string $manager_id
 * @property integer $login_attempts Количество неудачных попыток авторизоваться
 *
 * @property User $manager
 * @property User $referrer
 * @property User[] $manageUsers
 * @property PartnerCompany $partnerCompany
 * @property UserContact[] $contacts
 */
class User extends DynamicActiveRecord implements IdentityInterface
{
    use FormAttributes;

    /**
     * @const string Отображать информацию о пользователе согласно формату по умолчанию для \mcms\common\widget\UserSelect2
     * @see getViewLink()
     */
    const LABEL_TEMPLATE_DEFAULT = 'default';
    /**
     * @const string Отображать информацию о пользователе в виде юзернейма
     * @see getViewLink()
     */
    const LABEL_TEMPLATE_USERNAME = 'username';

    const STATUS_DELETED = 7;
    const STATUS_BLOCKED = 8;
    const STATUS_INACTIVE = 9;
    const STATUS_ACTIVE = 10;
    const STATUS_ACTIVATION_WAIT_HAND = 11;
    const STATUS_ACTIVATION_WAIT_EMAIL = 12;

    const STATUS_REMEMBER_PASSWORD_BY_HAND = 13;

    const CACHE_KEY_IS_ONLINE = 'is_online';

    const ONLINE_LIFETIME = 600;
    const ACCESS_TOKEN_EXPIRE = 24 * 3600;

    const SCENARIO_REPLACEMENT = 'replacement';
    const SCENARIO_VIEW = 'view';

    const SESSION_AUTH_TOKEN_KEY = 'session.auth_token';
    const SESSION_BACK_IDENTITY_ID = 'session.back_identity';

    const TABLE_USERS_REFERRALS = '{{%users_referrals}}';
    const TABLE_AUTH_ITEM_CHILD = '{{%auth_item_child}}';
    const FIELD_ID = 'id';
    const FIELD_USER_ID = 'user_id';
    const FIELD_REFERRAL_ID = 'referral_id';

    const LOGIN_LOG_LIMIT = 10;

    const CONTACTS_DELIMITER = '|';
    const ROOT_USER_ID = 1;

    public $formAttributes = [];
    public static $availableStatuses = [
        self::STATUS_DELETED => 'users.main.status_deleted',
        self::STATUS_ACTIVE => 'users.main.status_active',
        self::STATUS_BLOCKED => 'users.main.status_blocked',
        self::STATUS_INACTIVE => 'users.main.status_inactive',
        self::STATUS_ACTIVATION_WAIT_HAND => 'users.main.status_activation_wait_hand',
        self::STATUS_ACTIVATION_WAIT_EMAIL => 'users.main.status_activation_wait_email',
    ];
    public static $cacheKeys = [
        self::CACHE_KEY_IS_ONLINE => 'users.is_online.user{userId}',
    ];
    public static $inactiveStatuses = [
        self::STATUS_DELETED,
        self::STATUS_BLOCKED,
        self::STATUS_INACTIVE,
        self::STATUS_ACTIVATION_WAIT_HAND,
        self::STATUS_ACTIVATION_WAIT_EMAIL,
    ];

    protected $additionalFieldsRelationName = 'params';
    protected $useObjectInsteadRelation = true;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%users}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public static function statusesDropDown()
    {
        $statuses = \mcms\user\models\User::$availableStatuses;

        foreach ($statuses as &$status) {
            $status = Yii::_t($status);
        }
        return $statuses;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            [['login_attempts'], 'integer'],
            ['status', function ($attribute, $params) {
                if (!in_array($this->$attribute, array_keys(self::$availableStatuses))) {
                    $this->addError($attribute, Yii::_t('main.wrong_status'));
                }
            }],
            [['email_activation_code', 'email_unsubscribe_token'], 'string'],
            ['email', 'filter', 'filter' => 'strtolower', 'skipOnArray' => true],
            ['comment', 'string'],
            [['comment'], 'filter', 'filter' => '\yii\helpers\HtmlPurifier::process'],
        ];
        return $rules;
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @param bool $activeOnly
     * @return null|static
     */
    public static function findByUsername($username, $activeOnly = true)
    {
        $condition = ['username' => $username];
        $activeOnly && $condition['status'] = self::STATUS_ACTIVE;

        return static::findOne($condition);
    }

    /**
     * Функция возвращает пользователя по email.
     *
     * @param $email
     * @param bool $activeOnly
     * @return User|null
     */
    public static function findByEmail($email, $activeOnly = true)
    {
        $condition = ['email' => $email];
        $activeOnly && $condition['status'] = self::STATUS_ACTIVE;

        return static::findOne($condition);
    }

    /**
     * Функция возвращает пользователя по email_unsubscribe_token.
     *
     * @param $token
     * @param bool $activeOnly
     * @return User|null
     */
    public static function findByEmailUnnsubscribeToken($token, $activeOnly = true)
    {
        $condition = ['email_unsubscribe_token' => $token];
        $activeOnly && $condition['status'] = self::STATUS_ACTIVE;

        return static::findOne($condition);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return boolean
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int)substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function getNewAccessToken()
    {
        if (time() > $this->token_expire) {
            $this->access_token = Yii::$app->getSecurity()->generateRandomString(32);
            $this->token_expire = time() + self::ACCESS_TOKEN_EXPIRE;
            return $this->save() ? [$this->access_token, $this->token_expire] : false;
        }
        return [$this->access_token, $this->token_expire];
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    public static function generateNewPassword()
    {
        return Yii::$app->security->generateRandomString(13);
    }

    public function generateEmailActivationCode()
    {
        $this->email_activation_code = Yii::$app->security->generateRandomString() . '_' . time();
    }

    public function generateEmailUnsuscribeToken()
    {
        $this->email_unsubscribe_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    /**
     * Removes password reset token
     */
    public function removeEmailUnsubscribeToken()
    {
        $this->email_unsubscribe_token = null;
    }

    public function scenarios()
    {
        $adminCreateFields = $adminEditFields =
            [
                'username', 'email', 'password', 'status', 'language', 'comment',
                'notify_browser_system', 'notify_telegram_system', 'notify_push_system', 'notify_push_news', 'notify_telegram_news', 'notify_browser_news',
                'notify_email', 'notify_email_system', 'notify_email_news',
                'notify_email_categories', 'notify_browser_categories', 'notify_telegram_categories', 'notify_push_categories',
            ];
        if ($this->canChangeManager($this)) {
            $adminCreateFields[] = 'manager_id';
            $adminEditFields[] = 'manager_id';
        }
        return array_merge(parent::scenarios(), [
            'create' => ['username', 'email', 'password', 'status', 'language'],
            'edit' => ['username', 'email', 'password', 'status', 'language'],
            'activate' => ['status'],
            'online' => ['online_at'],
            self::SCENARIO_VIEW => ['username', 'email', 'status', 'language'],
            'adminCreate' => $adminCreateFields,
            'adminEdit' => $adminEditFields,
        ]);
    }

    public function getStatus()
    {
        if ($this->scenario === self::SCENARIO_REPLACEMENT) {
            return $this->getNamedStatus();
        }

        return $this->getAttribute('status');
    }

    public function getParams()
    {
        return UserParam::getByUserId($this->id);
    }

    public function setOnline()
    {
        $this->online_at = time();
        return $this;
    }

    public function setOffline()
    {
        $this->online_at = 0;
        return $this;
    }

    /**
     * @return bool
     */
    public function isOnline()
    {
        return (time() - $this->online_at) < self::ONLINE_LIFETIME;
    }

    public function getNamedStatus()
    {
        return Yii::_t(ArrayHelper::getValue(static::$availableStatuses, $this->getAttribute('status')));
    }

    public function renewAuthTokenAndSave()
    {
        $this->generateAuthKey();
        return $this->save();
    }

    /**
     * @return ActiveQuery
     */
    public function getRoles()
    {
        return $this
            ->hasMany(Role::class, ['name' => 'item_name'])
            ->viaTable('auth_assignment', ['user_id' => 'id'])
            ->where([Role::tableName() . '.type' => 1]);
    }

    public function getRole()
    {
        return $this
            ->hasOne(Role::class, ['name' => 'item_name'])
            ->viaTable('auth_assignment', ['user_id' => 'id'])
            ->where([Role::tableName() . '.type' => 1]);
    }

    public function getNamesRoles()
    {
        $roles = $this->getRoles()->select(['name'])->column();
        if (count($roles) > 0) {
            return implode(', ', array_map(function ($role) {
                    return ucfirst($role);
                },
                    $roles
                )
            );
        }
        return Yii::_t('main.no_roles');
    }

    public function getReplacements()
    {
        return [
            'id' => [
                'value' => $this->isNewRecord ? null : $this->id,
                'help' => [
                    'label' => 'users.replacements.user_id'
                ]
            ],
            'username' => [
                'value' => $this->isNewRecord ? null : $this->username,
                'help' => [
                    'label' => 'users.replacements.user_username'
                ]
            ],
            'email' => [
                'value' => $this->isNewRecord ? null : $this->email,
                'help' => [
                    'label' => 'users.replacements.user_email'
                ]
            ],
            'language' => [
                'value' => $this->isNewRecord ? null : $this->language,
                'help' => [
                    'label' => 'users.replacements.user_language'
                ]
            ],
            'status' => [
                'value' => $this->isNewRecord ? null : $this->getNamedStatus(),
                'help' => [
                    'label' => 'users.replacements.user_status'
                ]
            ],
            'password_reset_token' => [
                'value' => $this->password_reset_token,
                'help' => [
                    'label' => 'users.replacements.password_reset_token'
                ]
            ],
            'roles' => [
                'value' => $this->isNewRecord ? null : implode(',', array_map(function (Role $role) {
                    return $role->name;
                }, $this->getRoles()->all())),
                'help' => [
                    'label' => 'users.replacements.user_roles'
                ]
            ]
        ];
    }

    public function attributeLabels()
    {
        return [
            'email' => Yii::_t('users.forms.user_email'),
            'username' => Yii::_t('users.forms.user_username'),
            'status' => Yii::_t('users.forms.user_status'),
            'password' => Yii::_t('users.forms.user_password'),
            'language' => Yii::_t('users.forms.user_language'),
            'phone' => Yii::_t('users.forms.user_phone'),
            'skype' => Yii::_t('users.forms.user_skype'),
            'referralLink' => Yii::_t('users.forms.user_referral_link'),
            'created_at' => Yii::_t('users.forms.user_created_at'),
            'manager_id' => Yii::_t('users.forms.manager_id'),
        ];
    }

    public function getReferrer()
    {
        return $this
            ->hasOne(User::class, ['id' => self::FIELD_USER_ID])
            ->viaTable(self::TABLE_USERS_REFERRALS, [self::FIELD_REFERRAL_ID => 'id']);
    }

    public function getReferrals()
    {
        return $this
            ->hasMany(User::class, ['id' => self::FIELD_REFERRAL_ID])
            ->viaTable(self::TABLE_USERS_REFERRALS, [self::FIELD_USER_ID => 'id']);
    }

    public function setReferrer($referralId)
    {
        $decoded = ReferralDecoder::decode($referralId);
        /** @var User $user */
        $user = self::findOne(['id' => $decoded]);
        if ($user === null) return;
        $this->link('referrer', $user);
    }

    public function updateOnlineOffline()
    {
        $key = strtr(static::$cacheKeys[self::CACHE_KEY_IS_ONLINE], ['{userId}' => $this->id]);
        if (!Yii::$app->cache->get($key)) {
            $this->setOnline();
            $this->save();
            Yii::$app->cache->set($key, true, self::ONLINE_LIFETIME);
        }
    }

    public function initFormAttributes()
    {
        $statuses = User::$availableStatuses;

        $this->formAttributes = [
            'email' => [
                'type' => Form::INPUT_TEXT,
                'label' => Yii::_t('forms.user_email')
            ],
            'username' => [
                'type' => Form::INPUT_TEXT,
                'label' => Yii::_t('forms.user_username')
            ],
            'status' => [
                'type' => Form::INPUT_DROPDOWN_LIST,
                'items' => $statuses,
                'label' => Yii::_t('forms.user_status')
            ],
            'password' => [
                'type' => Form::INPUT_PASSWORD,
                'label' => Yii::_t('forms.user_password')
            ],
            'language' => [
                'type' => Form::INPUT_DROPDOWN_LIST,
                'items' => ['ru' => Yii::_t('forms.russian'), 'en' => Yii::_t('forms.english')],
                'label' => Yii::_t('forms.user_language')
            ],
        ];
    }

    /**
     * @param string $role
     * @return bool
     */
    public function hasRole($role): bool
    {
        return Yii::$app->authManager->checkAccess($this->id, $role);
    }

    /**
     * @param array $roles
     * @return bool
     */
    public function hasOneOfRoles(array $roles): bool
    {
        $result = false;
        foreach ($roles as $role) {
            $result = Yii::$app->authManager->checkAccess($this->id, $role) ?: $result;
        }

        return $result;
    }

    /**
     * возвращает отформатированную строку вида:
     * "#1 - user@test.email"
     *
     * @return string
     */
    public function getStringInfo()
    {
        return Yii::$app->formatter->asText(UserSelect2::format(
            [
                'id' => $this->id,
                'username' => $this->username,
                'email' => $this->email
            ]
        ));
    }

    /**
     * Ссылка на пользователя
     * TRICKY При использовании метода в виджете списка или деталки, нужно использовать форматтер raw, а не html, иначе
     * будет удален аттрибут data-pjax
     * @param string $labelTemplate Тип содержимого ссылки
     * @return string
     * @see User::LABEL_*
     */
    public function getViewLink($labelTemplate = User::LABEL_TEMPLATE_DEFAULT)
    {
        $label = $this->getViewLabel($labelTemplate);
        return Link::get(
            '/users/users/view',
            ['id' => $this->id], ['data-pjax' => 0], $label, false
        );
    }

    /**
     * Ссылка на менеджера
     * TRICKY При использовании метода в виджете списка или деталки, нужно использовать форматтер raw, а не html, иначе
     * будет удален аттрибут data-pjax
     * @return string
     */
    public function getManagerLink()
    {
        if (!$this->manager_id) {
            return '';
        }

        return Link::get(
            '/users/users/view',
            ['id' => $this->manager_id],
            ['data-pjax' => 0],
            $this->manager->email,
            false
        );
    }

    /**
     * Можно ли просматиривать колонку с менеджерами
     * @return bool
     */
    public function canViewManager()
    {
        $managerCount = User::find()->where('manager_id IS NOT NULL')->count();
        return $managerCount > 0 && Yii::$app->user->can(UserSearch::PERMISSION_VIEW_MANAGER_USER);
    }

    /**
     * Ссылка на редактирование пользователя
     * @param string $labelTemplate Тип содержимого ссылки
     * @return string
     * @see User::LABEL_*
     */
    public function getEditLink($labelTemplate = User::LABEL_TEMPLATE_DEFAULT)
    {
        $label = $this->getViewLabel($labelTemplate);
        return Link::get(
            '/users/users/update',
            ['id' => $this->id], ['data-pjax' => 0], $label, false
        );
    }

    /**
     * Лейбл пользователя
     * @param string $labelTemplate
     * @return string
     */
    public function getViewLabel($labelTemplate = User::LABEL_TEMPLATE_DEFAULT)
    {
        return $labelTemplate == User::LABEL_TEMPLATE_USERNAME ? $this->username : $this->getStringInfo();
    }

    public function getLoginLog()
    {
        $query = LoginLog::find();
        $query->where(['user_id' => $this->id]);

        return new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['created_at' => SORT_DESC]],
            'pagination' => [
                'pageSize' => self::LOGIN_LOG_LIMIT,
            ],
        ]);
    }

    public function getReferralLink()
    {
        return Yii::$app->getModule('users')->api('userLink')->buildReferralLink($this->id);
    }

    /**
     * Есть ли доступ для просмотра данных связанных с пользователем
     * @param int $userId ID пользователя для проверки
     * @param bool $canViewUserWithoutManager @see \mcms\user\components\rbac\ViewUserRule
     * @return bool
     */
    public function canViewUser($userId, $canViewUserWithoutManager = false)
    {
        return Yii::$app->user->can(
            'UsersUserView',
            ['userId' => $userId, 'canViewUserWithoutManager' => $canViewUserWithoutManager]
        );
    }

    /**
     * ID не соответствует ID текущего авторизованного пользователя
     * @param int $userId
     * @return bool
     */
    public function isNotCurrentUser($userId)
    {
        return !$this->isCurrentUser($userId);
    }

    /**
     * ID соответствует ID текущего авторизованного пользователя
     * @param int $userId
     * @return bool
     */
    public function isCurrentUser($userId)
    {
        return Yii::$app->user->id == $userId;
    }

    public function canViewPromo()
    {
        if (Yii::$app->getModule('users')
            ->api('userBack')
            ->hasOriginalLoggedInUserRoleAdminRootReseller()) {
            return true;
        }


        return !Yii::$app->getModule('promo')
            ->api('partnerCanViewPromo')
            ->isPromoHidden(Yii::$app->getUser()->id);
    }

    /**
     * @return bool
     */
    public function canHaveReferrer()
    {
        return Yii::$app->authManager->checkAccess($this->id, Module::PERMISSION_CAN_HAVE_REFERRER);
    }

    /**
     * @return bool
     */
    public function canHaveReferral()
    {
        return Yii::$app->authManager->checkAccess($this->id, Module::PERMISSION_CAN_HAVE_REFERRAL);
    }

    /**
     * @return bool
     */
    public function userHasReferrer()
    {
        return $this->isNewRecord ? false : $this->getReferrer()->exists();
    }

    /**
     * @param integer $referral_id
     * @return bool
     */
    public function hasUserReferral($referral_id)
    {
        return $this->getReferrals()->where(['id' => $referral_id])->exists();
    }

    /**
     * @param integer $referrerId
     * @return false|int
     * @throws \yii\db\Exception
     */
    public function saveReferrer($referrerId)
    {
        /** @var User $existedReferrer */
        $existedReferrer = $this->referrer;
        if (!$referrerId && $existedReferrer) {
            $this->unlink('referrer', $existedReferrer, true);
        }
        /** @var User $referrer */
        $referrer = self::findOne(['id' => $referrerId]);
        if (!$referrer) return;
        if (!$existedReferrer) {
            $this->link('referrer', $referrer);
            return;
        }
        if ($existedReferrer->id == $referrerId) {
            return;
        }
        $this->unlink('referrer', $existedReferrer, true);
        $this->link('referrer', $referrer);
    }

    public static function activate($activateCode)
    {
        if (!$activateCode) return false;
        /** @var \mcms\user\models\User $user */
        $user = static::findOne([
            'email_activation_code' => $activateCode
        ]);

        if (!$user) {
            return false;
        }

        $user->email_activation_code = '';
        $user->status = self::STATUS_ACTIVE;
        if ($user->save(false)) {
            (new EventUserApproved($user, $user->getReferralLink()))->trigger();
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->status == self::STATUS_ACTIVE;
    }

    /**
     * @return bool
     */
    public function getIsInacitve()
    {
        return in_array($this->status, self::$inactiveStatuses);
    }

    public function isModerationByHand()
    {
        return $this->status == self::STATUS_ACTIVATION_WAIT_HAND;
    }

    public function beforeSave($insert)
    {
        if (!$insert && $this->isPasswordChanged()) {
            $this->resetUserSessions();
        }
        if ($insert) {
            $this->notify_email = $this->email;
        }
        if ($this->isActivation()) {
            SmartLink::createForUser($this->id);
        }
        return parent::beforeSave($insert);
    }

    /**
     * @param bool $insert
     * @param array $changedAttributes
     * @throws \yii\base\Exception
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($insert && (int)$this->status === User::STATUS_ACTIVE) {
            SmartLink::createForUser($this->id);
        }

        parent::afterSave($insert, $changedAttributes);
    }

    private function isPasswordChanged()
    {
        return
            ArrayHelper::getValue($this->oldAttributes, 'password_hash') !==
            ArrayHelper::getValue($this->attributes, 'password_hash');
    }

    /**
     * Активация ли это партнера
     * @return bool
     */
    private function isActivation()
    {
        return
            ArrayHelper::getValue($this->oldAttributes, 'status') !== (int)$this->status &&
            (int)$this->status === self::STATUS_ACTIVE;
    }


    private function resetUserSessions()
    {
        // разлогиним пользователя, если сменили ему пароль
        $this->generateAuthKey();
        // логиним обратно текущего юзера
        if (Yii::$app->user->id == $this->id) {
            Yii::$app->session->set(self::SESSION_AUTH_TOKEN_KEY, $this->getAuthKey());
            Yii::$app->user->setIdentity($this);
        }
    }

    /**
     * @return ActiveQuery
     */
    public function getSources()
    {
        return $this->hasMany(Source::class, ['user_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getStreams()
    {
        return $this->hasMany(Stream::class, ['user_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getContacts()
    {
        return $this->hasMany(UserContact::class, ['user_id' => 'id']);
    }

    /**
     * TODO: Удалить этот метод и его использование в submodules/mcms/partners/themes/basic/layouts/main.php:153
     * TODO: после того, как менеджеры заполнят свои контакты по человечески (через UserContact)
     * Получить массив контактов
     * @return array
     */
    public function getContactsArray()
    {
        return explode(self::CONTACTS_DELIMITER, $this->skype);
    }

    /**
     * @return ActiveQuery
     */
    public function getActiveContacts()
    {
        return $this->getContacts()->andWhere([UserContact::tableName() . '.is_deleted' => 0]);
    }

    /**
     * @return mixed
     */
    public function getUserPromoSettings()
    {
        return Yii::$app->getModule('promo')->api('userPromoSettings', ['getRelation' => true])->hasOne($this, 'id');
    }

    /**
     * @return mixed
     */
    public function getUserPaymentSettings()
    {
        return Yii::$app->getModule('payments')->api('userSettingsData', ['getRelation' => true])->hasOne($this, 'id');
    }

    /**
     * @return mixed
     */
    public function getUserWallets()
    {
        return Yii::$app->getModule('payments')->api('userWallet', ['getRelation' => true])->hasOne($this, 'id');
    }

    /**
     * @return mixed
     */
    public function getUserAutopayWallet()
    {
        return Yii::$app->getModule('payments')
            ->api('userWallet', ['getRelation' => true])
            ->hasOne($this, 'id')
            ->andWhere(['is_autopayments' => 1]);
    }

    /**
     * Привязанный пользователь (личный менеджер)
     * @return ActiveQuery
     */
    public function getManager()
    {
        return $this->hasOne(User::class, ['id' => 'manager_id']);
    }

    /**
     * Получить привязанных пользователей
     * @return ActiveQuery
     */
    public function getManageUsers()
    {
        return $this->hasMany(static::class, ['manager_id' => 'id']);
    }

    private static $_managerUsersByUserId = [];

    /**
     * Отдает ID партнеров которыми может управлять юзер
     * @param int $userId
     * @return array
     */
    public static function getManageUsersByUserId($userId)
    {
        if (!isset(self::$_managerUsersByUserId[$userId])) {
            self::$_managerUsersByUserId[$userId] = self::findOne(['id' => $userId])->getManageUsers()->select('id')->column();
        }
        return self::$_managerUsersByUserId[$userId];
    }

    /**
     * Отфильтровать записи недоступных пользователей текущего пользователя
     * @param ActiveRecord $modelOrTable
     * @param ActiveQuery $query
     * @param string $field
     * @return ActiveQuery
     */
    public function filterUsersItems($query, $modelOrTable, $field = 'created_by')
    {
        return self::filterUsersItemsByUser(Yii::$app->user->id, $query, $modelOrTable, $field);
    }

    /**
     * Отфильтровать записи недоступных пользователей
     * @param string $userId
     * @param ActiveQuery|Query $query
     * @param ActiveRecord|string $modelOrTable
     * @param string $field
     * @return ActiveQuery
     */
    public static function filterUsersItemsByUser($userId, $query, $modelOrTable, $field = 'created_by')
    {
        $table = $modelOrTable instanceof ActiveRecord ? $modelOrTable::tableName() : $modelOrTable;

        if (!Yii::$app->authManager->checkAccess($userId, Module::PERMISSION_CAN_MANAGE_ALL_USERS)) {
            $query->andWhere([
                'or',
                [$table . '.' . $field => self::getManageUsersByUserId($userId)],
                [$table . '.' . $field => null],
                [$table . '.' . $field => $userId],
            ]);
        }

        return $query;
    }

    /**
     * Отфильтровать недоступных пользователей
     * @param ActiveQuery $query
     * @param bool $canViewUserWithoutManager Должен ли быть доступ к пользователям, к которым не привязан менеджер.
     * Параметр сделан для того, что бы менеджер мог редактировать пользователей без привязанного менеджера в списке пользователей
     * и не мог управлять их записями, например тикетами, лендами и прочим
     * @return ActiveQuery
     */
    public function filterUsers($query, $canViewUserWithoutManager = false)
    {
        if (!Yii::$app->user->can(Module::PERMISSION_CAN_MANAGE_ALL_USERS)) {
            $condition = [
                'or',
                [static::tableName() . '.manager_id' => Yii::$app->user->id],
            ];

            if ($canViewUserWithoutManager) $condition[] = [static::tableName() . '.manager_id' => null];

            $query->andWhere($condition);
        }

        return $query;
    }

    /**
     * Получить список доступных пользователей
     * @return ActiveQuery
     */
    public function getAvailableUsers()
    {
        $query = static::find();
        $this->filterUsers($query);

        return $query;
    }

    /**
     * Есть доступ для смены менеджера привязанного к пользователю
     * @param User $user
     * @return bool
     */
    public function canChangeManager(User $user)
    {
        return Yii::$app->user->can(Module::PERMISSION_CAN_CHANGE_MANAGER_ALL_USERS)
            || Yii::$app->user->can(Module::PERMISSION_CAN_CHANGE_MANAGER_TO_OWNSELF_USERS_WITHOUT_MANAGER, ['user' => $user]);
    }

    /**
     * Управление ТБ провайдерами
     * @return bool
     */
    public function canManageTbProviders()
    {
        return Yii::$app->getModule('promo')->settings->getValueByKey(\mcms\promo\Module::SETTINGS_ENABLE_TB_SELL)
            || Yii::$app->user->can('PromoCanManageTbProvidersWithoutCanSellTb');
    }

    /**
     * @return ActiveQuery
     */
    public function getPartnerCompany()
    {
        return $this->hasOne(PartnerCompany::class, ['id' => 'partner_company_id'])->via('userPaymentSettings');
    }

    /**
     * @return int|null
     */
    public function getInvoicingCycle()
    {
        /** @var PartnerCompany $company */
        $company = $this->partnerCompany;
        if (!$company) {
            return null;
        }

        return $company->getInvoicingCycle();
    }
}
