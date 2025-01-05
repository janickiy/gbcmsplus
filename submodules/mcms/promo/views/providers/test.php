<?php

use mcms\promo\models\form\ProviderTestForm;
use yii\bootstrap\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/** @var ProviderTestForm $model */
/** @var array $providersDropdownItems */
?>

<?php $form = ActiveForm::begin([
  'options' => ['class' => 'well'],
  'method' => 'get'
]); ?>

<?= $form->field($model, 'providerId')
  ->dropDownList($providersDropdownItems, ['prompt' => '-- choose provider --'])
  ->label(false); ?>

<?= $form->field($model, 'type')->radioList($model::getTypesList())->label(false); ?>

<?= Html::submitButton(Html::icon('glyphicon glyphicon-new-window') . ' Get response', ['class' => 'btn btn-primary', 'formtarget' => '_blank']) ?>


  <div class="btn-group pull-right">
    <?= Html::submitButton(Html::icon('glyphicon glyphicon-play') . ' Make sync', [
      'class' => 'btn btn-warning',
      'formaction' => Url::to(['make-sync']),
    ]) ?>
    <?= Html::submitButton(Html::icon('glyphicon glyphicon-play') . ' Make sync without time check', [
      'class' => 'btn btn-danger',
      'formaction' => Url::to(['make-full-sync']),
    ]) ?>
    <?= Html::submitButton(Html::icon('glyphicon glyphicon-play') . ' Test provider', [
      'class' => 'btn btn-warning',
      'formaction' => Url::to(['test-provider']),
    ]) ?>
  </div>

<?php ActiveForm::end();