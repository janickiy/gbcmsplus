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
    'options' => ['autocomplete' => 'off']]); ?>

<?php if (!$module->isRestorePasswordSupport()): ?>

    <?= $form->field($model, 'email', ['options' => ['class' => 'form-group input-email']])
        ->textInput(['placeholder' => Yii::_t('users.signup.email'), 'class' => 'form-control'])->label(false) ?>

    <?php if ($model->shouldUseCaptcha()) : ?>
        <?= $form->field($model, 'captcha', ['options' => ['class' => false]])->widget(ReCaptcha::class)->label(false) ?>
    <?php else: ?>
        <span id="recapcha-<?= $form->id ?>" class="checkbox"
              data-site-key="<?= Yii::$app->reCaptcha->siteKey ?>"></span>
        <?= Html::activeHiddenInput($model, 'captcha') ?>
    <?php endif; ?>

    <?= Html::submitButton(Yii::_t('users.forms.send'), ['class' => 'btn btn-primary']); ?>

<?php else: ?>
    <?= Yii::_t('users.forms.for change password please contact administrator') ?>
<?php endif; ?>

<?php ActiveForm::end() ?>