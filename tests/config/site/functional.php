<?php
$_SERVER['SCRIPT_FILENAME'] = FRONTEND_ENTRY_FILE;
$_SERVER['SCRIPT_NAME'] = FRONTEND_ENTRY_URL;

/**
 * Application configuration for site functional tests
 */
return yii\helpers\ArrayHelper::merge(
    require(YII_APP_BASE_PATH . '/common/config/main.php'),
    require(YII_APP_BASE_PATH . '/common/config/main-local.php'),
    require(YII_APP_BASE_PATH . '/site/config/main.php'),
    require(YII_APP_BASE_PATH . '/site/config/main-local.php'),
    require(dirname(__DIR__) . '/config.php'),
    require(dirname(__DIR__) . '/functional.php'),
    require(__DIR__ . '/config.php'),
    [
    ]
);
