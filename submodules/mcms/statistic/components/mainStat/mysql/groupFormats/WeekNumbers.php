<?php

namespace mcms\statistic\components\mainStat\mysql\groupFormats;

use DateTime;
use mcms\common\helpers\Html;
use mcms\statistic\components\mainStat\mysql\BaseGroupValuesFormatter;
use Yii;

/**
 * Форматтер для недель
 */
class WeekNumbers extends BaseGroupValuesFormatter
{
  /**
   * @inheritdoc
   */
  public function getFormattedValue()
  {
    return Html::tag('div', $this->getFormat(), ['title' => $this->getTitle()]);
  }

  /**
   * Тайтл для месяца. Диапазон дат, за которые считается статистика с учетом фильтров
   * @return string
   */
  protected function getTitle()
  {
    // Дата начала и конца текущей недели
    list($leftDate, $rightDate) = $this->getWeekPeriod();

    $leftDateString = $leftDate->format('Y-m-d');
    $rightDateString = $rightDate->format('Y-m-d');

    return $leftDateString === $rightDateString
      ? Yii::$app->formatter->asDate($leftDateString)
      : Yii::$app->formatter->asDate($leftDateString) . ' - ' . Yii::$app->formatter->asDate($rightDateString);
  }

  /**
   * Затрагивает ли диапазон несколько лет
   * @return bool
   */
  protected function isMultiYear()
  {
    // Диапазон дат в фильтре
    $filterDateFrom = new DateTime($this->formModel->dateFrom);
    $filterDateTo = new DateTime($this->formModel->dateTo);

    return $filterDateFrom->format('Y') !== $filterDateTo->format('Y');
  }

  /**
   * Если диапазон затрагивает несколько лет, возвращаем формат YYYY.W, иначе W
   * @return string
   */
  protected function getFormat()
  {
    list(, $weekNumber) = explode('.', $this->value);
    return $this->isMultiYear() ? $this->value : $weekNumber;
  }

  /**
   * Определить даты начала и конца недели при группировке в гриде статы
   * Используется ещё в @see ComplainLink
   * @return DateTime[]
   */
  public function getWeekPeriod()
  {
    /** @var string $year */
    /** @var int $weekNumber Номер недели без ведущего нуля */
    list($year, $weekNumber) = explode('.', $this->value);

    $dateTime = new DateTime("1 January $year");
    $days = 7 * ($weekNumber - 2) + 1 - $dateTime->format('w'); // <-- если с этой херней опять будут проблемы,
    // то давайте по-другому доставать инфу из мускуля (в виде первого дня недели чтоб дата возвращалась)
    // подглядеть можно в МП, вроде там делал аналогично
    $dateTime->modify("+ {$days} days");
    $date = $dateTime->format('Y-m-d');

    /** Определение дат начала и конца недели */
    // Определение начала недели
    $dateWeekBegin = new DateTime($date);
    // Если день выданный БД не понедельник, то передвигаем на понедельник
    if ($dateWeekBegin->format('D') !== 'Mon') {
      $dateWeekBegin->modify('last monday');
    }

    // Определение окончания недели
    $dateWeekEnd = new DateTime($date);
    // Если день выданный БД не воскресенье, то передвигаем на воскресенье
    if ($dateWeekEnd->format('D') !== 'Sun') {
      $dateWeekEnd->modify('sunday');
    }

    return $this->correctWeekPeriod($dateWeekBegin, $dateWeekEnd, $weekNumber);
  }

  /**
   * Корректировка начальной/конечной даты недели для отображения в гриде
   * @param DateTime $dateWeekBegin Начало недели/месяца
   * @param DateTime $dateWeekEnd Конец недели/месяца
   * @param int $weekOrMonthNumber Номер недели/месяца
   * @return array
   */
  protected function correctWeekPeriod($dateWeekBegin, $dateWeekEnd, $weekOrMonthNumber)
  {
    /**
     * Корректировка недели распологающегося одновременно в двух годах так как это делает MySQL при группировке.
     * Месяц/неделя разбитый на два года считается разными периодами
     */
    // Если месяц/неделя первый в году, начало месяц/неделя в одном году, а конец в другом,
    // ограничиваем начало месяц/неделя началом текущего года
    $crossYears = $dateWeekBegin->format('Y') !== $dateWeekEnd->format('Y');
    if ($weekOrMonthNumber === 1 && $crossYears) {
      $dateWeekBegin->modify('next year first day of january');
    }

    // Если месяц/неделя последняя в году, начало месяца/недели в одном году, а конец в другом,
    // ограничиваем конец месяца/недели концом текущего года
    // TRICKY Если убрать $crossYears, то все месяцы/недели кроме первого будут считаться последними
    if ($weekOrMonthNumber !== 1 && $crossYears) {
      $dateWeekEnd->modify('previous year last day of december');
    }

    /** Корректировка в зависимости от дат указанных в фильтре */
    $filterDateBegin = new DateTime($this->formModel->dateFrom);
    $filterDateEnd = new DateTime($this->formModel->dateTo);
    if ($dateWeekBegin < $filterDateBegin) {
      $dateWeekBegin = $filterDateBegin;
    }
    if ($dateWeekEnd > $filterDateEnd) {
      $dateWeekEnd = $filterDateEnd;
    }

    return [$dateWeekBegin, $dateWeekEnd];
  }
}
