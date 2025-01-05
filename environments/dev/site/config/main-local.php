<?php

$config = [
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => '',
        ],
        'jwt' => [
            'key' => '',  //typically a long random string
        ],
    ],
];

if (YII_DEBUG) {
    // configuration adjustments for 'debug'
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        'dataPath' => '@runtime/debug-site',
        'allowedIPs' => ['*']
    ];

}

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'allowedIPs' => ['*']
    ];
}

$config['modules']['openapireader'] = [
    'class' => \bestyii\openapiReader\Module::class,

    'defaultDoc' => 'api',
    'path' => [
        'api' => '@site/modules/v3/',
        //'openwork'=>'@api/controllers/',
    ],
    // disable page with your logic
    'isDisable' => function () {
        return false;
    },
    // replace placeholders in swagger content
    'afterRender' => function ($content) {
        $content = str_replace(
            [
                '{{SERVER_DESCRIPTION}}',
                '{{HOST}}',
                '{{BASE_PATH}}'
            ],
            [
                'Localhost',
                Yii::$app->request->hostInfo,
                'api/v3'
            ], $content);
        return $content;
    }
];


return $config;
