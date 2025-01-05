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
        'dataPath' => '@runtime/debug-site'
    ];
}

return $config;
