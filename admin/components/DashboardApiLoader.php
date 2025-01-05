<?php

namespace admin\components;

use mcms\statistic\components\api\Dashboard;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class DashboardApiLoader
 * @package admin\components
 */
class DashboardApiLoader
{
    private static $api;

    /**
     * Кеширование API дашборда в свойство и получение из кеша
     * @param $startDate
     * @param null $endDate
     * @param array $countries
     * @param array $operators
     * @param array $users
     * @return Dashboard
     */
    public static function getApi($startDate, $endDate = null, $countries = [], $operators = [], $users = [])
    {
        $key = self::getKey(
            $startDate,
            $endDate,
            $countries,
            $operators,
            $users
        );

        return ArrayHelper::getValue(self::$api, $key) ?: self::$api[$key] = Yii::$app->getModule('statistic')->api('dashboard', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'countries' => $countries,
            'operators' => $operators,
            'users' => $users,
        ]);
    }

    /**
     * @param $startDate
     * @param null $endDate
     * @param array $countries
     * @param array $operators
     * @param array $users
     * @return string
     */
    private static function getKey($startDate, $endDate = null, $countries = [], $operators = [], $users = [])
    {
        $cacheCountries = is_array($countries) ? implode(',', $countries) : $countries;
        $cacheOperators = is_array($operators) ? implode(',', $operators) : $operators;
        $cacheUsers = is_array($users) ? implode(',', $users) : $users;

        return md5(implode(':', [$startDate, $endDate, $cacheCountries, $cacheOperators, $cacheUsers]));
    }
}