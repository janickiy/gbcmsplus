<?php

namespace mcms\statistic\components\cron;

use mcms\statistic\components\cron\handlers\BalanceByUserAndDate;
use Yii;
use yii\base\Object;

/**
 * Class CronParams
 * @package mcms\statistic\components\cron
 *
 * @property string $dateQuery
 * @property string $fromDate
 * @property string $fromTime
 */
class CronParams extends Object
{
  /** @var int timestamp */
  private $fromTime = 0;

  /** @var string дата формата SQL */
  private $_fromDate;

  /** @var string SQL условие н-р: "(`date` >= '2016-04-07' AND `hour` >= 13)" */
  private $dateQuery;

  /**
   * @var bool разрешить ли старый обработчик
   * @see BalanceByUserAndDate
   */
  public $allowBalanceByUserAndDate;

  /**
   * @return string
   */
  public function getFromDate()
  {
    if ($this->_fromDate) return $this->_fromDate;

    return $this->_fromDate = self::getAsDate($this->fromTime, 'Y-m-d');
  }

  /**
   * @return int timestamp
   */
  public function getFromTime()
  {
    return $this->fromTime;
  }

  /**
   * @param $value
   */
  protected function setFromTime($value)
  {
    $this->fromTime = $value;
  }

  /**
   * @return string
   */
  public function getDateQuery($alias = null, $cache = true)
  {
    if ($alias) {
      $alias = $alias.'.';
    }

    if ($cache && $this->dateQuery) {
      return $this->dateQuery;
    }

    $hour = (int)self::getAsDate($this->fromTime, 'G');

    if ($this->fromDate === self::getAsDate('today', 'Y-m-d')) {
      $dateQuery = "($alias`date` >= '{$this->fromDate}' AND $alias`hour` >= $hour)";
      if ($cache) {
        return $this->dateQuery = $dateQuery;
      }
      return $dateQuery;
    }

    $nextDate = self::getAsDate(strtotime('+1 day', $this->fromTime), 'Y-m-d');
    $dateQuery = "(($alias`date` >= '{$this->fromDate}' AND $alias`hour` >= $hour) OR ($alias`date` >= '$nextDate' AND $alias`hour` >= 0))";
    if ($cache) {
      return $this->dateQuery = $dateQuery;
    }

    return $dateQuery;
  }

  /**
   * @param $value
   * @param $phpFormat
   * @return string
   */
  private static function getAsDate($value, $phpFormat)
  {
    return Yii::$app->formatter->asDate($value, 'php:' . $phpFormat);
  }
}
