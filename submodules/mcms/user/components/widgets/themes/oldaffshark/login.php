<?php
use himiklab\yii2\recaptcha\ReCaptcha;
use yii\widgets\ActiveForm;
use yii\bootstrap\Html;
use yii\helpers\Url;

/* @var $model \mcms\user\models\LoginForm */
?>

<?php $form = ActiveForm::begin([
  'id' => 'login-form',
  'action' => Url::to(['users/api/login']),
  'options' => ['class' => 'modal-content']
]); ?>

<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
  </button>
  <h1 class="modal-title text-center" id="myModalLabel"><?= Yii::_t('users.login.login_form') ?></h1>
</div>
<div class="modal-body">

  <?= $form->field($model, 'username')->textInput(['placeholder' => Yii::_t('users.login.username_email')])->label(false) ?>
  <?= $form->field($model, 'password')->passwordInput(['placeholder' => Yii::_t('users.login.password')])->label(false) ?>

  <a href="#" data-toggle="modal" data-target="#lost-pass" data-dismiss="modal"
     class="forgot-pass request-password-modal-button"><?= Yii::_t('users.login.forgot_your_password')?></a>

  <?= $form->field($model, 'rememberMe')->checkbox(); ?>

  <?php if ($model->shouldUseCaptcha()) : ?>
    <?= $form->field($model, 'captcha')->widget(ReCaptcha::class)->label(false) ?>
  <?php else: ?>
    <span id="recapcha-<?= $form->id ?>" data-site-key="<?= Yii::$app->reCaptcha->siteKey ?>"></span>
    <?= Html::activeHiddenInput($model, 'captcha') ?>
  <?php endif; ?>

</div>
<div class="modal-footer text-center">

  <div class="form-group">
    <?= Html::submitButton(Yii::_t('users.login.sign_in'), ['class' => 'btn custom-button custom-red-btn']); ?>
    <div class="help-block"></div>
  </div>
</div>

<?php ActiveForm::end() ?>

