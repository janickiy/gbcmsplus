<?php
return [
    'components' => [

        'db' => require(__DIR__ . '/db-local.php'),
        'dbCs' => require(__DIR__ . '/db-columnstore-local.php'),
        'sdb' => require(__DIR__ . '/sdb-local.php'), // бд для статы по меткам
        'clickhouse' => require(__DIR__ . '/db-clickhouse-local.php'), // кликхаус

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

        'reCaptcha' => [
            'class' => 'mcms\user\components\widgets\recaptcha\ReCaptchaConfig',
            'siteKeyV2' => '',
            'secretV2' => '',
            'siteKeyV3' => '',
            'secretV3' => '',
        ],

        'exchange' => [
            'class' => rgk\exchange\components\ExternalCurrenciesProvider::class,
            'coursesProvider' => [
                'class' => rgk\exchange\components\provider\SimpleCoursesProvider::class,
                'exchanger' => [
                    'class' => \mcms\currency\components\WapGroupFetcher::class,
                    'apiUrl' => '',
                    'token' => '',
                ],
            ]
        ],

        'queue' => [
            'class' => '\rgk\queue\QueueFacade',
            'code' => 'mcms_queue', // TRICKY Должен быть в формате {код_проекта}_{код_компонента} для избежания конфликтов
            'dbCallable' => function () {
                return Yii::$app->db->getMasterPdo();
            },
            'driver' => [
                // TODO Включить резервную очередь @see \rgk\queue\drivers\AbstractDrive::isReserve (не забыть крон)
                'class' => '\rgk\queue\driver\RabbitMQ',
                'host' => 'rabbitmq',
                'port' => 5672,
                'user' => 'guest',
                'password' => 'guest',
                'payloadMap' => [
                    'libs\queue\postbacks\Payload' => 'mcms\statistic\components\queue\postbacks\Payload',
                ],
                'loggerClass' => '\yii\log\Logger',
            ],
        ],

        'view' => [
            'theme' => [
                'pathMap' => [
                    '@app/views' => [
                        '@app/themes/wapcash', // тема лендинга
                        '@app/themes/basic', // общая тема для лейаута лендинга
                    ],
                    '@mcms/partners/components/widgets/views' => '@mcms/partners/components/widgets/themes/wapcash', // темы для email шаблонов
                    '@mcms/user/components/widgets/views' => '@mcms/user/components/widgets/themes/wapcash', // темы виджетов форм
                ],
            ],
        ],

//    'log' => [
//      'targets' => [
//        // dsn проекта test в sentry
//        [
//          'class' => 'notamedia\sentry\SentryTarget',
//          'dsn' => 'http://fd81e563b6e84d9690b14e766e604252:c8f2282343c44b0ea669eec07d1843f7@sentry.rgktools.com/13',
//          'levels' => ['error'],
//          'context' => true
//        ],
//      ]
//    ]

    ],
];
