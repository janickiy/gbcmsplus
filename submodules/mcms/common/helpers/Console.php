<?php

namespace mcms\common\helpers;

class Console extends \yii\helpers\Console
{
    public static $interactive = true;

    public static function confirm($message, $default = false)
    {
        if (self::$interactive) {
            return parent::confirm($message, $default);
        } else {
            return $default;
        }
    }
}