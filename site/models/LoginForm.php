<?php

namespace site\models;

use mcms\user\components\CaptchaCheck;
use Yii;
use yii\base\Model;

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
        return $this->_captchaCheck->isCaptchaRequired($this->getUser());
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // TRICKY Валидация капчи должна быть в начале, иначе появится возможность подбора пароля,
            // так как скрипт будет говорить правильный пароль или нет, даже если капча введена неверно
            ['captcha', 'captcha', 'captchaAction' => '/users/site/captcha/', 'when' => function () {
                return $this->shouldUseCaptcha();
            }],
            // username and password are both required
            [['username', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
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
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, Yii::_t('forms.login_username_or_password_incorrect'));
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {

            $user = $this->getUser();
            $user->generateAuthKey();
            $user->save();

            $session = Yii::$app->getSession();
            $session->set($user::SESSION_AUTH_TOKEN_KEY, $user->getAuthKey());

            $loginResult = Yii::$app->user->login($user, $this->rememberMe ? 3600 * 24 * 30 : 0);
            if ($loginResult) {
                $this->_captchaCheck->resetAttempts($this->getUser());
            }
            return $loginResult;
        } else {
            $this->_captchaCheck->incrementAttempts($this->getUser());
            return false;
        }
    }

    /**
     * Finds user by [[username]]
     *
     * @return \mcms\user\models\User|null
     */
    public function getUser()
    {
        if ($this->_user === null) {
            /** @var \mcms\user\models\User $identity */
            $identity = Yii::$app->user->identityClass;
            $this->_user = $identity::findByUsername($this->username);
        }

        return $this->_user;
    }


    public function getRedirectUri()
    {
        return $this->getUser()->getRedirectUri();
    }

    /**
     * @return CaptchaCheck
     */
    public function getCaptchaCheck()
    {
        return $this->_captchaCheck;
    }
}