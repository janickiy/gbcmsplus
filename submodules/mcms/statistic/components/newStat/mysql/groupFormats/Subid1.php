<?php

namespace mcms\statistic\components\newStat\mysql\groupFormats;

use mcms\statistic\components\newStat\mysql\BaseGroupValuesFormatter;

/**
 * Форматтер для subid1
 */
class Subid1 extends BaseGroupValuesFormatter
{
  /**
   * @inheritdoc
   */
  public function getFormattedValue()
  {
    return $this->value;
  }
}
