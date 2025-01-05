<?php

namespace mcms\promo\components\exception;

use yii\base\Exception;

class SourceOperatorLandingNotSavedException extends Exception
{
    public function getName()
  {
        return 'SourceOperatorLanding model save error';
  }

} 
