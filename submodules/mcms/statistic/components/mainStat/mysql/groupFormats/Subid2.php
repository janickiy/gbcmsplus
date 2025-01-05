<?php

namespace mcms\statistic\components\mainStat\mysql\groupFormats;

use mcms\statistic\components\mainStat\mysql\BaseGroupValuesFormatter;

/**
 * Форматтер для метки2
 */
class Subid2 extends BaseGroupValuesFormatter
{

  /**
   * @inheritdoc
   */
  public function getFormattedValue()
  {
    return $this->value;
  }
}
