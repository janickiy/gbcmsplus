<?php
return [
    'adminEmail' => 'admin@example.com',
    'supportEmail' => 'support@example.com',
    'user.passwordResetTokenExpire' => 3600,
    'languages' => [
        'ru',
        'en'
    ],
    'langCodes' => [
        'ru' => 'ru-RU',
        'en' => 'en-EN',
    ],
    //proxy for cURL
    //нужен для отправки постбеков, чтобы не светить реальный адрес
//  'proxy' => '178.62.4.108:3128',
    // логирование dev-версии
    'dev_log' => [
        'class' => \yii\log\FileTarget::class,
        'logFile' => '@runtime/logs/error.log',
        'except' => [
            'yii\web\HttpException:404',
            'yii\web\HttpException:405'
        ],
        'logVars' => ['_GET', '_POST', '_COOKIES'],
        'levels' => ['error'],
    ],
    'processingPercent' => -1.00,
    'paysystem-percents' => [
        'webmoney' => 0.00,
        'yandex-money' => -1.50,
        'epayments' => 0.00,
        'paypal' => -3.00,
        'paxum' => -1.00,
        'wireiban' => 0.00,
        'card' => -3.50,
        'private-person' => 6.00,
        'juridical-person' => 8.00,
        'qiwi' => -1.50,
    ],
    'invoiceNumberPrefix' => '',
    'analyticsDateOn' => '2020-01-01',
    'geo_client' => [
        'url' => '',
        'token' => '',
    ],
    'clickhouseMysql' => [
        'host' => 'db:3306',
        'user' => 'root',
        'password' => 'root',
        'db' => 'dev_db',
    ],
    'clickhouse' => [
        'host' => 'clickhouse',
        'user' => 'default',
        'password' => '',
        'db' => 'wapclick',
    ]
];
