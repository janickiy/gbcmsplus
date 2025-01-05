<?php

namespace mcms\partners\components\mainStat\groupFormats;

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
    $formatter = Yii::$app->formatter;
    return Html::a(Yii::$app->formatter->asDate($this->value), '#', [
      'data-start' => $formatter->asPartnerDate($this->value),
      'data-end' => $formatter->asPartnerDate($this->value),
      'class' => 'change_date'
    ]);
  }
}
