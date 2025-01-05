<?php

namespace mcms\user\models;

use mcms\common\validators\ReCaptchaValidator;
use mcms\common\SystemLanguage;
use mcms\user\components\contacts\Factory;
use mcms\user\Module;
use yii\base\Model;
use Yii;

/**
 * Signup form
 */
class SignupForm extends Model
{
  public $username;
  public $email;
  public $password;
  public $passwordRepeat;
  public $currency;
  public $language;
  public $captcha;
  public $agreement;
  public $contact_type;
  public $contact_data;

  public $isRecaptchaValidator = true; // валидация для аффшарка. Прокидывается через di или в контроллере аффшарка.

  public $isManualRegister = true;

  protected $contactModel = false;

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      ['username', 'filter', 'filter' => 'trim'],
      ['email', 'filter', 'filter' => 'trim'],
      ['email', 'required'],
      ['email', 'email', 'checkDNS' => true],
      ['email', 'string', 'max' => 255],
      ['email', function ($attribute) {
        $existedUser = User::findByEmail($this->$attribute);
        if (!$existedUser) {
          return;
        }
        if ($existedUser->status == User::STATUS_ACTIVATION_WAIT_HAND) {
          $this->addError($attribute, Yii::_t('forms.signup_email_activation_wait_hand_error'));
          return;
        }
        if ($existedUser->status == User::STATUS_ACTIVATION_WAIT_EMAIL) {
          $this->addError($attribute, Yii::_t('forms.signup_email_activation_wait_email_error'));
          return;
        }
        $this->addError($attribute, Yii::_t('forms.signup_email_not_unique'));
      }],
      ['email', 'unique', 'targetClass' => '\mcms\user\models\User', 'message' => Yii::_t('forms.signup_email_not_unique')],

      [['currency', 'language'], 'string'],
      ['language', 'in', 'range' => array_keys(SystemLanguage::getLanguangesDropDownArray())],
      [['password', 'passwordRepeat'], 'required'],
      ['password', 'string', 'min' => 6],
      ['passwordRepeat', 'compare', 'compareAttribute' => 'password'],
      ['captcha', ReCaptchaValidator::class,
        'secret' => Yii::$app->reCaptcha->secretV2, 'when' => function ($model) {
        return $model->isRecaptchaValidator;
      }],
      [['contact_type', 'contact_data'], 'required', 'when' => function () {
        return $this->isManualRegister;
      }],
      ['contact_type', 'validateContactType'],
      ['contact_data', 'validateContactData', 'whenClient' => "function (attribute, value) {
        console.log(attribute);
        console.log(value);
      }"],

      ['agreement', 'boolean'],
    ];
  }

  public function attributeLabels()
  {
    return [
      'username' => Yii::_t('users.signup.username'),
      'password' => Yii::_t('users.signup.password'),
      'passwordRepeat' => Yii::_t('users.signup.passwordRepeat'),
      'currency' => Yii::_t('users.signup.currency'),
      'language' => Yii::_t('users.signup.language'),
      'agreement' => Yii::_t('users.signup.agreement'),
      'contact_type' => Yii::_t('users.signup.contact_type'),
      'contact_data' => Yii::_t('users.signup.contact_data'),
    ];
  }

  /**
   * @param $attribute
   */
  public function validateContactType($attribute)
  {
    if ($this->hasErrors()) {
      return;
    }

    $this->getContactModel()->setAttributes(['type' => $this->$attribute]);
    if ($this->getContactModel()->validate()) {
      return;
    }

    $errors = $this->getContactModel()->getErrors('type');
    foreach ($errors as $error) {
      $this->addError($attribute, $error);
    }
  }

  /**
   * @param $attribute
   */
  public function validateContactData($attribute)
  {
    if ($this->hasErrors()) {
      return;
    }

    $this->getContactModel()->setAttributes(['data' => $this->$attribute]);
    if ($this->getContactModel()->validate()) {
      return;
    }

    $errors = $this->getContactModel()->getErrors('data');
    foreach ($errors as $error) {
      $this->addError($attribute, $error);
    }
  }

  /**
   * @return UserContact
   */
  public function getContactModel()
  {
    if  ($this->contactModel === false) {
      $this->contactModel = new UserContact();
    }

    return  $this->contactModel;
  }

  /**
   * @param UserInvitation $invitation
   * @return bool
   */
  public function loadFromInvitation($invitation)
  {
    $this->email = $invitation->username;
    $this->password = $invitation->password;
    $this->passwordRepeat = $invitation->password;

    $this->agreement = true;
    $this->isManualRegister = false;

    return true;
  }

  /**
   * @return UserInvitation
   */
  public function findInvitation()
  {
    // пароль не указываем, тк при регистрации мог ввести пароль не такой как в приглашении
    return UserInvitation::findByCredentials($this->email);
  }

  /**
   * Signs user up.
   *
   * @return User|null the saved model or null if saving fails
   */
  public function signup()
  {
    /* @var \mcms\user\Module $module */
    $module = Yii::$app->getModule('users');

    if (!$this->language) {
      $this->language = $module->languageUser();
    }
    if (!$this->currency) {
      $this->currency = $module->currencyUser();
    }

    $user = new User();
    $user->username = $this->email;
    $user->email = $this->email;
    $user->language = $this->language;
    $user->currency = $this->currency;
    $user->color = Yii::$app->getModule('partners')->getColorTheme();
    $user->setPassword($this->password);
    $user->generateAuthKey();

    return $user;
  }
}
