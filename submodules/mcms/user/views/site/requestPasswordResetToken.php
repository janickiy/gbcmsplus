<?php

use himiklab\yii2\recaptcha\ReCaptcha;
use yii\widgets\ActiveForm;
use yii\helpers\Html;

/* @var $model \mcms\user\models\PasswordResetRequestForm */
?>
<div class="form-container well">

  <div class="row">
    <div class="col-md-12">
      <?php $form = ActiveForm::begin(['id' => 'password-reset-token-form']); ?>

      <?= $form->field($model, 'email') ?>

      <?php if ($model->shouldUseCaptcha()) : ?>
        <?= $form->field($model, 'captcha')->widget(ReCaptcha::class)->label(false) ?>
      <?php endif; ?>

      <div class="form-group">
        <?= Html::submitButton(Yii::_t('forms.send'), ['class' => 'btn btn-primary']); ?>
      </div>

      <?php ActiveForm::end() ?>
    </div>
  </div>
</div>
