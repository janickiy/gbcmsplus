<?php

use mcms\partners\components\PartnerFormatter;

$params = array_merge(
  require(__DIR__ . '/../../common/config/params.php'),
  require(__DIR__ . '/../../common/config/params-local.php'),
  require(__DIR__ . '/params.php'),
  require(__DIR__ . '/params-local.php')
);

return [
  'id' => 'app-site',
  'basePath' => dirname(__DIR__),
  'bootstrap' => ['log'],
  'controllerNamespace' => 'site\controllers',
  'defaultRoute' => 'default/index',
  'components' => [
    'request' => [
      'baseUrl' => '',
//      'parsers' => [
//        'application/json' => 'yii\web\JsonParser',
//      ]
    ],
    'urlManager' => [
      'scriptUrl' => '/',
      'normalizer' => [
        'class' => 'yii\web\UrlNormalizer',
        'action' => \yii\web\UrlNormalizer::ACTION_REDIRECT_PERMANENT, // используем временный редирект вместо постоянного
        'normalizeTrailingSlash' => true,
      ],
      'rules' => [
        [
          'class' => 'common\components\RestUrlRule',
          'module' => 'api',
          'version' => 'v2',
        ],
        [
          'class' => 'yii\rest\UrlRule',
          // 'pattern' => '<module:\w+><controller:\w+>/<id:\d+>',
          'prefix' => 'api',
          'controller' => 'v3/country',
         // 'except' => ['index','create','update','delete','view','options'],
          'pluralize'=>false
        ],
        'api/v3/<controller:[\w\-]+>/<action:[\w\-]+>' => 'v3/<controller>/<action>',
        [
//          'pattern' => 'apidoc/<controller:[\w\-]+>/<action:[\w\-]+>',
//          'route' => 'v3/<controller>/<action>',
          'pattern' => 'api/doc/<action:[\w\-]+>',
          'route' => 'doc/<action>',
          'defaults' => ['doc' => 'api'],
        ],
       '<url:\w+>' => 'default/view-page',
        
      ]
    ],

    'log' => [
      'traceLevel' => YII_DEBUG ? 3 : 0,
      'targets' => [
        [
          'class' => 'yii\log\FileTarget',
          'logFile' => '@runtime/logs/site.log',
          'levels' => ['error', 'warning'],
        ],
        [
          'class' => 'yii\log\FileTarget',
          'categories' => ['email'],
          'logFile' => '@runtime/logs/email.log',
          'levels' => ['info'],
        ],
      ],

    ],

    'errorHandler' => [
      'errorAction' => 'partners/default/error',
    ],

    'formatter' => [
      'class' => PartnerFormatter::class,
      'dateFormat' => 'php:d.m.Y',
      'datetimeFormat' => 'php:d.m.Y H:i:s',
      'timeFormat' => 'php:H:i:s',
      'thousandSeparator' => ' ',
      'decimalSeparator' => ',',
      'defaultTimeZone' => 'Europe/Moscow',
    ],
    'jwt' => [
      'class' => \sizeg\jwt\Jwt::class,
      'key' => 'SECRET-KEY',  //typically a long random string
      'jwtValidationData' => \site\modules\v3\components\JwtValidationData::class,
    ],
  ],
  'modules' => [
    'v3' => [
      'class' => 'site\modules\v3\Module',
    ]
  ],
  'params' => $params,

  // перехват событий
  'on ' . \yii\web\Application::EVENT_BEFORE_REQUEST => function () {
    if (!Yii::$app->user->isGuest) {
      Yii::$app->user->identity->updateOnlineOffline();
    }
  }

];
