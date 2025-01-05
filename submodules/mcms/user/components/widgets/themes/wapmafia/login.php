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

<?= $form->field($model, 'username', ['options' => ['class' => 'form-group input-email']])
    ->textInput(['placeholder' => Yii::_t('users.login.username_email'), 'class' => 'form-control'])->label(false) ?>

<?= $form->field($model, 'password', ['options' => ['class' => 'form-group input-password']])
    ->passwordInput(['placeholder' => Yii::_t('users.login.password'), 'class' => 'form-control'])->label(false) ?>

<a href="#password__reset" class="pass_reset open__modal"><?= Yii::_t('users.login.forgot_your_password') ?></a>

<?php if ($model->shouldUseCaptcha()) : ?>
    <?= $form->field($model, 'captcha', ['options' => ['class' => false]])->widget(ReCaptcha::class)->label(false) ?>
<?php else: ?>
    <span id="recapcha-<?= $form->id ?>" class="checkbox" data-site-key="<?= Yii::$app->reCaptcha->siteKey ?>"></span>
    <?= Html::activeHiddenInput($model, 'captcha') ?>
<?php endif; ?>

<div class="login_submit">
    <?= Html::submitButton(Yii::_t('users.login.sign_in'), ['class' => 'btn btn-primary']); ?>
</div>
<div class="save_me">
    <div class="form-group field-loginform-rememberme">
        <?= $form->field($model, 'rememberMe', [

        ])->checkbox(['label' => Yii::_t('users.login.rememberMe')]) ?>
        <?php /*<input type="hidden" name="LoginForm[rememberMe]" value="0"><label><input type="checkbox" id="loginform-rememberme" name="Login[rememberMe]" value="1" checked=""> Запомнить меня</label><div class="help-block"></div> */ ?>
    </div>
</div>

<?php ActiveForm::end() ?>
