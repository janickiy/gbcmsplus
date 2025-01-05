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
  'options' => ['class' => 'login-form login-form_modal']]); ?>

<div class="title title_mini title_modal"><?= Yii::_t('users.forms.request_password_title') ?></div>

<?php if (!$module->isRestorePasswordSupport()): ?>
  <div id="restore-password" class="summary">
    <div class="login-form__input-item">
      <?= $form->field($model, 'email', ['options' => ['class' => 'custom-div custom-div_login']])
        ->textInput(['placeholder' => Yii::_t('users.signup.email'), 'class' => 'form-control custom-input'])->label(false) ?>
    </div>
    <?php if ($model->shouldUseCaptcha()) : ?>
      <div class="login-form__input-item">
        <?= $form->field($model, 'captcha')->widget(ReCaptcha::class)->label(false) ?>
      </div>
    <?php else: ?>
      <div class="login-form__input-item">
        <span id="recapcha-<?= $form->id ?>" data-site-key="<?= Yii::$app->reCaptcha->siteKey ?>"></span>
        <?= Html::activeHiddenInput($model, 'captcha') ?>
      </div>
    <?php endif; ?>
    <div class="login-form__input-item">
      <div class="form-group">
        <?= Html::submitButton(Yii::_t('users.forms.send'), ['class' => 'btn custom-submit uk-button uk-button-large uk-button-primary']); ?>
      </div>
    </div>
  </div>
  <div class="modal__footer">
  </div>
<?php else: ?>
<div class="login-form__input-item">
  <div style="text-align: center"><?= Yii::_t('users.forms.for change password please contact administrator') ?></div>
</div>
<div class="modal__footer"></div>
<?php endif; ?>

<?php ActiveForm::end() ?>
