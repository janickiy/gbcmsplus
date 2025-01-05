<?php
use mcms\common\web\UrlAccess;
use mcms\modmanager\Module;
use yii\filters\AccessControl;

$params = array_merge(
  require(__DIR__ . '/../../common/config/params.php'),
  require(__DIR__ . '/../../common/config/params-local.php'),
  require(__DIR__ . '/params.php'),
  require(__DIR__ . '/params-local.php')
);

return [
  'id' => Module::ADMIN_APP_ID,
  'basePath' => dirname(__DIR__),
  'controllerNamespace' => 'admin\controllers',
  'bootstrap' => ['log', 'changelog', 'payprocess', 'utils', 'settings'],
  'modules' => [
    'gridview' => [
      'class' => '\kartik\grid\Module'
    ],
    'datecontrol' => [
      'class' => 'kartik\datecontrol\Module'
    ],
    'markdown' => [
      'class' => 'kartik\markdown\Module',
    ],
    'utils' => [
      'class' => rgk\utils\Module::class,
    ],
    'settings' => [
      'class' => rgk\settings\Module::class,
      'as access' => [
        'class' => AccessControl::class,
        'rules' => [
          [
            'allow' => true,
            'roles' => ['AppBackendDefaultSettings']
          ]
        ],
        'denyCallback' => function ($rule, $action) {
          /** @var \yii\base\InlineAction $action */
          /** @var \mcms\user\Module $userModule */
          $userModule = Yii::$app->getModule('users');
          $action->controller->redirect($userModule->urlCabinet);
        }
      ],
    ],
    'changelog' => [
      'class' => \rgk\changelog\Module::class,
      'on ' . \rgk\changelog\controllers\DefaultController::EVENT_AFTER_ACTION => function () {
        \common\modules\changelog\models\UserChangelogSetting::touchCurrentUserChangelogLastRead();
      }
    ],
    'payprocess' => [ // в приложении console тот же конфиг если что
      'class' => \rgk\payprocess\Module::class,
      'protectedUploadPath' => Yii::getAlias('@protectedUploadPath'),
      'proxy' => \yii\helpers\ArrayHelper::getValue($params, 'proxy'),
      'useProxy' => true,
    ]
  ],
  'layout' => '@app/views/layouts/main',
  'defaultRoute' => 'default/index',
  'components' => [

    'assetManager' => [
      'appendTimestamp' => true,
      'bundles' => [
        'yii\grid\GridViewAsset' => [
          'sourcePath' => '@mcms/common/grid/assets/js',
          'js' => ['yii.gridView.2.0.12fetched.js'],
        ]
      ]
    ],

    // настройка url
    'request' => [
      'baseUrl' => '/admin'
    ],
    'urlManager' => [
      'scriptUrl' => '',
    ],

    // настройка логов
    'log' => [
      'traceLevel' => YII_DEBUG ? 3 : 0,
      'targets' => [
        [
          'class' => 'yii\log\FileTarget',
          'logFile' => '@runtime/logs/admin.log',
          'levels' => ['error', 'warning'],
        ],
      ],
    ],
    'errorHandler' => [
      'errorAction' => 'default/error',
    ],
    'urlAccess' => [
      'class' => UrlAccess::class
    ],
  ],
  'params' => $params,
  'on moduleInstall' => function () {
    \Yii::$app->cache->delete('moduleInstall');
  },
  'on ' . \mcms\common\rbac\DbManager::EVENT_ASSIGN => '\mcms\common\rbac\DbManager::handleAssignRevoke',
  'on ' . \mcms\common\rbac\DbManager::EVENT_REVOKE => '\mcms\common\rbac\DbManager::handleAssignRevoke',

  // перехват событий
  'on ' . \yii\web\Application::EVENT_BEFORE_REQUEST => function () {
    if (!Yii::$app->user->isGuest) {
      Yii::$app->user->identity->updateOnlineOffline();
    }
  }
];
