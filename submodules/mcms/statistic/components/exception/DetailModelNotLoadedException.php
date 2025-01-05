<?php

namespace mcms\statistic\components\exception;

use yii\base\Exception;

class DetailModelNotLoadedException extends Exception
{
  public function getName()
  {
    return 'Detail model not loaded';
  }

}