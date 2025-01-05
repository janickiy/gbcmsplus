<?php


namespace mcms\promo\commands;

use mcms\common\controller\ConsoleController;
use mcms\common\helpers\ArrayHelper;
use mcms\promo\components\ApiHandlersHelper;
use mcms\promo\models\LandingConvertTest;
use mcms\promo\models\SourceOperatorLanding;
use yii\helpers\Console;
use mcms\promo\components\events\LandingConvertTest as LandingConvertTestEvent;

/**
 * Class LandingConvertTestController
 * @package mcms\promo\commands
 */
class LandingConvertTestController extends ConsoleController
{

  /**
   * @var array - массив моделей активных тестов
   */
  private $tests = [];
  /**
   * @var array - массив моделей завершившихся тестов, но со статусом = active, т.е.
   * это те тесты, которые необходимо просчитать и завершить (сменить статус)
   */
  private $finishedTests = [];

  /** @var  \mcms\promo\components\events\LandingConvertTest */
  private $event;

  /**
   *
   */
  public function actionIndex()
  {

    $this->event = new LandingConvertTestEvent();
    $this->event->startTime = time();

    $this->stdout("Checking active tests..." . "\n");

    $this->tests = LandingConvertTest::findAll(['status' => LandingConvertTest::STATUS_ACTIVE]);

    $this->stdout("-- " . count($this->tests) . " active test(s) found: " . json_encode(ArrayHelper::getColumn($this->tests, 'id')) . "\n", Console::FG_YELLOW);

    $this->stdout("Ignoring tests in progress..." . "\n");

    $this->initFinishedTests();

    $this->stdout("-- " . count($this->finishedTests) . " are going to be calculated" . "\n", Console::FG_YELLOW);

    $this->calculateConvert();

    $this->stdout("Work finished" . "\n", Console::FG_GREEN);

    $this->event->endTime = time();
    $this->event->trigger();
  }

  /**
   *
   */
  private function initFinishedTests()
  {
    foreach ($this->tests as $test) {
      if ($test->isFinished) {
        $this->finishedTests[] = $test;
        $this->stdout("[accept] Test#" . $test->id . " for source_id=" . $test->source_id . " will be calculated\n", Console::FG_GREEN);
        continue;
      }
      $this->stdout("[ignore] Test#" . $test->id . " for source_id=" . $test->source_id . " is in progress and will NOT be calculated now\n", Console::FG_PURPLE);
    }
  }

  private function calculateConvert()
  {

    $this->event->countTests = count($this->tests);
    $this->event->countFinishedTests = count($this->finishedTests);

    if (count($this->finishedTests) == 0) return;

    $this->stdout("Calculating landings convert..." . "\n");

    foreach ($this->finishedTests as $test) {
      /** @var $test LandingConvertTest */

      $this->stdout("[testCalcBegin] Test#" . $test->id . " for source_id=" . $test->source_id . "\n");

      $converts = $test->getLandingsConvert();

      $this->stdout("Test#" . $test->id . " converts: " . json_encode($converts) . "\n");

      $sourceLandingsOperators = $test->source->sourceOperatorLanding;

      $groupedLands = $this->groupLandsByOperatorId($sourceLandingsOperators);

      foreach($groupedLands as $operatorId => $operLands) {

        $delete = $this->getLandsToDelete($operLands, $converts);
        $this->stdout("Test#" . $test->id . " source landings for operator#" . $operatorId . ": " . json_encode(ArrayHelper::getColumn($operLands, 'landing_id')) . "\n");
        $this->stdout("Test#" . $test->id . " source landings for operator#" . $operatorId . " to delete: " . json_encode($delete) . "\n");



        if (empty($delete)) continue;

        SourceOperatorLanding::deleteAll([
          'source_id' => $test->source_id,
          'operator_id' => $operatorId,
          'landing_id' => $delete
        ]);
      }

      /** чистим кэш микросервисов для данного источника */
      ApiHandlersHelper::clearCache('SourceLandingIdsGroupByOperator' . $test->source_id);

      $test->scenario = LandingConvertTest::SCENARIO_TEST_DEACTIVATE;
      $test->status = LandingConvertTest::STATUS_INACTIVE;
      $test->save();
      $this->stdout("[testCalcFinish] Test#" . $test->id . " for source_id=" . $test->source_id . "\n");
    }
  }

  /**
   * @param $sourceLandingsOperators
   * @return array
   */
  private function groupLandsByOperatorId($sourceLandingsOperators)
  {
    $result = [];
    foreach($sourceLandingsOperators as $land) {
      $result[$land->operator_id][] = $land;
    }
    return $result;
  }

  /**
   * @param $operLands
   * @param $converts
   * @return array
   */
  private function getLandsToDelete($operLands, $converts)
  {
    if (count($operLands) == 1) return [];

    $landsWithConvert = [];

    foreach($operLands as $operLand){
      $landsWithConvert[$operLand->landing_id] = isset($converts[$operLand->landing_id]) ? (float)$converts[$operLand->landing_id] : 0;
    }

    // Собираем массив на удаление (т.е. отбираем все landing_id, кроме того у которого максимальный конверт)
    arsort($landsWithConvert);
    reset($landsWithConvert);
    $key = key($landsWithConvert);

    $toDelete = $landsWithConvert;
    unset($toDelete[$key]);

    return array_keys($toDelete);
  }


  /**
   * @inheritdoc
   */
  public function stdout($string)
  {
    $this->event->stdout .= $string;
    return call_user_func_array(array('parent', __FUNCTION__), func_get_args());
  }
}