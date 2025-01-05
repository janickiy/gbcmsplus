<?php

namespace mcms\common\helpers;

use mcms\user\components\BaseConverter;

class StringEncoderDecoder
{
    static $chars = 'abcdef0123456789';

    public static function decode($decoded)
    {
        return BaseConverter::convertToBinary((string)$decoded, self::$chars);
    }

    public static function encode($id)
    {
        return BaseConverter::convertFromBinary((string)$id, self::$chars);
    }
}