<?php

namespace mcms\partners\components\mainStat\groupFormats;

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
