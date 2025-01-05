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
  'options' => ['class' => 'login-form']]); ?>

<?php if (!$module->isRestorePasswordSupport()): ?>

  <?= $form->field($model, 'email')->textInput(['placeholder' => Yii::_t('users.signup.email')])->label(false) ?>

  <?php if ($model->shouldUseCaptcha()) : ?>
    <?= $form->field($model, 'captcha')->widget(ReCaptcha::class)->label(false) ?>
  <?php else: ?>
    <div id="recapcha-<?= $form->id ?>" data-site-key="<?= Yii::$app->reCaptcha->siteKey ?>"></div>
    <?= Html::activeHiddenInput($model, 'captcha') ?>
  <?php endif; ?>

  <div
    class="form-group"><?= Html::submitButton(Yii::_t('users.forms.send'), ['class' => 'btn custom-btn-dark custom-btn-sm custom-btn-login']); ?></div>
  <ul>
    <li><a class="js-back-to-login" href="#" data-scroll="loginDrop"><?= Yii::_t('users.login.login') ?></a></li>
  </ul>

<?php else: ?>
  <?= Yii::_t('users.forms.for change password please contact administrator') ?>
<?php endif; ?>

<?php ActiveForm::end() ?>
