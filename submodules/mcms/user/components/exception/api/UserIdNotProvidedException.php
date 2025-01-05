<?php

namespace mcms\user\components\exception\api;

use yii\base\Exception;

class UserIdNotProvidedException extends Exception
{
    public function getName()
    {
        return 'userId should be in passed array';
    }
}