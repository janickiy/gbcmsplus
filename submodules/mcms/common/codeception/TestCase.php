<?php
namespace mcms\common\codeception;

use rgk\settings\components\SettingsManager;
use Yii;
use mcms\user\models\User;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;
use mcms\common\output\FakeOutput;
use mcms\common\output\OutputInterface;

/**
 * Class FormatterTest
 * @package mcms\common\codeception
 */
class TestCase extends \Codeception\Test\Unit
{
  protected function setUp()
  {
    Yii::$container->set(OutputInterface::class, [
      'class' => FakeOutput::class
    ]);
    parent::setUp();
  }


  /**
   * @param array $fixtures
   * @return array
   */
  public function convertFixtures(array $fixtures)
  {
    $out = [];
    foreach ($fixtures as $fixture) {
      $out[$fixture] = $this->convertFixture($fixture);
    }
    return $out;
  }

  /**
   * @param $fixture
   * @return mixed
   */
  public function convertFixture($fixture)
  {
    list($module, $fixtureCode) = StringHelper::explode($fixture, '.');

    return ArrayHelper::getValue(Yii::$app->getModule($module)->fixtures, $fixtureCode);
  }

  /**
   * @param $setting
   * @param $value
   */
  public function setModuleSetting($setting, $value)
  {
    /** @var SettingsManager $settings */
    $settings = Yii::$app->get('settingsManager');
    $settings->offsetSet($setting, $value);
  }

  public function loginAsRoot()
  {
    $this->loginById(1);
  }

  /**
   * @param $id
   */
  public function loginById($id)
  {
    /* @var $user User */
    $user = User::findOne($id);
    Yii::$app->user->setIdentity($user);
  }

  public function loginAsReseller()
  {
    $this->loginById(4);
  }

  /**
   * Выполняем команду в БД
   * @param $command
   * @return int
   * @throws \yii\db\Exception
   */
  protected function executeDb($command)
  {
    return Yii::$app->db->createCommand($command)->execute();
  }

  /**
   * Так короче вызывать
   * @param string $command
   * @return \yii\db\Command
   */
  protected function getDbCommand($command = null)
  {
    return Yii::$app->db->createCommand($command);
  }
}
