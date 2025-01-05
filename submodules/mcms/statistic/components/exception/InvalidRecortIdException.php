<?php

namespace mcms\statistic\components\exception;

use yii\base\Exception;

class InvalidRecordIdException extends Exception
{
  public function getName()
  {
    return '"recordId" should be passed into params array';
  }

}