<?php

namespace mcms\common\helpers\curl;

use yii\base\Exception;

class CurlInitException extends Exception
{

    public function getName()
    {
        return 'Curl init returned false';
    }

}