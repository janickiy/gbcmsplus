<?php

namespace mcms\statistic\components\exception;

use yii\base\Exception;

class InvalidStatisticTypeException extends Exception
{
  public function getName()
  {
    return '"statisticType" should be passed into params array';
  }
}