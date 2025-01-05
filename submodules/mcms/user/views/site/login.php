<?php
use himiklab\yii2\recaptcha\ReCaptcha;
use yii\widgets\ActiveForm;
use \yii\bootstrap\Html;

/* @var $model \mcms\user\models\LoginForm */
?>

<div class="form-container well">

  <div class="row">
    <div class="col-md-12">
      <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

        <?= $form->field($model, 'username') ?>
        <?= $form->field($model, 'password')->passwordInput() ?>
        <?= $form->field($model, 'rememberMe')->checkbox(); ?>

        <?php if ($model->shouldUseCaptcha()) : ?>
          <?= $form->field($model, 'captcha')->widget(ReCaptcha::class)->label(false) ?>
        <?php endif; ?>

        <div class="form-group">
          <?= Html::submitButton(Yii::_t('forms.login'), ['class' => 'btn btn-primary col-md-12']); ?>
        </div>

      <?php ActiveForm::end() ?>
    </div>
  </div>

</div>