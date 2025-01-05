<?php

namespace mcms\payments\components\exchanger;

use yii\base\Exception;

class ExchangerCurlException extends Exception
{
  public $message;

  public function getName()
  {
    return $this->message ? : parent::getName();
  }


}