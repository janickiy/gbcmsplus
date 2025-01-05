<?php

namespace mcms\statistic\components\mainStat\mysql\groupFormats;

use mcms\common\helpers\Html;
use mcms\statistic\components\mainStat\mysql\BaseGroupValuesFormatter;
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
    return Html::a(Yii::$app->formatter->asDate($this->value), '#', [
      'data-start' => $this->value,
      'data-end' => $this->value,
      'class' => 'change_date'
    ], [], false);
  }
}
