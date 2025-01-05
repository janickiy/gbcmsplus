<?php


namespace mcms\common\helpers;

class Currency
{
    private static $map = [
        1 => 'rub',
        2 => 'usd',
        3 => 'eur',
    ];

    public static function getName($currencyId)
    {
        return ArrayHelper::getValue(self::$map, $currencyId);
    }

    public static function getId($currencyName)
    {
        return ArrayHelper::getValue(array_flip(self::$map), $currencyName);
    }
}