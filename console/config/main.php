<?php

$params = array_merge(
  require(__DIR__ . '/../../common/config/params.php'),
  require(__DIR__ . '/../../common/config/params-local.php'),
  require(__DIR__ . '/params.php'),
  require(__DIR__ . '/params-local.php')
);

$migrationLookup = require(__DIR__ . '/migration-lookup.php');

return [
  'id' => 'app-console',
  'basePath' => dirname(__DIR__),
  'bootstrap' => ['log', 'payprocess'],
  'controllerNamespace' => 'console\controllers',
  'modules' => [
    'alerts' => [
      'class' => 'admin\modules\alerts\Module',
      'messages' => 'admin/modules/alerts/messages',
      'fixtures' => [
        'alert_events' => \admin\modules\alerts\tests\fixtures\Event::class,
        'alert_event_filters' => \admin\modules\alerts\tests\fixtures\EventFilter::class,
      ],
    ],
    'users' => [
      'class' => 'mcms\user\Module',
    ],
    'manager' => [
      'class' => 'mcms\modmanager\Module'
    ],
    'payments' => [
      'class' => 'mcms\payments\Module'
    ],
    'payprocess' => [ // в приложении admin тот же конфиг если что
      'class' => \rgk\payprocess\Module::class,
      'protectedUploadPath' => Yii::getAlias('@protectedUploadPath'),
      'proxy' => \yii\helpers\ArrayHelper::getValue($params, 'proxy'),
      'useProxy' => true,
    ]
  ],
  'components' => [
    'urlManager' => [
      'baseUrl' => '/admin',
      'hostInfo' => ''
    ],
    'log' => [
      'traceLevel' => YII_DEBUG ? 3 : 0,
      'targets' => [
        [
          'class' => 'yii\log\FileTarget',
          'logFile' => '@runtime/logs/console.log',
          'levels' => ['error', 'warning'],
        ],
        [
          'class' => 'yii\log\FileTarget',
          'logFile' => '@runtime/logs/warning.log',
          'categories' => ['warning'],
          'levels' => ['warning'],
          'logVars' => [],
          'prefix' => function ($message) { return ''; },
          'exportInterval' => 1,
        ],
      ],
    ]

  ],
  'controllerMap' => [
    'migrate' => [
      'class' => console\components\MigrateController::class,
      'migrationLookup' => $migrationLookup,
    ],
    'db-fix' => \console\components\DbFixController::class,
    'fixture' => [
      'class' => 'yii\faker\FixtureController',
    ],
    'changelog' => [
      'class' => \rgk\changelog\commands\ChangeLogController::class,
    ],
    'rgkutils' => [
      'class' => \rgk\utils\controllers\UtilsController::class
    ],
  ],
  'params' => $params,
];
