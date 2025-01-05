<?php

namespace site\models;

use common\components\I18N;
use mcms\user\models\User;
use yii\base\Model;

/**
 * Registration form
 */
class RegistrationForm extends Model
{
    public $username;
    public $email;
    public $password;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['username', 'filter', 'filter' => 'trim'],
            ['username', 'required'],
            ['username', 'unique', 'targetClass' => '\mcms\user\models\User', 'message' => I18N::_t('forms.signup_username_not_unique')],
            ['username', 'string', 'min' => 2, 'max' => 255],

            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => '\mcms\user\models\User', 'message' => I18N::_t('forms.signup_email_not_unique')],

            ['password', 'required'],
            ['password', 'string', 'min' => 6],
        ];
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        $user = new User();
        $user->username = $this->username;
        $user->email = $this->email;
        $user->setPassword($this->password);
        $user->generateAuthKey();

        /** @var \mcms\user\Module $module */
        $module = \Yii::$app->getModule('users');
        if ($module->isRegistrationTypeEmailConfirm()) {
            $user->setAttribute('status', User::STATUS_ACTIVATION_WAIT_EMAIL);
            $user->generateEmailActivationCode();
        }

        if ($module->isRegistrationWithoutConfirm()) {
            $user->setAttribute('status', User::STATUS_ACTIVE);
        }

        if ($module->isRegistrationTypeByHand()) {
            $user->setAttribute('status', User::STATUS_ACTIVATION_WAIT_HAND);
        }

        return $user;
    }
}
