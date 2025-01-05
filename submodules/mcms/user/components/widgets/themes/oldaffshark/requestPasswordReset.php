<?php

use himiklab\yii2\recaptcha\ReCaptcha;
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var \mcms\user\Module $module */
/* @var $model \mcms\user\models\PasswordResetRequestForm */
$module = Yii::$app->getModule('users');
?>

<?php $form = ActiveForm::begin([
  'id' => 'password-reset-request-form',
  'action' => Url::to(['users/api/request-password-reset']),
  'options' => ['class' => 'modal-content']]); ?>

<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
  </button>
  <h1 class="modal-title text-center" id="myModalLabel"><?= Yii::_t('users.forms.request_password_title') ?></h1>
</div>
<?php if (!$module->isRestorePasswordSupport()): ?>
  <div id="restore-password" class="modal-body">
    <?= $form->field($model, 'email')->textInput(['placeholder' => Yii::_t('users.signup.email')])->label(false) ?>

    <?php if ($model->shouldUseCaptcha()) : ?>
      <?= $form->field($model, 'captcha')->widget(ReCaptcha::class)->label(false) ?>
    <?php else: ?>
      <span id="recapcha-<?= $form->id ?>" data-site-key="<?= Yii::$app->reCaptcha->siteKey ?>"></span>
      <?= Html::activeHiddenInput($model, 'captcha') ?>
    <?php endif; ?>
  </div>
  <div id="restore-password-footer" class="modal-footer text-center">
    <div class="form-group">
      <?= Html::submitButton(Yii::_t('users.forms.send'), ['class' => 'btn custom-button custom-red-btn']); ?>
      <div class="help-block"></div>
    </div>
  </div>
<?php else: ?>
  <div class="modal-body wp-caption-text"><?= Yii::_t('users.forms.for change password please contact administrator') ?></div>
  <div id="signup-footer" class="modal-footer text-center"></div>
<?php endif; ?>

<?php ActiveForm::end() ?>
