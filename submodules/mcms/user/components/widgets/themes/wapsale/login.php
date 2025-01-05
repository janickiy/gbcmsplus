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
    'options' => ['class' => 'login-form']
]); ?>

<?= $form->field($model, 'username')->textInput(['placeholder' => Yii::_t('users.login.username_email'),
    'class' => 'form-control custom-login'])->label(false) ?>

<?= $form->field($model, 'password')->passwordInput(['placeholder' => Yii::_t('users.login.password'),
    'class' => 'form-control custom-login'])->label(false) ?>

<?php if ($model->shouldUseCaptcha()) : ?>
    <?= $form->field($model, 'captcha')->widget(ReCaptcha::class)->label(false) ?>
<?php else: ?>
    <div id="recapcha-<?= $form->id ?>" data-site-key="<?= Yii::$app->reCaptcha->siteKey ?>"></div>
    <?= Html::activeHiddenInput($model, 'captcha') ?>
<?php endif; ?>


    <div class="form-group">
        <?= Html::submitButton(Yii::_t('users.login.sign_in'), ['class' => 'btn custom-btn-dark custom-btn-sm custom-btn-login']); ?>
    </div>

    <ul>
        <li>
            <a class="js-remember-link request-password-modal-button" href="#"
               data-scroll="rememberDrop"><?= Yii::_t('users.login.forgot_your_password') ?></a>
        </li>
    </ul>

<?php ActiveForm::end() ?>