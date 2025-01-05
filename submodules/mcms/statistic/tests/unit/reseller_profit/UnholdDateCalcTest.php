<?php
namespace mcms\statistic\tests\unit\reseller_profit;

use mcms\common\codeception\TestCase;
use mcms\statistic\components\ResellerUnholdDateCalc;
use mcms\statistic\models\ResellerHoldRule;

/**
 * Class UnholdDateCalcTest
 * @package mcms\statistic\tests\unit\reseller_profit
 */
class UnholdDateCalcTest extends TestCase
{

  public function testUnholdDateCorrect()
  {
    foreach ($this->getTestCaseData() as $testData) {
      $calc = new ResellerUnholdDateCalc([
        'holdRule' => new ResellerHoldRule([
          'unholdRange' => $testData['unholdRange'],
          'unholdRangeType' => $testData['unholdRangeType'],
          'minHoldRange' => $testData['minHoldRange'],
          'minHoldRangeType' => $testData['minHoldRangeType'],
          'atDay' => $testData['atDay'],
          'atDayType' => $testData['atDayType'],
        ]),
        'holdDate' => $testData['holdDate'],
        'maxUnholdDate' => $testData['maxUnholdDate'],
        'minHoldDate' => $testData['minHoldDate'],
      ]);
      $calc->getUnholdDate();
      $this->assertEquals($testData['nextUnholdRange'][0], $calc->nextUnholdRange[0], 'check nextUnholdRange left date');
      $this->assertEquals($testData['nextUnholdRange'][1], $calc->nextUnholdRange[1], 'check nextUnholdRange right date');
      $this->assertEquals($testData['nextUnholdNotEarly'], $calc->nextUnholdDateNotEarly, 'check nextUnholdNotEarly');
      $this->assertEquals($testData['unholdDate'], $calc->unholdDate, 'check unholdDate');
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
        'unholdRangeType' => 2,
        'minHoldRange' => 1,
        'minHoldRangeType' => 2,
        'atDay' => null,
        'atDayType' => null,
        'holdDate' => '2017-06-01',
        'maxUnholdDate' => '2017-05-21',
        'minHoldDate' => null,
        // расчетные:
        'nextUnholdRange' => ['2017-05-22', '2017-06-11'],
        'nextUnholdNotEarly' => '2017-06-19',
        'unholdDate' => '2017-06-19',
      ],
      [
        'unholdRange' => 3,
        'unholdRangeType' => 2,
        'minHoldRange' => 1,
        'minHoldRangeType' => 2,
        'atDay' => null,
        'atDayType' => null,
        'holdDate' => '2017-06-01',
        'maxUnholdDate' => null,
        'minHoldDate' => '2017-05-31',
        // расчетные:
        'nextUnholdRange' => ['2017-05-31', '2017-06-25'],
        'nextUnholdNotEarly' => '2017-07-03',
        'unholdDate' => '2017-07-03',
      ],
      [
        'unholdRange' => 10,
        'unholdRangeType' => 1,
        'minHoldRange' => 5,
        'minHoldRangeType' => 1,
        'atDay' => 4,
        'atDayType' => 1,
        'holdDate' => '2017-06-01',
        'maxUnholdDate' => null,
        'minHoldDate' => '2017-05-31',
        // расчетные:
        'nextUnholdRange' => ['2017-05-31', '2017-06-10'],
        'nextUnholdNotEarly' => '2017-06-16',
        'unholdDate' => '2017-06-22',
      ],
      [
        'unholdRange' => 2,
        'unholdRangeType' => 3,
        'minHoldRange' => 1,
        'minHoldRangeType' => 3,
        'atDay' => 10,
        'atDayType' => 2,
        'holdDate' => '2017-06-12',
        'maxUnholdDate' => '2017-05-31',
        'minHoldDate' => null,
        // расчетные:
        'nextUnholdRange' => ['2017-06-01', '2017-07-31'],
        'nextUnholdNotEarly' => '2017-09-01',
        'unholdDate' => '2017-09-10',
      ],

      [
        'unholdRange' => 1,
        'unholdRangeType' => 3,
        'minHoldRange' => 12,
        'minHoldRangeType' => 1,
        'atDay' => 20,
        'atDayType' => 2,
        'holdDate' => '2017-07-03',
        'maxUnholdDate' => null,
        'minHoldDate' => null,
        // расчетные:
        'nextUnholdRange' => ['2017-07-03', '2017-08-31'],
        'nextUnholdNotEarly' => '2017-09-13',
        'unholdDate' => '2017-09-20',
      ],

      [
        'unholdRange' => 1,
        'unholdRangeType' => 3,
        'minHoldRange' => 12,
        'minHoldRangeType' => 1,
        'atDay' => 20,
        'atDayType' => 2,
        'holdDate' => '2017-07-03',
        'maxUnholdDate' => null,
        'minHoldDate' => '2017-06-23',
        // расчетные:
        'nextUnholdRange' => ['2017-06-23', '2017-07-31'],
        'nextUnholdNotEarly' => '2017-08-13',
        'unholdDate' => '2017-08-20',
      ],
      [
        'unholdRange' => 32,
        'unholdRangeType' => 1,
        'minHoldRange' => 0,
        'minHoldRangeType' => 0,
        'atDay' => null,
        'atDayType' => null,
        'holdDate' => '2017-08-02',
        'maxUnholdDate' => '2017-06-22',
        'minHoldDate' => null,
        // расчетные:
        'nextUnholdRange' => ['2017-06-23', '2017-08-26'],
        'nextUnholdNotEarly' => '2017-08-27',
        'unholdDate' => '2017-08-27',
      ],
      [
        'unholdRange' => 1,
        'unholdRangeType' => 2,
        'minHoldRange' => 0,
        'minHoldRangeType' => 0,
        'atDay' => null,
        'atDayType' => null,
        'holdDate' => '2017-08-02',
        'maxUnholdDate' => '2017-06-22',
        'minHoldDate' => null,
        // расчетные:
        'nextUnholdRange' => ['2017-06-23', '2017-08-06'],
        'nextUnholdNotEarly' => '2017-08-07',
        'unholdDate' => '2017-08-07',
      ],
      [
        'unholdRange' => 1,
        'unholdRangeType' => 3,
        'minHoldRange' => 0,
        'minHoldRangeType' => 0,
        'atDay' => null,
        'atDayType' => null,
        'holdDate' => '2017-08-02',
        'maxUnholdDate' => '2017-06-22',
        'minHoldDate' => null,
        // расчетные:
        'nextUnholdRange' => ['2017-06-23', '2017-08-31'],
        'nextUnholdNotEarly' => '2017-09-01',
        'unholdDate' => '2017-09-01',
      ],

      [
        'unholdRange' => 1,
        'unholdRangeType' => 3,
        'minHoldRange' => 4,
        'minHoldRangeType' => 3,
        'atDay' => 15,
        'atDayType' => 2,
        'holdDate' => '2017-12-01',
        'maxUnholdDate' => '2017-10-01',
        'minHoldDate' => null,
        // расчетные:
        'nextUnholdRange' => ['2017-10-02', '2017-12-31'],
        'nextUnholdNotEarly' => '2018-05-01',
        'unholdDate' => '2018-05-15',
      ],
    ];
  }

}