<?php

namespace mcms\statistic\components;

use mcms\statistic\models\ResellerHoldRule;
use Yii;
use yii\base\Object;
use yii\helpers\ArrayHelper;

/**
 * Class ResellerUnholdDateCalc
 * @package mcms\statistic\components
 */
class ResellerUnholdDateCalc extends Object
{
  /** @var  ResellerHoldRule */
  public $holdRule;
  /**
   * дата попадания в холд
   * @var string
   */
  public $holdDate;
  /**
   * максимальная дата расхолденного холда
   * @var string|null
   */
  public $maxUnholdDate;
  /**
   * минимальная дата холда (нужна если нет [[maxUnholdDate]])
   * @var string|null
   */
  public $minHoldDate;
  /**
   * диапазон следующего расхолда.
   * Это значение вычисляется на основе того что дано.
   * Например ['2017-05-22', '2017-06-11']
   * @var string[]
   */
  public $nextUnholdRange;

  /**
   * следующий расхолд не ранее чем (без учета настройки [[atDay]]).
   * То есть к $nextUnholdRange прибавили $minHoldRange.
   * @var string
   */
  public $nextUnholdDateNotEarly;

  /**
   * итоговое зачение когда расхолдится холд
   * @var string
   */
  public $unholdDate;

  /**
   * @return string
   */
  public function getUnholdDate()
  {
    $this->initNextUnholdRange();
    $this->initNextUnholdDateNotEarly();
    $this->initUnholdDate();

    return $this->unholdDate;
  }

  /**
   * левую границу определяем как последняя дата которая была расхолдена [[maxUnholdDate]] + 1 день.
   * если [[maxUnholdDate]] = null, то левую границу определяем как дату [[minHoldDate]].
   * если [[minHoldDate]] = null тоже, то берем дату текущего холда [[holdDate]]
   *
   * правую границу получаем так:
   * сначала к левой дате прибавляем нужное количество дней до круглой недели|месяца (см. настройку [[unholdRange]])
   * далее прибавляем к полученной дате [[unholdRange]]
   */
  protected function initNextUnholdRange()
  {
    $leftDate = $this->maxUnholdDate
      ? Yii::$app->formatter->asDate($this->maxUnholdDate . ' + 1day', 'php:Y-m-d')
      : ($this->minHoldDate ?: $this->holdDate)
    ;

    if ($this->holdRule->unholdRangeType == ResellerHoldRule::UNHOLD_RANGE_TYPE_MONTH) {
      $currentFirstDayOfMonth = Yii::$app->formatter->asDate($leftDate, 'php:Y-m-01');
      $corrected = $currentFirstDayOfMonth === $leftDate
        ? $currentFirstDayOfMonth
        : Yii::$app->formatter->asDate($currentFirstDayOfMonth . '+1month', 'php:Y-m-01')
      ;

      // $rightDay всегда последний день месяца, ведь это пачка по полным месяцам
      $rightDay = Yii::$app->formatter->asDate($corrected . "+ {$this->holdRule->unholdRange} month -1 day", 'php:Y-m-d');
      // бывает, что дата холда не попала в полученный диапазон. Тогда мы должны прибавить ещё N полных дней/недель/месяцев
      while ($rightDay < $this->holdDate) {
        $firstDayOfNextMonth = Yii::$app->formatter->asDate($rightDay . '+1 day', 'php:Y-m-d');
        $rightDay = Yii::$app->formatter->asDate($firstDayOfNextMonth . "+{$this->holdRule->unholdRange} month -1day", 'php:Y-m-d');
      }

      $this->nextUnholdRange = [$leftDate, $rightDay];
      return;
    }

    if ($this->holdRule->unholdRangeType == ResellerHoldRule::UNHOLD_RANGE_TYPE_WEEK) {
      $corrected = Yii::$app->formatter->asDate("Monday $leftDate", 'php:Y-m-d');
      $rightDay = Yii::$app->formatter->asDate($corrected . "+ {$this->holdRule->unholdRange} week -1 day", 'php:Y-m-d');

      // бывает, что дата холда не попала в полученный диапазон. Тогда мы должны прибавить ещё N полных дней/недель/месяцев
      while ($rightDay < $this->holdDate) {
        $rightDay = Yii::$app->formatter->asDate($rightDay . "+ {$this->holdRule->unholdRange} week", 'php:Y-m-d');
      }

      $this->nextUnholdRange = [$leftDate, $rightDay];
      return;
    }

    $rightDay = Yii::$app->formatter->asDate($leftDate . "+ {$this->holdRule->unholdRange} day", 'php:Y-m-d');

    while ($rightDay < $this->holdDate) {
      // бывает, что дата холда не попала в полученный диапазон. Тогда мы должны прибавить ещё N полных дней/недель/месяцев
      $rightDay = Yii::$app->formatter->asDate($rightDay . "+ {$this->holdRule->unholdRange} day", 'php:Y-m-d');
    }

    $this->nextUnholdRange = [$leftDate, $rightDay];
  }

  /**
   * для начала к правой границе [[nextUnholdRange]] надо прибавить 1 день.
   * В этот день мог бы расхолдиться весь диапазон, если бы стояла настройка минимального холда 0 дней.
   * Но если она не стоит, то надо прибавить к ней [[minHoldRange]]
   */
  protected function initNextUnholdDateNotEarly()
  {
    $rightDate = $this->nextUnholdRange[1];

    $nextRightDate = Yii::$app->formatter->asDate($rightDate . ' + 1day', 'php:Y-m-d');

    $this->nextUnholdDateNotEarly = Yii::$app->formatter->asDate(sprintf(
      '%s +%s %s',
      $nextRightDate,
      $this->holdRule->minHoldRange ?: 0,
      (
        $this->holdRule->minHoldRangeType == ResellerHoldRule::UNHOLD_RANGE_TYPE_DAY
          ? 'days'
          : ($this->holdRule->minHoldRangeType == ResellerHoldRule::UNHOLD_RANGE_TYPE_WEEK ? 'weeks' : 'months')
      )
    ), 'php:Y-m-d');
  }

  /**
   * здесь надо учесть настройку [[atDay]].
   */
  protected function initUnholdDate()
  {
    if (!$this->holdRule->atDay){
      $this->unholdDate = $this->nextUnholdDateNotEarly;
      return;
    }

    if ($this->holdRule->atDayType == ResellerHoldRule::AT_DAY_TYPE_WEEK) {
      $this->unholdDate = Yii::$app->formatter->asDate(sprintf(
        '%s %s',
        $this->getWeekName($this->holdRule->atDay),
        $this->nextUnholdDateNotEarly
      ), 'php:Y-m-d');
      return;
    }

    if ($this->holdRule->atDayType == ResellerHoldRule::AT_DAY_TYPE_MONTH) {
      $currentDayOfMonth = Yii::$app->formatter->asDate($this->nextUnholdDateNotEarly, "php:Y-m-{$this->holdRule->atDay}");

      if ($currentDayOfMonth >= $this->nextUnholdDateNotEarly) {
        $this->unholdDate = $currentDayOfMonth;
        return;
      }

      $this->unholdDate = Yii::$app->formatter->asDate("$currentDayOfMonth + 1 month", 'php:Y-m-d');
      return;
    }
  }

  /**
   * @param $num
   * @return string
   */
  protected function getWeekName($num)
  {
    return ArrayHelper::getValue([
      1 => 'Monday',
      2 => 'Tuesday',
      3 => 'Wednesday',
      4 => 'Thursday',
      5 =>'Friday',
      6 =>'Saturday',
      7 =>'Sunday',
    ], $num);
  }
}