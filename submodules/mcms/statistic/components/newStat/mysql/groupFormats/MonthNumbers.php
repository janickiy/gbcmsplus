<?php

namespace mcms\statistic\components\newStat\mysql\groupFormats;

use DateTime;
use mcms\statistic\components\newStat\mysql\BaseGroupValuesFormatter;
use mcms\statistic\components\newStat\mysql\ComplainLink;
use Yii;
use yii\helpers\Html;

/**
 * Форматтер для месяцев
 */
class MonthNumbers extends BaseGroupValuesFormatter
{

  /**
   * @inheritdoc
   */
  public function getFormattedValue()
  {
    return Html::tag('div', $this->getFormattedPlainValue(), ['title' => $this->getTitle()]);
  }

  /**
   * Тайтл для месяца. Диапазон дат, за которые считается статистика с учетом фильтров
   * @return string
   */
  private function getTitle()
  {
    $leftDate = $this->getLeftDate();
    $rightDate = $this->getRightDate();

    return $leftDate === $rightDate
      ? Yii::$app->formatter->asDate($leftDate)
      : Yii::$app->formatter->asDate($leftDate) . ' - ' . Yii::$app->formatter->asDate($rightDate);
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
   * Если диапазон затрагивает несколько лет, возвращаем формат YYYY.m, иначе m
   * @return string
   */
  public function getFormattedPlainValue()
  {
    list(, $monthNumber) = explode('.', $this->value);
    return $this->isMultiYear() ? $this->value : $monthNumber;
  }

  /**
   * Используется ещё в @see ComplainLink
   * @return string
   */
  public function getLeftDate()
  {
    $filterDateFrom = new DateTime($this->formModel->dateFrom);

    $date = explode('.', $this->value);
    $dateMonthBegin = new DateTime($date[0] . '-' . $date[1] . '-01');
    $leftDate = $filterDateFrom > $dateMonthBegin ? $filterDateFrom->format('Y-m-d') : $dateMonthBegin->format('Y-m-d');
    return $leftDate;
  }

  /**
   * Используется ещё в @see ComplainLink
   * @return string
   */
  public function getRightDate()
  {
    $filterDateTo = new DateTime($this->formModel->dateTo);

    $date = explode('.', $this->value);
    $dateMonthBegin = new DateTime($date[0] . '-' . $date[1] . '-01');
    $dateMonthEnd = $dateMonthBegin->modify('last day of');
    $rightDate = $filterDateTo < $dateMonthEnd ? $filterDateTo->format('Y-m-d') : $dateMonthEnd->format('Y-m-d');

    return $rightDate;
  }

  /**
   * @inheritdoc
   */
  public function getValue()
  {
    return sprintf('%s - %s', $this->getLeftDate(), $this->getRightDate());
  }
}
