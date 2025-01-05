<?php

namespace mcms\common\helpers\curl;


use yii\base\Exception;

class CurlProxyAddressInvalidException extends Exception
{
    public function getName()
    {
        return 'Proxy server ip is invalid';
    }
}