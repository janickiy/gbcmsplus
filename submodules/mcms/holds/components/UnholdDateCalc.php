<?php

namespace mcms\holds\components;

use mcms\holds\models\HoldProgramRule;
use Yii;
use yii\base\Object;
use yii\helpers\ArrayHelper;

/**
 * Расчитываем дату расхолда по конечной дате пачки [[packDateTo]] и настройкам
 * [[min_hold_range]], [[min_hold_range_type]], [[at_day]], [[at_day_type]]
 */
class UnholdDateCalc extends Object
{
  public $packDateTo;
  public $minHoldRange;
  public $minHoldRangeType;
  public $atDay;
  public $atDayType;

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
  public function calcUnholdDate()
  {
    $this->initNextUnholdDateNotEarly();
    $this->initUnholdDate();

    return $this->unholdDate;
  }

  /**
   * для начала к правой границе [[nextUnholdRange]] надо прибавить 1 день.
   * В этот день мог бы расхолдиться весь диапазон, если бы стояла настройка минимального холда 0 дней.
   * Но если она не стоит, то надо прибавить к ней [[minHoldRange]]
   */
  protected function initNextUnholdDateNotEarly()
  {
    $nextRightDate = Yii::$app->formatter->asDate($this->packDateTo . ' + 1day', 'php:Y-m-d');

    $this->nextUnholdDateNotEarly = Yii::$app->formatter->asDate(
      strtr('{newRightDate} +{minHoldSize} {holdType}', [
        '{newRightDate}' => $nextRightDate,
        '{minHoldSize}' => $this->minHoldRange ?: 0,
        '{holdType}' => HoldProgramRule::getRangeTypeStr($this->minHoldRangeType)
      ]),
      'php:Y-m-d'
    );
  }

  /**
   * здесь надо учесть настройку [[atDay]].
   */
  protected function initUnholdDate()
  {
    if (!$this->atDay) {
      $this->unholdDate = $this->nextUnholdDateNotEarly;
      return;
    }

    if ((int)$this->atDayType === HoldProgramRule::AT_DAY_TYPE_WEEK) {
      $this->unholdDate = Yii::$app->formatter->asDate(sprintf(
        '%s %s',
        $this->getWeekName($this->atDay),
        $this->nextUnholdDateNotEarly
      ), 'php:Y-m-d');
      return;
    }

    if ((int)$this->atDayType === HoldProgramRule::AT_DAY_TYPE_MONTH) {
      $currentDayOfMonth = Yii::$app->formatter->asDate($this->nextUnholdDateNotEarly, "php:Y-m-{$this->atDay}");

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
      5 => 'Friday',
      6 => 'Saturday',
      7 => 'Sunday',
    ], $num);
  }
}
