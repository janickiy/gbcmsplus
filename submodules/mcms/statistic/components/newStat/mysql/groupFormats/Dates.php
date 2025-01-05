<?php

namespace mcms\statistic\components\newStat\mysql\groupFormats;

use mcms\common\helpers\Html;
use mcms\statistic\components\newStat\mysql\BaseGroupValuesFormatter;
use Yii;

/**
 * Форматтер для дат
 */
class Dates extends BaseGroupValuesFormatter
{
  /**
   * @inheritdoc
   */
  public function getFormattedValue()
  {
    // Костыть для экспорта. Иначе формат даты d.m не читается как дата
    if (isset($_POST['export_type'])) {
      return Yii::$app->formatter->asDate($this->value);
    }
    return Html::a($this->getFormattedPlainValue(), '#', [
      'data-start' => $this->value,
      'data-end' => $this->value,
      'class' => 'change_date'
    ], [], false);
  }

  /**
   * @inheritdoc
   */
  public function getValue()
  {
    return sprintf('%s - %s', $this->value, $this->value);
  }

  /**
   * @inheritdoc
   */
  public function getFormattedPlainValue()
  {
    return Yii::$app->formatter->asDate($this->value, 'php:d.m');
  }
}
