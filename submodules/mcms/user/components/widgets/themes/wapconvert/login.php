<?php

use himiklab\yii2\recaptcha\ReCaptcha;
use yii\bootstrap\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $model \mcms\user\models\LoginForm */
?>

<?php $form = ActiveForm::begin([
    'id' => 'login-form',
    'action' => Url::to(['users/api/login']),
    'options' => ['autocomplete' => 'off', 'class' => 'form-modal'],
]); ?>

<?= $form->field($model, 'username', [
    'template' => "<div class='form-group-icon'>@</div><div class='form-group-field'>{input}</div>{hint}\n{error}",
    'options' => [
        'class' => 'form-group form-login',
    ]
])->textInput([
    'placeholder' => Yii::_t('users.login.username_email'),
])->label(false) ?>

<?= $form->field($model, 'password', [
    'template' => "<div class='form-group-icon'><i class='fa fa-unlock-alt' aria-hidden='true'></i></div>
    <div class='form-group-field'>{input}</div>{hint}\n{error}",
    'options' => [
        'class' => 'form-group form-password',
    ]
])->passwordInput([
    'placeholder' => Yii::_t('users.login.password'),
])->label(false) ?>

    <ul class="form-modal-memory">
        <li><a class="js-open-passwordresetrequest" href="#"><?= Yii::_t('users.login.forgot_your_password') ?></a></li>
        <li>
            <label><?= $form->field($model, 'rememberMe', ['options' => ['class' => '']])->checkbox(['label' => Yii::_t('users.login.rememberMe')]) ?></label>
        </li>
    </ul>

<?php if ($model->shouldUseCaptcha()) : ?>
    <?= $form->field($model, 'captcha', ['options' => ['class' => false]])->widget(ReCaptcha::class)->label(false) ?>
<?php else: ?>
    <span id="recapcha-<?= $form->id ?>" class="checkbox" data-site-key="<?= Yii::$app->reCaptcha->siteKey ?>"></span>
    <?= Html::activeHiddenInput($model, 'captcha') ?>
<?php endif; ?>

    <div class="login_submit">
        <?= Html::submitButton(
            '<i class="fa fa-angle-double-right" aria-hidden="true"></i>' .
            Yii::_t('users.login.sign_in'), ['class' => 'btn btn-green btn-reg']); ?>
    </div>

    <ul class="form-modal-link">
        <li><?= Yii::_t('users.login.need_an_account') ?></li>
        <li><a href="#" class="js-open-reg"><?= Yii::_t('users.login.publisher_signup_form') ?></a></li>
    </ul>

<?php ActiveForm::end() ?>