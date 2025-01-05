<?php

namespace mcms\statistic\components;

use DateTime;
use Yii;
use yii\base\Object;

/**
 * Работа с периодом дат
 */
class DatePeriod extends Object
{
  const PERIOD_TODAY = 'today';
  const PERIOD_YESTERDAY = 'yesterday';
  const PERIOD_LAST_WEEK = 'week';
  const PERIOD_LAST_MONTH = 'month';
  const PERIOD_OTHER = 'other';

  /**
   * Получить начальную и конечную даты по коду периода
   * @param string $period
   * @return array
   *    - string $from
   *    - string $to
   */
  public static function getPeriodDates($period)
  {
    $dateFrom = $dateTo = null;
    $formatter = Yii::$app->formatter;

    switch ($period) {
      case static::PERIOD_TODAY:
        $dateFrom = $formatter->asDate(time(), 'php:Y-m-d');
        $dateTo = $formatter->asDate(time(), 'php:Y-m-d');
        break;
      case static::PERIOD_YESTERDAY:
        $dateFrom = $formatter->asDate(strtotime('- 1 day'), 'php:Y-m-d');
        $dateTo = $formatter->asDate(strtotime('- 1 day'), 'php:Y-m-d');
        break;
      case static::PERIOD_LAST_WEEK:
        $dateFrom = $formatter->asDate(strtotime('- 6 days'), 'php:Y-m-d');
        $dateTo = $formatter->asDate(time(), 'php:Y-m-d');
        break;
      case static::PERIOD_LAST_MONTH:
        $dateFrom = $formatter->asDate(strtotime('- 1 month'), 'php:Y-m-d');
        $dateTo = $formatter->asDate(time(), 'php:Y-m-d');
        break;
    }

    return ['from' => $dateFrom, 'to' => $dateTo];
  }
}