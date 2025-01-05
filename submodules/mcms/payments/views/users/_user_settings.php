<?php
use mcms\payments\components\widgets\PartnerSettings;
use mcms\payments\components\widgets\UserSettings;
$config = [
  'isModal' => $isModal,
  'options' => [
    'userId' => $userId,
    'currency' => Yii::$app->request->get('currency'),
    'wallet_type' => Yii::$app->request->get('wallet_type'),
    'getPartial' => true,
  ]
]; ?>
<?= PartnerSettings::widget($config) ?>
