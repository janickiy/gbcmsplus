<?php
namespace mcms\user\tests\unit\models;

use mcms\common\codeception\TestCase;
use Yii;
use mcms\user\models\LoginForm;

/**
 * Проверка залогинки с корректными и некорректными данными
 * Class LoginTest
 * @package mcms\user\tests\unit\models
 */
class LoginTest extends TestCase
{

  /** @var  array */
  protected $user;


  public function _fixtures()
  {
    return $this->convertFixtures([
      'users.users'
    ]);
  }

  protected function tearDown()
  {
    Yii::$app->user->logout();
    parent::tearDown();
  }

  public function testLoginNotExistUser()
  {
    $model = self::getFormModel();
    $model->attributes = [
      'username' => 'username_not_exist',
      'password' => 'password_not_exist',
    ];

    $this->assertFalse($model->login(), 'model should not login user');
    $this->assertTrue(Yii::$app->user->isGuest, 'user should not be logged in');
  }

  public function testLoginWrongPassword()
  {
    $model = self::getFormModel();
    $user = $this->getFirstUser();


    $model->attributes = [
      'username' => $user['username'],
      'password' => $user['username'] . 'wrong_password',
    ];

    $this->assertFalse($model->login(), 'model should not login user');

    $this->assertArrayHasKey('password', $model->errors, 'error message should be set');
    $this->assertTrue(Yii::$app->user->isGuest, 'user should not be logged in');
  }

  public function testSuccessLogin()
  {
    $model = self::getFormModel();
    $model->captchaCheck->resetAttempts($model->getUser()); // иначе тест падает из-за капчи
    $model->init();

    $user = $this->getFirstUser();

    $model->attributes = [
      'username' => $user['username'],
      'password' => $user['username'],
    ];

    $this->assertTrue($model->login(), 'model should login user');
    $this->assertFalse(Yii::$app->user->isGuest, 'user should be logged in');
  }

  /**
   * @return mixed
   */
  private function getFirstUser()
  {
    if ($this->user) return $this->user;
    return $this->user = $this->tester->grabFixture('users.users')['test_user_1'];
  }

  /**
   * @return LoginForm
   */
  private static function getFormModel()
  {
    return new LoginForm();
  }
}