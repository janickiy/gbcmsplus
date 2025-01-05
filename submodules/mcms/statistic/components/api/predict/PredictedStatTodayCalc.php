<?php

namespace mcms\statistic\components\api\predict;
use mcms\common\helpers\ArrayHelper;

/**
 * Class PredictedStatTodayCalc
 * @package mcms\statistic\components\api\predict
 */
class PredictedStatTodayCalc
{
  private $sampleDaysCount;
  private $data;
  private $today;
  private $hour;

  private $todaySums = [];
  private $sampleDaySums = [];

  const LIMIT_MAX_RATIO = 3;

  /**
   * PredictedStatTodayCalc constructor.
   * @param array $data
   * @param $sampleDaysCount
   * @param null $today
   * @param null $hour
   */
  public function __construct(array $data, $sampleDaysCount, $today = null, $hour = null)
  {
    $this->today = $today ?: date('Y-m-d');
    $this->hour = $hour ?: date('H');
    $this->sampleDaysCount = (int)$sampleDaysCount;

    $count = count($data);
    $shift = $count - $this->sampleDaysCount - 1;

    $this->data = array_slice($data, $shift);
  }

  /**
   * @return array
   */
  public function getPredictions()
  {
    foreach ($this->data as $group=>$row) {
      $row['group'] = $group;
      $this->addIndexIfNotSet($this->todaySums, $row);

      $this->isTodayRow($row) && $this->sumArrays($this->todaySums, $row, 1);
      !$this->isTodayRow($row) && $this->sumArrays($this->sampleDaySums, $row,  1/$this->sampleDaysCount);
    }

    return $this->calcPredictions();
  }

  /**
   * @param array $array
   * @param array $row
   */
  private function addIndexIfNotSet(array &$array, array $row)
  {
    foreach ($row as $key => $value) {
      if (!isset($array[$key])) {
        $array[$key] = 0;
      }
    }
  }

  /**
   * @param array $array1
   * @param array $array2
   * @param $factor
   */
  private function sumArrays(array &$array1, array $array2, $factor)
  {
    foreach ($array2 as $key => $value) {
      if ($key == 'group') continue;

      if (!key_exists($key, $array1)) $array1[$key] = 0;

      $array1[$key] += (double)$factor * (double)$value;
    }
  }

  /**
   * @param array $row
   * @return bool
   */
  private function isTodayRow(array $row)
  {
    return $row['group'] == $this->today;
  }

  /**
   * @return array
   */
  private function calcPredictions()
  {
    $predictions = [];

    foreach ($this->todaySums as $key => $todayValue) {
      $sampleDayValue = (double)ArrayHelper::getValue($this->sampleDaySums, $key, 0);
      $sampleValue = round((($sampleDayValue/24)*($this->hour + 1)), 2);

      if ($sampleDayValue == 0) {
        $predictions[$key] = $todayValue;
        continue;
      }

      if ($sampleDayValue < $todayValue) {
        $predictions[$key] = round(($todayValue/($this->hour + 1)) * 24, 2);
        continue;
      }
      $predictions[$key] = round(($sampleDayValue - $sampleValue + $todayValue), 2);
    }

    return $predictions;
  }

}
