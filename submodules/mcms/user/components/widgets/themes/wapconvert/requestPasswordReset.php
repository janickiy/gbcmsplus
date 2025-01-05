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
    'options' => ['class' => 'form-modal', 'autocomplete' => 'off']]); ?>

<?php if (!$module->isRestorePasswordSupport()): ?>

    <div class="form-group field-passwordresetrequestform-email">
        <div class="form-group-icon">
            @
        </div>
        <div class="form-group-field">
            <?= Html::activeTextInput($model, 'email', ['class' => 'form-control', 'placeholder' => Yii::_t('users.signup.email')]) ?>
        </div>
        <div class="help-block"></div>
    </div>

    <?php if ($model->shouldUseCaptcha()) : ?>
        <?= $form->field($model, 'captcha', ['options' => ['class' => false]])->widget(ReCaptcha::class)->label(false) ?>
    <?php else: ?>
        <span id="recapcha-<?= $form->id ?>" class="checkbox"
              data-site-key="<?= Yii::$app->reCaptcha->siteKey ?>"></span>
        <?= Html::activeHiddenInput($model, 'captcha') ?>
    <?php endif; ?>

    <?= Html::submitButton('<i class="fa fa-angle-double-right" aria-hidden="true"></i> ' .
        Yii::_t('users.forms.send'), ['class' => 'btn btn-green btn-reg']); ?>

<?php else: ?>
    <?= Yii::_t('users.forms.for change password please contact administrator') ?>
<?php endif; ?>

<?php ActiveForm::end() ?>