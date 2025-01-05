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
]); ?>
<?= $form->field($model, 'username', ['options' => ['class' => 'form-group form-login']])->textInput(['placeholder' => Yii::_t('users.login.username_email')])->label(false) ?>
<?= $form->field($model, 'password', ['options' => ['class' => 'form-group form-password']])->passwordInput(['placeholder' => Yii::_t('users.login.password')])->label(false) ?>

<div class="row">
    <div class="col-xs-6 text-left">
        <a class="password_recovery change-form" data-target="#recovery"
           href=""><?= Yii::_t('users.login.forgot_your_password') ?></a>
    </div>
    <div class="col-xs-6 text-right">
        <?= $form->field($model, 'rememberMe', ['template' => '{input}{label}{error}', 'options' => ['class' => 'checkbox']])->checkbox([], false); ?>
    </div>
</div>
<?php if ($model->shouldUseCaptcha()) : ?>
    <?= $form->field($model, 'captcha')->widget(ReCaptcha::class)->label(false) ?>
<?php else: ?>
    <br/>
    <div id="recapcha-<?= $form->id ?>" data-site-key="<?= Yii::$app->reCaptcha->siteKey ?>"></div>
    <?= Html::activeHiddenInput($model, 'captcha') ?>
<?php endif; ?>
<?= Html::submitButton(Yii::_t('users.login.sign_in'), ['class' => 'btn']); ?>

<?php ActiveForm::end() ?>

