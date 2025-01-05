<?php

use rgk\settings\components\SettingsManager;
use mcms\common\event\EventsBootstrap;
use rgk\exchange\components\ExternalCurrenciesProvider;
use rgk\exchange\components\fetcher\GeoApiFetcher;
use rgk\exchange\components\provider\SimpleCoursesProvider;

return [
    'name' => 'Yii2 Project',
    'language' => 'ru',
    'sourceLanguage' => 'en-SOURCE',
    'timeZone' => 'Europe/Moscow',
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'runtimePath' => dirname(dirname(__DIR__)) . '/runtime',

    //При использовании asset-packagist bower ассеты пришутся в директорию vendor/bower-asset, а yii2 ождает установки
    //bower пакетов в директорию vendor/bower. Для этого используем алисы
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],

    // автолоад модуль менеджера - далее он подключает все что нужно
    'bootstrap' => [
        'modmanager', EventsBootstrap::class
    ],

    'components' => [

        'session' => [
            'class' => 'yii\web\CacheSession'
        ],

        // настройка url + настройка в .htaccess
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'suffix' => '/', // все урл заканчиваются на /
            'rules' => [
                'api/v1/<module:[\w-]+>/<controller:[\w-]+>/<action:[\w-]+>' => '<module>/apiv1/<controller>/<action>',
                [
                    'class' => 'common\components\RestUrlRule',
                    'module' => 'api',
                    'version' => 'v2',
                ]
            ]
        ],

        'exchange' => [
            'class' => ExternalCurrenciesProvider::class,
            'coursesProvider' => [
                'class' => SimpleCoursesProvider::class,
                'exchanger' => GeoApiFetcher::class
            ]
        ],

        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
        ],

        'user' => [
            'class' => 'mcms\common\web\User',
            'identityClass' => 'mcms\user\models\User',
            'enableAutoLogin' => true,
            'loginUrl' => ['user/login'],
        ],
        'settingsBuilder' => [
            'class' => 'rgk\settings\components\SettingsBuilder',
        ],
        'settingsManager' => [
            'class' => SettingsManager::class,
            'useCache' => true,
        ],

        'authManager' => [
            'class' => 'mcms\common\rbac\DbManager',
            'defaultRoles' => ['guest'],
            'cache' => 'cache', // включить кеширование
        ],

        'i18n' => [
            'class' => 'mcms\common\translate\I18N',
            'translations' => [
                'app.*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@app/messages'
                ],
                'commonMsg.*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@mcms/common/messages'
                ],
                'kvgrid' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@vendor/kartik-v/yii2-grid/messages'
                ],
            ],
        ],

        'formatter' => [
            'class' => \mcms\common\AdminFormatter::class,
            'dateFormat' => 'php:d.m.Y',
            'datetimeFormat' => 'php:d.m.Y H:i:s',
            'timeFormat' => 'php:H:i:s',
            'thousandSeparator' => ' ',
            'decimalSeparator' => ',',
            'defaultTimeZone' => 'Europe/Moscow',
//    'locale' => 'ru_RU', TRICKY Если расскоментировать, то форматтер будет работать на неправильном языке.
//    Например в англ версии вместо "not set" будет отображать "не заполнено"
        ],

        'exportFormatter' => [
            'class' => \mcms\common\AdminFormatter::class,
            'dateFormat' => 'php:d.m.Y',
            'datetimeFormat' => 'php:d.m.Y H:i:s',
            'timeFormat' => 'php:H:i:s',
            'thousandSeparator' => '',
            'decimalSeparator' => ',',
            'defaultTimeZone' => 'Europe/Moscow',
        ],

        'paysystemIcons' => require(__DIR__ . '/paysystem-icons.php'),

        'assetManager' => [
            'hashCallback' => function ($path) {
                $mostRecentFileMTime = 0;
                $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::CHILD_FIRST);
                foreach ($iterator as $fileinfo) {
                    if ($fileinfo->isFile() && $fileinfo->getMTime() > $mostRecentFileMTime) {
                        $mostRecentFileMTime = $fileinfo->getMTime();
                    }
                }
                $path = (is_file($path) ? dirname($path) : $path) . $mostRecentFileMTime;
                return sprintf('%x', crc32($path . Yii::getVersion()));
            },
        ],

        'view' => [
            'class' => 'mcms\common\web\View'
        ],
        'mgmpClient' => [
            'class' => \mcms\common\mgmp\MgmpClient::class
        ],
        'gridExporter' => [
            'class' => rgk\utils\components\grid\export\GridExporter::class,
        ],
    ],

    'modules' => [
        'gridview' => [
            'class' => '\kartik\grid\Module'
        ],
        'modmanager' => 'mcms\modmanager\Module',
        'queue' => 'mcms\queue\Module',
    ],

];
