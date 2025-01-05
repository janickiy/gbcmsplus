<?php

use himiklab\yii2\recaptcha\ReCaptcha;
use yii\widgets\ActiveForm;
use yii\bootstrap\Html;
use yii\helpers\Url;

/* @var $model \mcms\user\models\LoginForm */
/* @var $this \mcms\common\web\View */
$requiredMessage = Yii::_t("users.signup.required_field");
$js = <<<JS
window.recaptchaRequiredCaption = '{$requiredMessage}';
JS;

$this->registerJs($js, \mcms\common\web\View::POS_BEGIN);

?>

<?php $form = ActiveForm::begin([
    'id' => 'login-form',
    'action' => Url::to(['users/api/login']),
    'options' => ['class' => 'new_panel', 'autocomplete' => 'off'],
]); ?>

<?= $form->field($model, 'username', ['options' => ['class' => 'form-group input-email']])
    ->textInput(['placeholder' => Yii::_t('users.login.username_email'), 'class' => 'form-control'])->label(false) ?>

<?= $form->field($model, 'password', ['options' => ['class' => 'form-group input-password']])
    ->passwordInput(['placeholder' => Yii::_t('users.login.password'), 'class' => 'form-control'])->label(false) ?>

<div class="pass_remember">
    <div class="row">
        <div class="col-xs-6">
            <a data-modal="recovery" class="change-modal" href=""><?= Yii::_t('users.login.forgot_your_password') ?></a>
        </div>
        <div class="col-xs-6 text-right">
            <div class="form-group checkbox">
                <?= $form->field($model, 'rememberMe', [
                    'options' => ['class' => ''],
                    'template' => '{input}<label for="loginform-rememberme">' . Yii::_t('users.login.rememberMe') . '</label>',
                ])->checkbox(['label' => null]); ?>
            </div>
        </div>
    </div>
</div>

<?php if ($model->shouldUseCaptcha()) : ?>
    <?= $form->field($model, 'captcha', ['options' => ['class' => false]])->widget(ReCaptcha::class, [
        'jsCallback' => '(function(){$(document).trigger("captchaValid", true);})',
        'jsExpiredCallback' => '(function(){$(document).trigger("captchaValid", false);})',
        'widgetOptions' => ['id' => 'recapcha-login-form']
    ])->label(false) ?>
<?php else: ?>
    <span id="recapcha-<?= $form->id ?>" class="checkbox" data-site-key="<?= Yii::$app->reCaptcha->siteKey ?>"></span>
    <?= Html::activeHiddenInput($model, 'captcha') ?>
<?php endif; ?>

<?= Html::submitButton(Yii::_t('users.login.sign_in'), ['class' => 'btn']); ?>

<?php ActiveForm::end() ?>
