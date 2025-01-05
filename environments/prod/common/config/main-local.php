<?php
return [
    'name' => 'ProjectName',

    'components' => [
        'db' => require(__DIR__ . '/db-local.php'),
        'dbCs' => require(__DIR__ . '/db-columnstore-local.php'),
        'sdb' => require(__DIR__ . '/sdb-local.php'), // бд для статы по меткам
        'clickhouse' => require(__DIR__ . '/db-clickhouse-local.php'), // кликхаус
        'session' => [
            'class' => 'yii\web\Session' // нужен для хранения сессий в редис
        ],

        'cache' => [
            'class' => 'yii\caching\MemCache', // use DummyCache for no real caching
            'keyPrefix' => 'MCMS', // поменять если на одном сервере мемкеш для двух и более проектов
            'servers' => [[
                'host' => '127.0.0.1',
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
                        '@app/themes/rgktools', // тема лендинга
                        '@app/themes/basic', // общая тема для лейаута лендинга
                    ],
                    '@mcms/partners/components/widgets/views' => '@mcms/partners/components/widgets/themes/rgktools', // темы для email шаблонов
                    '@mcms/user/components/widgets/views' => '@mcms/user/components/widgets/themes/rgktools', // темы виджетов форм
                ],
            ],
        ],
    ],
];