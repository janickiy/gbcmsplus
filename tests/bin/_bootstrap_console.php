<?php
/**
 * Иначе миграции падают со статусом=0, а надо статус=1 иначе CI не знает что билд упал
 * А статус=1 можно получить только если у нас YII_ENV_TEST === false (так зашито в логике yii)
 * Данный файл (с YII_ENV_DEV) используется только для миграций. Для юнитов всё по-обычному и
 * там YII_ENV_TEST === true, так как для юнитов подключается другой бутстрап (tests/bin/_bootstrap.php)
 */

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

// fcgi doesn't have STDIN and STDOUT defined by default
defined('STDIN') or define('STDIN', fopen('php://stdin', 'r'));
defined('STDOUT') or define('STDOUT', fopen('php://stdout', 'w'));

defined('YII_APP_BASE_PATH') or define('YII_APP_BASE_PATH', dirname(dirname(__DIR__)));

require_once(YII_APP_BASE_PATH . '/vendor/autoload.php');
require_once(YII_APP_BASE_PATH . '/submodules/mcms/common/Yii.php');
require_once(YII_APP_BASE_PATH . '/common/config/bootstrap.php');

Yii::setAlias('@tests', dirname(dirname(__DIR__)));