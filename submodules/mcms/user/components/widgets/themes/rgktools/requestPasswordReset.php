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

<?php if (!$module->isRestorePasswordSupport()): ?>
    <?= $form->field($model, 'email', ['options' => ['class' => 'form-group form-login']])->textInput(['placeholder' => Yii::_t('users.signup.email')])->label(false) ?>

    <?php if ($model->shouldUseCaptcha()) : ?>
      <?= $form->field($model, 'captcha')->widget(ReCaptcha::class)->label(false) ?>
    <?php else: ?>
      <span id="recapcha-<?= $form->id ?>" data-site-key="<?= Yii::$app->reCaptcha->siteKey ?>"></span>
      <?= Html::activeHiddenInput($model, 'captcha') ?>
    <?php endif; ?>
      <?= Html::submitButton(Yii::_t('users.forms.restore'), ['class' => 'btn']); ?>
<?php else: ?>
  <p><?= Yii::_t('users.forms.for change password please contact administrator') ?></p>
<?php endif; ?>

<?php ActiveForm::end() ?>
