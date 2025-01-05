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
    'options' => ['class' => 'login-form uk-form uk-flex uk-flex-space-between']
]); ?>

<div class="login-form__input-item login-form__input-item_head">
    <div class="form-group field-username required custom-input-login">
        <?= $form->field($model, 'username', ['options' => ['class' => 'custom-input-login']])
            ->textInput(['placeholder' => Yii::_t('users.login.username_email'), 'class' => 'form-control required', 'tabindex' => 1])->label(false) ?>
    </div>
    <a href="#reg-modal" class="link link_login uk-text-bold only-not-s register-modal-button"
       data-uk-modal><?= Yii::_t('users.login.publisher_signup_form') ?></a>
</div>
<div class="login-form__input-item login-form__input-item_head">
    <div class="form-group field-username required custom-input-pass">
        <?= $form->field($model, 'password', ['options' => ['class' => 'custom-input-pass']])
            ->passwordInput(['placeholder' => Yii::_t('users.login.password'), 'class' => 'form-control', 'tabindex' => 2])->label(false) ?>
    </div>
    <a href="#remember-modal" class="link link_dotted link_login"
       data-uk-modal><?= Yii::_t('users.login.forgot_your_password') ?></a>
    <a style="display: none" id="reset-modal-button" href="#reset-modal" data-uk-modal></a>
    <a style="display: none" id="success-modal-button" href="#success-modal" data-uk-modal></a>
    <a style="display: none" id="fail-modal-button" href="#fail-modal" data-uk-modal></a>
</div>
<div class="login-form__submit-item form-group">
    <?= Html::submitButton(Yii::_t('users.login.sign_in'), ['class' => 'btn custom-icon-login custom-icon-login_icon custom-icon-login_icon-big', 'tabindex' => 3]); ?>
    <?php if ($model->shouldUseCaptcha()) : ?>
        <?= $form->field($model, 'captcha')->widget(ReCaptcha::class)->label(false) ?>
    <?php else: ?>
        <span id="recapcha-<?= $form->id ?>" data-site-key="<?= Yii::$app->reCaptcha->siteKey ?>"></span>
        <?= Html::activeHiddenInput($model, 'captcha') ?>
    <?php endif; ?>
</div>

<?php ActiveForm::end() ?>

