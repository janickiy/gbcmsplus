<?php
namespace mcms\user\tests\models;

use mcms\notifications\models\EmailNotification;
use mcms\user\models\SignupForm;
use mcms\user\models\User;
use mcms\user\Module;
use mcms\user\tests\fixtures\UsersFixture;
use Yii;
use mcms\common\codeception\TestCase;

/**
 * Проверка регистрации
 * Class RegistrationTest
 * @package mcms\user\tests\models
 */
class RegistratioClosedTest extends TestCase
{
  const PASS = '123123';
  const RUB = 'rub';
  const EUR = 'eur';
  const USD = 'usd';
  const RU = 'ru';
  const EN = 'en';
  const USERNAME = 'test_user_registration_test';
  const EMAIL = 'test_user_registration_test@testexample.com';

  protected $authApi;

  protected function _before()
  {
    Yii::$app->db->createCommand('set foreign_key_checks=0')->execute();
    Yii::$app->db->createCommand('delete from sources')->execute();
    Yii::$app->db->createCommand('
      delete from auth_assignment
      where user_id = (
      select users.id from users where email = "'. self::EMAIL . '")
    ')->execute();
    Yii::$app->db->createCommand('delete from users where email = "' . self::EMAIL . '"')->execute();
    Yii::$app->db->createCommand('delete from email_notifications where email = "' . self::EMAIL . '"')->execute();
    $this->setRegistrationType(Module::SETTINGS_REGISTRATION_TYPE_HAND);
    Yii::$app->db->createCommand('set foreign_key_checks=1')->execute();

    $this->setRegistrationType(Module::SETTINGS_REGISTRATION_TYPE_CLOSED);
  }

  public function _fixtures()
  {
    return $this->convertFixtures([
      'users.users'
    ]);
  }

  protected function getUserAttributes()
  {
    return [
      'username' => self::USERNAME,
      'email' => self::EMAIL,
      'password' => self::PASS,
      'passwordRepeat' => self::PASS,
      'skype' => 'test_skype',
      'currency' => self::RUB,
      'language' => self::RU,
    ];
  }

  protected function setRegistrationType($type)
  {
    $this->setModuleSetting(Module::SETTINGS_REGISTRATION_TYPE, $type);
  }

  protected static function getFormModel()
  {
    return new SignupForm();
  }

  /**
   * @return \mcms\user\components\api\Auth
   */
  protected function getAuthApi()
  {
    if ($this->authApi) {
      return $this->authApi;
    }

    $this->authApi = $authApi = Yii::$app->getModule('users')->api('auth');
    return $this->getAuthApi();
  }

  /**
   * @return null|User
   */
  protected static function getUser()
  {
    return User::findOne(['email' => self::EMAIL]);
  }

  /**
   * @return null|EmailNotification
   */
  protected static function getEmail()
  {
    return EmailNotification::find()->where(['email' => self::EMAIL])->orderBy(['id' => SORT_DESC])->one();
  }

  public function testValidUser()
  {
    $model = self::getFormModel();

    $signUpResult = $this->getAuthApi()->signUp($model, [$model->formName() => $this->getUserAttributes()]);
    $this->assertNull($signUpResult, 'user should not be registered');

    $this->assertNull(self::getUser(), 'user should be created');
  }

  public function testInvalidUserPass()
  {
    $model = self::getFormModel();

    $invalidPassAttributes = $this->getUserAttributes();
    $invalidPassAttributes['passwordRepeat'] .= '1';
    $signUpResult = $this->getAuthApi()->signUp($model, [$model->formName() => $invalidPassAttributes]);
    $this->assertNull($signUpResult, 'user should not be registered');
  }

  public function testValidUserAlreadyExists()
  {
    $model = self::getFormModel();

    $attr = $this->getUserAttributes();
    $attr['username'] = 'test_user_1';
    $attr['email'] = 'test_user_1@mail.ru';
    $signUpResult = $this->getAuthApi()->signUp($model, [$model->formName() => $attr]);
    $this->assertNull($signUpResult, 'user should not be registered');
  }

}