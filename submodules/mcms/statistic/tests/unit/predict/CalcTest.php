<?php
namespace mcms\statistic\tests\unit\predict;

use mcms\statistic\components\api\predict\PredictedStatTodayCalc;
use mcms\common\codeception\TestCase;

/**
 * Class CalcTest
 * @package mcms\statistic\tests\unit\predict
 */
class CalcTest extends TestCase
{

  const TEST_VALUE = 'testValue';

  /**
   * Текущее: 10
   * Позавчера: 20
   * Вчера: 30
   * текущий час: 3
   * Прогноз на сегодня: 30 - (30/24 * 3 + 1) + 10 = 35
   *
   * Текущее: 20
   * Позавчера: 8
   * Вчера: 40
   * текущий час: 3
   * Прогноз на сегодня: (8 + 40)/2 - (24/24 * 3 + 1) + 20 = 40
   */
  public function testCommon()
  {
    $calc = new PredictedStatTodayCalc([
      '2016-12-13' => [self::TEST_VALUE => 20, 'group' => '2016-12-13'],
      '2016-12-14' => [self::TEST_VALUE => 30, 'group' => '2016-12-14'],
      '2016-12-15' => [self::TEST_VALUE => 10, 'group' => '2016-12-15']
    ], 1, '2016-12-15', 3);

    static::assertEquals(35, $calc->getPredictions()[self::TEST_VALUE]);

    $calc = new PredictedStatTodayCalc([
      '2016-12-13' => [self::TEST_VALUE => 8, 'group' => '2016-12-13'],
      '2016-12-14' => [self::TEST_VALUE => 40, 'group' => '2016-12-14'],
      '2016-12-15' => [self::TEST_VALUE => 20, 'group' => '2016-12-15']
    ], 2, '2016-12-15', 3);

    static::assertEquals(40, $calc->getPredictions()[self::TEST_VALUE]);
  }

  /**
   * В выборке 0
   *
   * Текущее: 10
   * Позавчера: 0
   * Вчера: 0
   * текущий час: 3
   * Прогноз на сегодня: 10
   *
   */
  public function testSampleDayNull()
  {
    $calc = new PredictedStatTodayCalc([
      '2016-12-13' => [self::TEST_VALUE => 0, 'group' => '2016-12-13'],
      '2016-12-14' => [self::TEST_VALUE => 0, 'group' => '2016-12-14'],
      '2016-12-15' => [self::TEST_VALUE => 10, 'group' => '2016-12-15']
    ], 1, '2016-12-15', 3);

    static::assertEquals(10, $calc->getPredictions()[self::TEST_VALUE]);
  }

  /**
   * Сегодня больше чем в выборке
   *
   * Текущее: 40
   * Позавчера: 30
   * Вчера: 20
   * текущий час: 3
   * Прогноз на сегодня: (40/(3+1)) * 24
   *
   */
  public function testTodayMore()
  {
    $calc = new PredictedStatTodayCalc([
      '2016-12-13' => [self::TEST_VALUE => 30, 'group' => '2016-12-13'],
      '2016-12-14' => [self::TEST_VALUE => 20, 'group' => '2016-12-14'],
      '2016-12-15' => [self::TEST_VALUE => 40, 'group' => '2016-12-15']
    ], 1, '2016-12-15', 3);

    static::assertEquals(240, $calc->getPredictions()[self::TEST_VALUE]);
  }

}