<?php

namespace mcms\common\helpers\curl;

use yii\base\Exception;

class CurlMandatoryUrlException extends Exception
{

    public function getName()
    {
        return 'Url parameter is mandatory';
    }

}