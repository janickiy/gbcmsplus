<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>

<?php

$form = ActiveForm::begin([
  'options' => ['enctype' => 'multipart/form-data'],
  'method' => 'POST'
]) ?>

  <div class="form-group">
    <?= Html::fileInput('fileInput', null, ['id' => 'fileInput']); ?>
    <p class="help-block">New file will be returned</p>
  </div>

<?= Html::submitButton('Run ', ['class' => 'btn btn-default']) ?>

<?php ActiveForm::end() ?>