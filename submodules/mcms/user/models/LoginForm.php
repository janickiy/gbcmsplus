<?php

namespace mcms\user\models;

use mcms\common\validators\ReCaptchaValidator;
use mcms\user\components\CaptchaCheck;
use Yii;
use yii\base\Model;
use yii\helpers\Json;

/**
 * Login form
 * @property CaptchaCheck $captchaCheck
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;

    private $_user;

    public $captcha;

    public $shouldUseCaptcha = false;

    /**
     * @var bool|int
     */
    protected $failReason;

    /** @var CaptchaCheck Определение потребности в отображении капчи */
    private $_captchaCheck;

    /**
     * Initializes the object.
     * This method is invoked at the end of the constructor after the object is initialized with the
     * given configuration.
     */
    public function init()
    {
        parent::init();
        $this->_captchaCheck = new CaptchaCheck;
    }

    /**
     * @return bool
     */
    public function shouldUseCaptcha()
    {
        return $this->shouldUseCaptcha && $this->_captchaCheck->isCaptchaRequired($this->getUser());
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // TRICKY Валидация капчи должна быть в начале, иначе появится возможность подбора пароля,
            // так как скрипт будет говорить правильный пароль или нет, даже если капча введена неверно
            [['captcha'], ReCaptchaValidator::class,
                'secret' => Yii::$app->reCaptcha->secretV2,
                'when' => function () {
                    return $this->shouldUseCaptcha();
                }],
            // username and password are both required
            [['username', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
            ['username', 'validateStatus'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'username' => Yii::_t('users.login.username'),
            'password' => Yii::_t('users.login.password'),
            'rememberMe' => Yii::_t('users.login.rememberMe')
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if ($this->hasErrors()) {
            return;
        }

        $user = $this->getUser();
        if (!$user) {
            $this->failReason = LoginAttempt::REASON_INVALID_LOGIN;
            $this->addError($attribute, Yii::_t('forms.login_username_or_password_incorrect'));

            return;
        }

        if (!$user->validatePassword($this->password)) {
            $this->failReason = LoginAttempt::REASON_INVALID_PASSWORD;
            $this->addError($attribute, Yii::_t('forms.login_username_or_password_incorrect'));

            return;
        }
    }

    /**
     * @param string $attribute
     * @param array $params
     */
    public function validateStatus($attribute, $params)
    {
        if ($this->hasErrors()) {
            return;
        }

        $user = $this->getUser();
        if (!$user) {
            return;
        }

        if ($user->getIsInacitve()) {
            $this->failReason = LoginAttempt::REASON_USER_INACTIVE;
            $this->addError($attribute, Yii::_t('forms.user_is_inactive'));
        }
    }

    /**
     * @return UserInvitation
     */
    public function findInvitation()
    {
        return UserInvitation::findByCredentials($this->username, $this->password);
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        if (!$this->validate()) {
            $user = $this->getUser();
            $this->_captchaCheck->incrementAttempts($user);

            if ($this->failReason === null && $this->hasErrors('captcha')) {
                $this->failReason = LoginAttempt::REASON_INVALID_CAPTCHA;
            }

            $loginAttempt = new LoginAttempt();
            $loginAttempt->user_id = $user ? $user->id : null;
            $loginAttempt->login = $this->username;
            $loginAttempt->password = $this->password;
            $loginAttempt->ip = Yii::$app->request->userIP;
            $loginAttempt->user_agent = Yii::$app->request->userAgent;
            $loginAttempt->server_data = Json::encode($_SERVER);
            $loginAttempt->fail_reason = $this->failReason;

            $loginAttempt->save();

            return false;
        }

        $user = $this->getUser();

        $session = Yii::$app->getSession();
        $session->set($user::SESSION_AUTH_TOKEN_KEY, $user->getAuthKey());

        $loginResult = Yii::$app->user->login($user, $this->rememberMe ? 3600 * 24 * 30 : 0);
        if ($loginResult) {
            $this->_captchaCheck->resetAttempts($this->getUser());
        }

        return $loginResult;
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === null) {
            $this->_user = User::findByUsername($this->username, false);
            $this->_user = $this->_user ? $this->_user : User::findByEmail($this->username, false);
        }

        return $this->_user;
    }

    /**
     * @return CaptchaCheck
     */
    public function getCaptchaCheck()
    {
        return $this->_captchaCheck;
    }
}