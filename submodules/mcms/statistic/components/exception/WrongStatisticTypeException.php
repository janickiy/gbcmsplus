<?php

namespace mcms\statistic\components\exception;

use yii\base\Exception;

class WrongStatisticTypeException extends Exception
{
  public function getName()
  {
    return "\"statisticType\" should be one of ['subscription', 'ik', 'sells']";
  }
}