<?php

namespace mcms\statistic\components\exception;

use yii\base\Exception;

class UnknownCurrencyException extends Exception
{

  private $currency;

  /**
   * @param mixed $currency
   * @return $this
   */
  public function setCurrency($currency)
  {
    $this->currency = $currency;
    return $this;
  }



  public function getName()
  {
    return 'Currency "' . $this->currency . '" is unknown';
  }

}