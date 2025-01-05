<?php

namespace mcms\common\exceptions;

use yii\base\Exception;

class ModelNotSavedException extends Exception
{
    public function getName()
    {
        return 'Model save error';
    }

} 
