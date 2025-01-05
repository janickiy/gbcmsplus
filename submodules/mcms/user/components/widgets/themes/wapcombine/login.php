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
    'options' => ['autocomplete' => 'off'],
]); ?>

<?= $form->field($model, 'username', ['options' => ['class' => 'form-group custom-field-login-username icon-user']])
    ->textInput(['placeholder' => Yii::_t('users.login.username_email'), 'class' => 'form-control'])->label(false) ?>

<?= $form->field($model, 'password', ['options' => ['class' => 'form-group custom-field-login-password icon-pass']])
    ->passwordInput(['placeholder' => Yii::_t('users.login.password'), 'class' => 'form-control'])->label(false) ?>

    <div class="lost">
        <span id="showlost"><?= Yii::_t('users.login.forgot_your_password') ?></span>
    </div>

<?php if ($model->shouldUseCaptcha()) : ?>
    <?= $form->field($model, 'captcha', ['options' => ['class' => false]])->widget(ReCaptcha::class)->label(false) ?>
<?php else: ?>
    <span id="recapcha-<?= $form->id ?>" class="checkbox" data-site-key="<?= Yii::$app->reCaptcha->siteKey ?>"></span>
    <?= Html::activeHiddenInput($model, 'captcha') ?>
<?php endif; ?>

<?= Html::submitButton(Yii::_t('users.login.sign_in')); ?>

    <div class="form-group  custom-field-login-rememberme">
        <?= $form->field($model, 'rememberMe', [
            'options' => ['class' => ''],
            'template' => '{input} <label for="loginform-rememberme">' . Yii::_t('users.login.rememberMe') . '</label>',
        ])->checkbox(['label' => null]); ?>
    </div>

    <div class="regado"><?= Yii::_t('users.signup.need_an_account') ?>
        <span><?= Yii::_t('users.signup.register') ?></span>
    </div>

<?php ActiveForm::end() ?>