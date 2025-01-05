<?php

use mcms\payments\components\widgets\UserSettings;

/** @var mcms\common\web\View $this */
/** @var integer $userId */
/** @var \mcms\payments\models\UserPaymentSetting $model */

?>

<?php $this->beginBlock('info') ?>
<?php if ($model->canUseMultipleCurrenciesBalance()): ?>
  <?= Yii::$app->getModule('promo')->api('mainCurrenciesWidget', [
    'type' => 'buttons',
    'containerId' => 'resellerBalanceCurrencySwitcher',
    'data' => ['confirm-text' => Yii::_t('app.common.data_will_not_be_saved') . '. ' . Yii::_t('app.common.are_you_sure')],
  ])->getResult() ?>
<?php endif ?>
<?php $this->endBlock() ?>

<?= UserSettings::widget(['options' => compact('userId')]) ?>