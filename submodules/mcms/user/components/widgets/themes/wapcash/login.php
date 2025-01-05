<?php

use yii\widgets\ActiveForm;
use yii\bootstrap\Html;
use yii\helpers\Url;
use himiklab\yii2\recaptcha\ReCaptcha;

/* @var $model \mcms\user\models\LoginForm */
?>

<?php $form = ActiveForm::begin([
    'id' => 'login-form',
    'action' => Url::to(['users/api/login']),
    'options' => ['class' => 'login-form login-form_modal']
]); ?>

<div class="title title_mini title_modal"><?= Yii::_t('users.login.login_form') ?></div>

<div class="login-form__input-item">
    <?= $form->field($model, 'username', ['options' => ['class' => 'custom-div custom-div_login']])
        ->textInput(['placeholder' => Yii::_t('users.login.username_email'), 'class' => 'form-control custom-input'])->label(false) ?>
</div>

<div class="login-form__input-item">
    <?= $form->field($model, 'password', ['options' => ['class' => 'custom-div custom-div_pass']])
        ->passwordInput(['placeholder' => Yii::_t('users.login.password'), 'class' => 'form-control custom-input'])->label(false) ?>
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
        <?= Html::submitButton(Yii::_t('users.login.sign_in_cabinet'), ['class' => 'btn custom-submit uk-button uk-button-large uk-button-primary']); ?>
    </div>
</div>
<div class="login-form__input-item">
    <?= $form->field($model, 'rememberMe', ['options' => ['class' => 'custom-checkbox custom-checkbox_unchecked']])->checkbox(); ?>
</div>
<div class="modal__footer uk-flex uk-flex-middle uk-flex-center">
    <a href="#" class="modal__footer-link modal__footer-link_main"
       data-uk-modal="{target: '#reg-modal', center:true}"><?= Yii::_t('users.login.publisher_signup_form') ?></a>
    <a href="#" class="js-gorem-form modal__footer-link"
       data-uk-modal="{target: '#remember-modal', center:true}"><?= Yii::_t('users.login.forgot_your_password') ?></a>
</div>

<?php ActiveForm::end() ?>

