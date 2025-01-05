<?php

use rgk\changelog\components\ChangelogUpdater;

Yii::setAlias('rootPath', dirname(__DIR__) . '/..');
Yii::setAlias('common', dirname(__DIR__));
Yii::setAlias('site', dirname(dirname(__DIR__)) . '/site');
Yii::setAlias('admin', dirname(dirname(__DIR__)) . '/admin');
Yii::setAlias('console', dirname(dirname(__DIR__)) . '/console');
Yii::setAlias('mcms', dirname(__DIR__) . '/../submodules/mcms');
Yii::setAlias('rgk', dirname(__DIR__) . '/../vendor/rgk');
Yii::setAlias('uploadPath', dirname(__DIR__) . '/../web/uploads');
Yii::setAlias('protectedUploadPath', dirname(__DIR__) . '/../protected/uploads');
Yii::setAlias('uploadUrl', '/uploads');

// подключаем bootstrap из common модуля для легкого обновления проектов
require_once(Yii::getAlias('@mcms') . '/common/bootstrap.php');

Yii::$container->set('mcms\modmanager\components\SettingsInterface', [
    'class' => 'mcms\modmanager\components\Settings'
]);

Yii::$container->setSingleton('admin\components\module\LocatorInterface', [
    'class' => 'admin\components\module\Locator'
]);

Yii::$container->set('mcms\notifications\components\storage\NotificationInterface', [
    'class' => 'mcms\notifications\components\storage\Notification'
]);

if (YII_ENV_DEV && YII_DEBUG) {
    Yii::$container->set('rgk\payprocess\components\handlers\PaypalPayHandler', [
        'isSandbox' => true
    ]);
}

Yii::$container->set(
    'yii\web\Request',
    ['class' => 'mcms\common\web\Request']
);

Yii::$container->set(ChangelogUpdater::class, [
    'clientSecret' => '@common/modules/changelog/config/client_secret.json',
    'credentialsPath' => '@common/modules/changelog/config/credentials.json',
    'fileId' => '1ioTOIc5bAKSx-Rcmb5slwYjNrkwCCcMz5hZOjT1GJSk',
]);
