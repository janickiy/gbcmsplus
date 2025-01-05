<?php

namespace common\components;

use Yii;

class ClickhouseHelper
{
    public static function getClickhouseMysqlConnectionString($tableName)
    {
        $clickhouseMysqlSettings = Yii::$app->params['clickhouseMysql'];

        return strtr("mysql(':host', ':db', ':table', ':user', ':password')", [
            ':host' => $clickhouseMysqlSettings['host'],
            ':db' => $clickhouseMysqlSettings['db'],
            ':table' => $tableName,
            ':user' => $clickhouseMysqlSettings['user'],
            ':password' => $clickhouseMysqlSettings['password'],
        ]);
    }
}
