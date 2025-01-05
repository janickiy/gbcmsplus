<?php

namespace mcms\payments\components\exchanger;

use yii\base\Exception;

class UnknownCurrencyException extends Exception
{
  public $currency;

  public function getName()
  {
    return $this->currency === null
      ? parent::getName()
      : sprintf('Currency "%s" is unknown', $this->currency)
      ;
  }
}