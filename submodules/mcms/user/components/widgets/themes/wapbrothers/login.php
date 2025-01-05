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
    'options' => ['class' => 'rega-form']
]); ?>

<?= $form->field($model, 'username')
    ->textInput(['placeholder' => Yii::_t('users.login.username_email'), 'class' => 'form-control email'])->label(Yii::_t('users.login.username_email')) ?>

<?= $form->field($model, 'password')
    ->passwordInput(['placeholder' => Yii::_t('users.login.password'), 'class' => 'form-control password'])->label() ?>

<?php if ($model->shouldUseCaptcha()) : ?>
    <?= $form->field($model, 'captcha')->widget(ReCaptcha::class)->label(false) ?>
<?php else: ?>
    <span id="recapcha-<?= $form->id ?>" data-site-key="<?= Yii::$app->reCaptcha->siteKey ?>"></span>
    <?= Html::activeHiddenInput($model, 'captcha') ?>
<?php endif; ?>

    <br>
    <div class="form-group">
        <?= Html::submitButton(Yii::_t('users.login.sign_in_account'), ['class' => 'btn-reg']); ?>
    </div>

    <br>
    <div class="login-link">
        <span class=" hidden-xs"><?= Yii::_t('users.login.forgot_your_password') ?></span>
        <a href="#modal-form_pass" class="link-login popup-with-move-anim"><?= Yii::_t('users.forms.restore') ?></a>
    </div>

<?php ActiveForm::end() ?>