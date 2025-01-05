<?php
return [
  'components' => [

    'db' => require(__DIR__ . '/../db-local.php'),

    'session' => [
      'class' => 'yii\web\Session' // use CacheSession
    ],

    'cache' => [
      'class' => 'yii\caching\MemCache', // use DummyCache for no real caching
      'keyPrefix' => 'MCMS',
      'servers' => [[
        'host' => 'memcache',
        'port' => 11211,
        'weight' => 100,
        'timeout' => 300,
        'retryInterval' => 1200
      ]]
    ],

    'queue' => [
      'class' => '\rgk\queue\QueueFacade',
      'driver' => [
        'class' => '\rgk\queue\driver\FakeQueue',
        'payloadMap' => [
          'libs\queue\postbacks\Payload' => 'mcms\statistic\components\queue\postbacks\Payload',
        ],
        'loggerClass' => '\yii\log\Logger',
      ],
    ],


    'assetManager' => [
      'forceCopy' => false // не кушируем assets
    ],

    'view' => [
      'theme' => [
        'pathMap' => [
          '@app/views' => [
            '@app/themes/wapclick', // тема лендинга
            '@app/themes/basic', // общая тема для лейаута лендинга
          ],
          '@mcms/partners/components/widgets/views' => '@mcms/partners/components/widgets/themes/wapclick',
          '@mcms/user/components/widgets/views' => '@mcms/user/components/widgets/themes/wapclick',
        ],
      ],
    ],
    'request' => [
      // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
      'cookieValidationKey' => 'sioEQASDkaslgdhasd8914',
    ],
  ],
];
