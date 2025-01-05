<?php
namespace mcms\statistic\tests\unit\stat_filters;

use mcms\common\codeception\TestCase;
use mcms\common\helpers\ArrayHelper;
use mcms\statistic\controllers\StatFiltersController;
use Yii;

/**
 * Class ControllerTest
 * @package mcms\statistic\tests\unit\stat_filters
 */
class ControllerTest extends TestCase
{

  public function _fixtures()
  {
    return $this->convertFixtures([
      'users.users', 'promo.streams', 'promo.sources'
    ]);
  }

  protected function setUp()
  {
    parent::setUp();
    Yii::$app->db->createCommand('DELETE FROM stat_filters')->execute();
    $this->loginAsRoot();
  }

  protected function tearDown()
  {
    Yii::$app->user->logout();
    parent::tearDown();
  }

  public function testUserNotFound()
  {
    $result = self::getController()->actionUsers();

    $this->assertEmpty(ArrayHelper::getValue($result, 'results', []));

    $result = self::getController()->actionUsers('101');
    $this->assertEmpty(ArrayHelper::getValue($result, 'results', []));
  }

  public function testUserFound()
  {
    Yii::$app->db->createCommand()->insert('stat_filters', [
      'user_id' => 101,
    ])->execute();
    $result = self::getController()->actionUsers('test_user_1');
    $this->assertEquals(['results' => [['text' => '#101 - test_user_1@mail.ru', 'id' => 101]]], $result);
  }

  public function testStreamNotFound()
  {
    $result = self::getController()->actionStreams();
    $this->assertEmpty(ArrayHelper::getValue($result, 'results', []));

    $result = self::getController()->actionStreams('eam101_');
    $this->assertEmpty(ArrayHelper::getValue($result, 'results', []));
  }

  public function testStreamFound()
  {
    Yii::$app->db->createCommand()->insert('stat_filters', [
      'stream_id' => 1,
    ])->execute();
    $result = self::getController()->actionStreams('eam101_');
    $this->assertEquals(['results' => [['text' => '#1 - Stream101_1', 'id' => 1]]], $result);
  }

  public function testSourceNotFound()
  {
    $result = self::getController()->actionSources();
    $this->assertEmpty(ArrayHelper::getValue($result, 'results', []));

    $result = self::getController()->actionSources('allbestprlnd');
    $this->assertEmpty(ArrayHelper::getValue($result, 'results', []));
  }

  public function testSourceFound()
  {
    Yii::$app->db->createCommand()->insert('stat_filters', [
      'source_id' => 1,
    ])->execute();
    $result = self::getController()->actionSources('allbestprlnd');

    $this->assertEquals(['results' => [['text' => '#1 - http://allbestprlnd.com', 'id' => 1]]], $result);
  }


  /**
   * @return StatFiltersController
   */
  protected static function getController()
  {
    return new StatFiltersController('stat-filters', Yii::$app->getModule('statistic'));
  }

}