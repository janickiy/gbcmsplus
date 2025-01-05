<?php

namespace mcms\statistic\components\mainStat\mysql\groupFormats;

use mcms\statistic\components\mainStat\mysql\BaseGroupValuesFormatter;

/**
 * Форматтер для часов
 */
class Hours extends BaseGroupValuesFormatter
{

  /**
   * @inheritdoc
   */
  public function getFormattedValue()
  {
    return $this->value;
  }
}
