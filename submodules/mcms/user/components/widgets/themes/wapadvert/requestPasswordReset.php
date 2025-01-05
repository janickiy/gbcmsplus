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
    'options' => ['class' => 'login-form login-form_modal uk-form']]); ?>

<div class="title title_mini title_modal"><?= Yii::_t('users.forms.request_password_title') ?></div>

<?php if (!$module->isRestorePasswordSupport()): ?>
    <div id="restore-password" class="summary">
        <div class="login-form__input-item">
            <div class="form-group field-email custom-login-field required custom-input-login">
                <?= $form->field($model, 'email', ['options' => ['class' => 'custom-div custom-input-login']])
                    ->textInput(['placeholder' => Yii::_t('users.signup.email'), 'class' => 'form-control custom-input'])->label(false) ?>
            </div>
        </div>
        <?php if ($model->shouldUseCaptcha()) : ?>
            <?= $form->field($model, 'captcha')->widget(ReCaptcha::class)->label(false) ?>
        <?php else: ?>
            <span id="recapcha-<?= $form->id ?>" data-site-key="<?= Yii::$app->reCaptcha->siteKey ?>"></span>
            <?= Html::activeHiddenInput($model, 'captcha') ?>
        <?php endif; ?>
        <div class="form-group custom-login-button-cont">
            <?= Html::submitButton(Yii::_t('users.forms.send'), ['class' => 'btn custom-icon-login custom-icon-login_text']); ?>
        </div>
    </div>
    <div class="modal__footer">
    </div>
<?php else: ?>
    <div style="text-align: center"><?= Yii::_t('users.forms.for change password please contact administrator') ?></div>
<?php endif; ?>

<?php ActiveForm::end() ?>

