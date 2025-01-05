<?php
namespace mcms\holds\tests\unit\rule_unhold_plan;

use mcms\common\codeception\TestCase;
use mcms\holds\components\RuleUnholdPlanner;
use mcms\holds\models\HoldProgramRule;
use Yii;
use yii\db\Query;

/**
 * проверяется работа класса @see RuleUnholdPlanner
 */
class RuleUnholdPlannerTest extends TestCase
{

  protected function setUp()
  {
    parent::setUp();
    Yii::$app->db->createCommand('SET FOREIGN_KEY_CHECKS=0;')->execute();
    Yii::$app->db->createCommand('TRUNCATE TABLE hold_program_rules')->execute();
    Yii::$app->db->createCommand('TRUNCATE TABLE hold_programs')->execute();
    Yii::$app->db->createCommand('TRUNCATE TABLE user_payment_settings')->execute();
    Yii::$app->db->createCommand('SET FOREIGN_KEY_CHECKS=1;')->execute();
  }

  public function testKeyDateValidations()
  {
    $rule = new HoldProgramRule([
      'unhold_range_type' => HoldProgramRule::UNHOLD_RANGE_TYPE_WEEK,
      'unhold_range' => 2,
      'min_hold_range' => 0,
      'min_hold_range_type' => 0,
      'key_date' => '2018-04-26',
    ]);

    $planner = new RuleUnholdPlanner(['rule' => $rule]);

    $this->assertFalse($planner->run());

    $rule = new HoldProgramRule([
      'unhold_range_type' => HoldProgramRule::UNHOLD_RANGE_TYPE_MONTH,
      'unhold_range' => 2,
      'min_hold_range' => 0,
      'min_hold_range_type' => 0,
      'key_date' => '2018-04-26',
    ]);

    $planner = new RuleUnholdPlanner(['rule' => $rule]);

    $this->assertFalse($planner->run());
  }

  public function testUnholdDateEqualsHoldDate()
  {
    foreach ($this->getTestCaseData() as $testCase) {
      Yii::$app->db->createCommand('TRUNCATE TABLE ' . RuleUnholdPlanner::tableName())->execute();
      $rule = new HoldProgramRule([
        'id' => 1,
        'unhold_range' => $testCase['unholdRange'],
        'unhold_range_type' => $testCase['unholdRangeType'],
        'min_hold_range' => $testCase['minHoldRange'],
        'min_hold_range_type' => $testCase['minHoldRangeType'],
        'at_day' => $testCase['atDay'],
        'at_day_type' => $testCase['atDayType'],
        'key_date' => $testCase['keyDate'],
      ]);

      $countPacks = 5 * $testCase['unholdRange']; // нарисует по ~5 пачек влево и вправо
      $packType = HoldProgramRule::getRangeTypeStr($testCase['unholdRangeType']);
      $planFrom = Yii::$app->formatter->asDate("{$testCase['keyDate']} - $countPacks $packType", 'php:Y-m-d');
      $planTo = Yii::$app->formatter->asDate("{$testCase['keyDate']} + $countPacks $packType", 'php:Y-m-d');

      $planner = new RuleUnholdPlanner(['rule' => $rule, 'dateFrom' => $planFrom, 'dateTo' => $planTo]);
      Yii::$app->db->createCommand('SET FOREIGN_KEY_CHECKS=0;')->execute(); // иначе FK заставляет создавать правило в БД :(
      $planner->run();
      Yii::$app->db->createCommand('SET FOREIGN_KEY_CHECKS=1;')->execute();

      $keyRow = (new Query())->select(['*'])->from(RuleUnholdPlanner::tableName())
        ->andWhere(['rule_id' => 1])
        ->andWhere(['<=', 'date_from', $testCase['keyDate']])
        ->andWhere(['>=', 'date_to', $testCase['keyDate']])
        ->one();

      $this->assertNotFalse($keyRow, 'Не найдена строка для ключевой даты');

      $this->assertEquals($testCase['keyPackDateTo'], $keyRow['date_to'], 'Дата окончания пачки неправильно расчитана');
      $this->assertEquals($testCase['keyPackUnholdDate'], $keyRow['unhold_date'], 'Дата расхолда для пачки неправильно расчитана');

      $leftRow = (new Query())->select(['*'])->from(RuleUnholdPlanner::tableName())
        ->andWhere(['rule_id' => 1])
        ->andWhere(['<=', 'date_from', $planFrom])
        ->andWhere(['>=', 'date_to', $planFrom])
        ->exists();

      $this->assertNotFalse($leftRow, 'Не найдена строка для крайней левой даты в графике');

      $rightRow = (new Query())->select(['*'])->from(RuleUnholdPlanner::tableName())
        ->andWhere(['rule_id' => 1])
        ->andWhere(['<=', 'date_from', $planTo])
        ->andWhere(['>=', 'date_to', $planTo])
        ->one();

      $this->assertNotFalse($rightRow, 'Не найдена строка для крайней правой даты в графике');
    }
  }

  /**
   * @return array
   */
  protected function getTestCaseData()
  {
    return [
      [
        'unholdRange' => 3,
        'unholdRangeType' => HoldProgramRule::UNHOLD_RANGE_TYPE_WEEK,
        'minHoldRange' => 1,
        'minHoldRangeType' => HoldProgramRule::UNHOLD_RANGE_TYPE_WEEK,
        'atDay' => null,
        'atDayType' => null,
        'keyDate' => '2017-05-22',
        // расчетные:
        'keyPackDateTo' => '2017-06-11',
        'keyPackUnholdDate' => '2017-06-19',
      ],
      [
        'unholdRange' => 3,
        'unholdRangeType' => HoldProgramRule::UNHOLD_RANGE_TYPE_WEEK,
        'minHoldRange' => 1,
        'minHoldRangeType' => HoldProgramRule::UNHOLD_RANGE_TYPE_WEEK,
        'atDay' => null,
        'atDayType' => null,
        'keyDate' => '2017-06-05',
        // расчетные:
        'keyPackDateTo' => '2017-06-25',
        'keyPackUnholdDate' => '2017-07-03',
      ],
      [
        'unholdRange' => 10,
        'unholdRangeType' => HoldProgramRule::UNHOLD_RANGE_TYPE_DAY,
        'minHoldRange' => 5,
        'minHoldRangeType' => HoldProgramRule::UNHOLD_RANGE_TYPE_DAY,
        'atDay' => 4,
        'atDayType' => HoldProgramRule::AT_DAY_TYPE_WEEK,
        'keyDate' => '2017-05-31',
        // расчетные:
        'keyPackDateTo' => '2017-06-09',
        'keyPackUnholdDate' => '2017-06-15',
      ],
      [
        'unholdRange' => 2,
        'unholdRangeType' => HoldProgramRule::UNHOLD_RANGE_TYPE_MONTH,
        'minHoldRange' => 1,
        'minHoldRangeType' => HoldProgramRule::UNHOLD_RANGE_TYPE_MONTH,
        'atDay' => 10,
        'atDayType' => HoldProgramRule::AT_DAY_TYPE_MONTH,
        'keyDate' => '2017-06-01',
        // расчетные:
        'keyPackDateTo' => '2017-07-31',
        'keyPackUnholdDate' => '2017-09-10',
      ],
      [
        'unholdRange' => 1,
        'unholdRangeType' => HoldProgramRule::UNHOLD_RANGE_TYPE_MONTH,
        'minHoldRange' => 12,
        'minHoldRangeType' => HoldProgramRule::UNHOLD_RANGE_TYPE_DAY,
        'atDay' => 20,
        'atDayType' => HoldProgramRule::AT_DAY_TYPE_MONTH,
        'keyDate' => '2017-08-01',
        // расчетные:
        'keyPackDateTo' => '2017-08-31',
        'keyPackUnholdDate' => '2017-09-20',
      ],
      [
        'unholdRange' => 1,
        'unholdRangeType' => HoldProgramRule::UNHOLD_RANGE_TYPE_MONTH,
        'minHoldRange' => 12,
        'minHoldRangeType' => HoldProgramRule::UNHOLD_RANGE_TYPE_DAY,
        'atDay' => 20,
        'atDayType' => HoldProgramRule::AT_DAY_TYPE_MONTH,
        'keyDate' => '2017-07-01',
        // расчетные:
        'keyPackDateTo' => '2017-07-31',
        'keyPackUnholdDate' => '2017-08-20',
      ],
      [
        'unholdRange' => 32,
        'unholdRangeType' => HoldProgramRule::UNHOLD_RANGE_TYPE_DAY,
        'minHoldRange' => 0,
        'minHoldRangeType' => 0,
        'atDay' => null,
        'atDayType' => null,
        'keyDate' => '2017-06-23',
        // расчетные:
        'keyPackDateTo' => '2017-07-24',
        'keyPackUnholdDate' => '2017-07-25',
      ],
      [
        'unholdRange' => 1,
        'unholdRangeType' => HoldProgramRule::UNHOLD_RANGE_TYPE_WEEK,
        'minHoldRange' => 0,
        'minHoldRangeType' => 0,
        'atDay' => null,
        'atDayType' => null,
        'keyDate' => '2017-06-26',
        // расчетные:
        'keyPackDateTo' => '2017-07-02',
        'keyPackUnholdDate' => '2017-07-03',
      ],
      [
        'unholdRange' => 1,
        'unholdRangeType' => HoldProgramRule::UNHOLD_RANGE_TYPE_MONTH,
        'minHoldRange' => 0,
        'minHoldRangeType' => 0,
        'atDay' => null,
        'atDayType' => null,
        'keyDate' => '2017-07-01',
        // расчетные:
        'keyPackDateTo' => '2017-07-31',
        'keyPackUnholdDate' => '2017-08-01',
      ],
      [
        'unholdRange' => 1,
        'unholdRangeType' => HoldProgramRule::UNHOLD_RANGE_TYPE_MONTH,
        'minHoldRange' => 4,
        'minHoldRangeType' => HoldProgramRule::UNHOLD_RANGE_TYPE_MONTH,
        'atDay' => 15,
        'atDayType' => HoldProgramRule::AT_DAY_TYPE_MONTH,
        'keyDate' => '2017-11-01',
        // расчетные:
        'keyPackDateTo' => '2017-11-30',
        'keyPackUnholdDate' => '2018-04-15',
      ],
    ];
  }

}