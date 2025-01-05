<?php

require(YII_APP_BASE_PATH . '/common/config/bootstrap.php');

$params = array_merge(
  require(YII_APP_BASE_PATH . '/common/config/params.php'),
  require(YII_APP_BASE_PATH . '/common/config/params-local.php')
);

/**
 * Application configuration shared by all applications and test types
 */
return yii\helpers\ArrayHelper::merge(
  require(YII_APP_BASE_PATH . '/common/config/main.php'),
  [
    'id' => 'TEST_APP',
    'basePath' => dirname(__DIR__),
    'controllerMap' => [
      'fixture' => [
        'class' => 'yii\faker\FixtureController',
        'fixtureDataPath' => '@tests/common/fixtures/data',
        'templatePath' => '@tests/common/templates/fixtures',
        'namespace' => 'tests\common\fixtures',
      ],
    ],
    'components' => [
      'mailer' => [
        'useFileTransport' => true,
      ],
      'urlManager' => [
        'showScriptName' => true,
        'scriptUrl' => '/',
        'hostInfo' => ''
      ],
      'request' => [
        'baseUrl' => '/admin'
      ],
    ],
    'params' => $params
  ],
  require(__DIR__ . '/main-local.php')
);
