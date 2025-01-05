<?php

namespace mcms\partners\components;

use mcms\common\AdminFormatter;

/**
 */
class PartnerFormatter extends AdminFormatter
{
  /**
   * @var array
   */
  public $icons = [
    'rub' => '₽',
    'usd' => '$',
    'eur' => '€',
  ];
}
