<?php
namespace mcms\promo\tests\unit\sources;

use mcms\common\codeception\TestCase;
use mcms\common\helpers\ArrayHelper;
use mcms\promo\commands\LandingConvertTestController;
use mcms\promo\models\LandingConvertTest;
use mcms\promo\models\Source;
use mcms\promo\models\SourceOperatorLanding;
use mcms\promo\Module;
use mcms\statistic\commands\CronController;
use Yii;

/**
 * Class ConvertTest
 * @package mcms\promo\tests\unit\sources
 */
class ConvertTest extends TestCase
{

  const SOURCE_ID = 3;

  /** @var  Source */
  public $source;

  public function _fixtures()
  {
    return $this->convertFixtures([
      'promo.sources',
      'promo.landings',
      'promo.operators',
      'promo.landing_operators',
    ]);
  }

  protected function setUp()
  {
    parent::setUp();

    $this->loginAsRoot();

    Yii::$app->db->createCommand()->delete('sources_operator_landings', [
      'source_id' => self::SOURCE_ID
    ])->execute();

    Yii::$app->db->createCommand()->delete('landing_convert_tests', [
      'source_id' => self::SOURCE_ID
    ])->execute();

    Yii::$app->db->createCommand()->delete('postbacks')->execute();
    Yii::$app->db->createCommand()->delete('hits')->execute();
    Yii::$app->db->createCommand()->delete('country_currency_log')->execute(); // иначе хиты неправильно достаются

    Yii::$app->db->createCommand()->delete('hit_params')->execute();

    Yii::$app->db->createCommand()->delete('hits_day_hour_group')->execute();
    Yii::$app->db->createCommand()->delete('hits_day_group')->execute();
    Yii::$app->db->createCommand()->delete('sold_subscriptions')->execute();
    Yii::$app->db->createCommand()->delete('subscriptions_day_hour_group')->execute();
    Yii::$app->db->createCommand()->delete('subscriptions_day_group')->execute();
    Yii::$app->db->createCommand()->delete('onetime_subscriptions')->execute();

    Yii::$app->db->createCommand()->delete('subscriptions')->execute();

    $this->source = Source::findOne(self::SOURCE_ID);
    $this->source->scenario = Source::SCENARIO_ADMIN_UPDATE_WEBMASTER_SOURCE;
    $this->setModuleSetting(Module::SETTINGS_LAND_CONVERT_TEST_MAX_HITS, 5);

  }


  /**
   * создать источник, начать конверт-тест с минимумом хитов = 5
   * запустить скрипт проверки, убедиться что тест ещё в процессе
   */
  public function testConvertTestNotCompleted()
  {
    $this->source->forceLaunchConvertTest = true;
    $this->source->save();

    self::assertEquals(5, count($this->getLands()));

    $this->convertTestHandler();

    self::assertNotEmpty(LandingConvertTest::findOne([
      'status' => LandingConvertTest::STATUS_ACTIVE,
      'source_id' => self::SOURCE_ID
    ]));

    self::assertEquals(5, count($this->getLands()));

    self::assertEquals(1, $this->getLastConvertTest()->status);
  }


  public function testConvertTestCompleted()
  {
    $this->source->forceLaunchConvertTest = true;
    $this->source->save();

    self::assertEquals(5, count($this->getLands()));
    $this->generateStatistic([
      '1_3' => [10, 5],
      '1_4' => [10, 10]
    ]);
    $this->convertTestHandler();
    $lands = $this->getLands();
    $indexed = ArrayHelper::map($lands, 'operator_id', 'landing_id');
    self::assertEquals(4, ArrayHelper::getValue($indexed, 1));

    self::assertEquals(4, count($lands));

    $operatorIds = ArrayHelper::getColumn($lands, 'operator_id');

    $operatorIds = array_unique($operatorIds);

    self::assertEquals(4, count($operatorIds));
    self::assertEquals(0, $this->getLastConvertTest()->status);
  }

  public function testConvertTestCompleted2()
  {
    $this->source->forceLaunchConvertTest = true;
    $this->source->save();

    self::assertEquals(5, count($this->getLands()));

    $this->generateStatistic([
      '1_4' => [10, 5],
      '1_3' => [10, 10]
    ]);

    $this->convertTestHandler();

    $lands = $this->getLands();

    $indexed = ArrayHelper::map($lands, 'operator_id', 'landing_id');
    self::assertEquals(3, ArrayHelper::getValue($indexed, 1));


    self::assertEquals(4, count($lands));

    $operatorIds = ArrayHelper::getColumn($lands, 'operator_id');

    $operatorIds = array_unique($operatorIds);

    self::assertEquals(4, count($operatorIds));

    self::assertEquals(0, $this->getLastConvertTest()->status);
  }



  /**
   * @return SourceOperatorLanding[]
   */
  protected function getLands()
  {
    return $this->source->getSourceOperatorLanding()->all();
  }

  /**
   * @param array $counts landId_operatorId => [countHits, countSubs]
   */
  private function generateStatistic($counts)
  {
    foreach ($counts as $landOp => $count) {
      list($operId, $landId) = explode('_', $landOp);
      list($hitsCount, $subsCount) = $count;

      $this->generateHitsAndSubs($operId, $landId, $hitsCount, $subsCount);
    }
    $cronController = new CronController('cron', Yii::$app->getModule('statistic'));
    $cronController->ignoreDateRangeLimit = true;
    $cronController->excludeHandlers = 'AliveOnsDayGroup,Ltv,Alive30OnsDayGroup';
    $cronController->actionIndex();
  }

  /**
   * @param $operId
   * @param $landId
   * @param $hitsCount
   * @param $subsCount
   */
  private function generateHitsAndSubs($operId, $landId, $hitsCount, $subsCount)
  {
    $insertedSubs = 0;

    for ($i = 0; $i < $hitsCount; $i++) {

      Yii::$app->db->createCommand()->insert('hits', [
        'time' => time(),
        'date' => date('Y-m-d'),
        'hour' => date('H'),
        'operator_id' => $operId,
        'landing_id' => $landId,
        'source_id' => self::SOURCE_ID
      ])->execute();

      $hitId = Yii::$app->db->getLastInsertID();

      Yii::$app->db->createCommand()->insert('hit_params', [
        'hit_id' => $hitId
      ])->execute();

      if ($insertedSubs == $subsCount) {
        continue;
      }

      Yii::$app->db->createCommand()->insert('subscriptions', [
        'hit_id' => $hitId,
        'trans_id' => md5($hitId),
        'time' => time(),
        'date' => date('Y-m-d'),
        'hour' => date('H'),
        'landing_id' => $landId,
        'source_id' => self::SOURCE_ID,
        'operator_id' => $operId,
        'currency_id' => 1,
        'phone' => 123456
      ])->execute();

      $insertedSubs++;
    }




  }

  private function convertTestHandler()
  {
    (new LandingConvertTestController('landing-convert-test', Yii::$app->getModule('promo')))->actionIndex();
  }

  /**
   * @return LandingConvertTest|\yii\db\ActiveRecord
   */
  private function getLastConvertTest()
  {
    /** @var LandingConvertTest $test */
    return $this->source->getLastLandingConvertTest()->one();
  }
}