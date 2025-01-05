<?php

use himiklab\yii2\recaptcha\ReCaptcha;
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use mcms\common\helpers\ArrayHelper;

/* @var \mcms\user\Module $module */
/* @var $model \mcms\user\models\PasswordResetRequestForm */
$module = Yii::$app->getModule('users');
?>

<?php $form = ActiveForm::begin([
  'id' => 'password-reset-request-form',
  'action' => Url::to(['users/api/request-password-reset']),
  'options' => ['class' => 'rega-form']]); ?>

<?php if (!$module->isRestorePasswordSupport()): ?>

  <div class="login-form-top">
    <h3><?= ArrayHelper::getValue($options, 'modal_title') ?></h3>
    <p><?= ArrayHelper::getValue($options, 'modal_text') ?></p>
  </div>

  <?= $form->field($model, 'email')
    ->textInput(['placeholder' => Yii::_t('users.signup.email'), 'class' => 'form-control email'])->label(false) ?>

  <?php if ($model->shouldUseCaptcha()) : ?>
    <?= $form->field($model, 'captcha')->widget(ReCaptcha::class)->label(false) ?>
  <?php else: ?>
    <span id="recapcha-<?= $form->id ?>" data-site-key="<?= Yii::$app->reCaptcha->siteKey ?>"></span>
    <?= Html::activeHiddenInput($model, 'captcha') ?>
  <?php endif; ?>

  <div class="form-group">
    <?= Html::submitButton(Yii::_t('users.forms.restore'), ['class' => 'btn-reg']); ?>
  </div>

  <br>
  <div class="login-link">
    <a href="#modal-form_login" class="link-login popup-with-move-anim"><?= Yii::_t('users.forms.remembered_password') ?></a>
  </div>

<?php else: ?>
  <div class="login-form-top">
    <h3><?= ArrayHelper::getValue($options, 'modal_title') ?></h3>
    <p><?= Yii::_t('users.forms.for change password please contact administrator') ?></p>
  </div>
<?php endif; ?>

<?php ActiveForm::end() ?>