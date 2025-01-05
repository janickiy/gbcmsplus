<?php
namespace mcms\user\models;

use mcms\common\validators\ReCaptchaValidator;
use Yii;
use yii\base\Model;

/**
 * Password reset request form
 */
class PasswordResetRequestForm extends Model
{
  public $email;
  public $password;

  public $captcha;

  /**
   * @return bool
   */
  public function shouldUseCaptcha()
  {
    // Капча для восстановления пароля должна быть всегда, иначе боты смогут задолбать письмами пользователей
    return true;
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['captcha'], ReCaptchaValidator::class,
        'secret' => Yii::$app->reCaptcha->secretV2,
        'uncheckedMessage' => 'Please confirm that you are not a bot.'
      ],
      ['email', 'filter', 'filter' => 'trim'],
      ['email', 'required'],
      ['password', 'string', 'min' => 6],
      ['email', 'email'],
      ['email', 'exist',
        'targetClass' => Yii::$app->user->identityClass,
        'filter' => ['status' => User::STATUS_ACTIVE],
        'message' => Yii::_t('users.forms.user_email_not_exist')
      ],
    ];
  }

  /**
   * @return User
   */
  public function getUser()
  {
    return User::findOne([
      'status' => User::STATUS_ACTIVE,
      'email' => $this->email
    ]);
  }


  public function setPasswordResetToken()
  {
    $user = $this->getUser();
    if ($user && !User::isPasswordResetTokenValid($user->password_reset_token)) {
      $user->generatePasswordResetToken();
      $user->save();
    }
  }

  /**
   * @return bool
   */
  public function setNewPassword()
  {
    $user = $this->getUser();
    $this->password = $user::generateNewPassword();
    $user->setPassword($this->password);
    $user->removePasswordResetToken();
    return $user->save();
  }
}
