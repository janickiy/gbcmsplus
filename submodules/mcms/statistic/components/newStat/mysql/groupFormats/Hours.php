<?php

namespace mcms\statistic\components\newStat\mysql\groupFormats;

use mcms\statistic\components\newStat\mysql\BaseGroupValuesFormatter;

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