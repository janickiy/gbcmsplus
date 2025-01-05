<?php

use himiklab\yii2\recaptcha\ReCaptcha;
use mcms\user\models\UserContact;
use yii\widgets\ActiveForm;
use yii\bootstrap\Html;
use yii\helpers\Url;

/* @var array $currencyList */
/* @var \mcms\user\Module $module */
/* @var $this \mcms\common\web\View */
$requiredMessage = Yii::_t("users.signup.required_field");
$js = <<<JS
window.recaptchaRequiredCaption = '{$requiredMessage}';
JS;

$this->registerJs($js, \mcms\common\web\View::POS_BEGIN);

$module = Yii::$app->getModule('users');

?>

<?php $form = ActiveForm::begin([
    'id' => 'signup-form',
    'action' => Url::to(['users/api/signup']),
    'options' => ['class' => 'text-center', 'autocomplete' => 'off']
]); ?>

<?php if ($module->isRegistrationTypeClosed()): ?>
    <?= Yii::_t('users.signup.closed') ?>
<?php else: ?>

    <?= $form->field($model, 'email', ['options' => ['class' => 'form-group input-email']])
        ->textInput(['placeholder' => Yii::_t('users.signup.email'), 'class' => 'form-control'])->label(false) ?>

    <?= $form->field($model, 'password', ['options' => ['class' => 'form-group input-password']])
        ->passwordInput(['placeholder' => Yii::_t('users.signup.password'), 'class' => 'form-control'])->label(false) ?>

    <?= $form->field($model, 'passwordRepeat', ['options' => ['class' => 'form-group input-password']])
        ->passwordInput(['placeholder' => Yii::_t('users.signup.passwordRepeat'), 'class' => 'form-control'])->label(false) ?>

    <?= $module->registrationWithLanguage()
        ? $form->field($model, 'language', ['options' => ['class' => 'form-group select input-language']])
            ->dropDownList(['ru' => Yii::_t('users.signup.russian'), 'en' => Yii::_t('users.signup.english')],
                ['placeholder' => Yii::_t('users.signup.language')])->label(false)
        : '' ?>

    <?= $module->registrationWithCurrency()
        ? $form->field($model, 'currency', ['options' => ['class' => 'form-group select input-currency']])
            ->dropDownList($currencyList, ['placeholder' => Yii::_t('users.signup.currency')])->label(false)
        : '' ?>

    <?= $form->field($model, 'contact_type', ['options' => ['class' => 'form-group select input-contacts']])
        ->dropDownList(UserContact::getTypes(true),
            ['placeholder' => Yii::_t('users.signup.contact_type')])->label(false) ?>
    <?= $form->field($model, 'contact_data', ['options' => ['class' => 'form-group input-contacts']])
        ->textInput(['placeholder' => Yii::_t('users.signup.contact_data'), 'class' => 'form-control'])->label(false) ?>

    <?php if ($model->isRecaptchaValidator) : ?>
        <?= $form->field($model, 'captcha', ['options' => ['class' => false]])->widget(ReCaptcha::class, [
            'jsCallback' => '(function(){$(document).trigger("captchaValid", true);})',
            'jsExpiredCallback' => '(function(){$(document).trigger("captchaValid", false);})',
            'widgetOptions' => ['id' => 'recapcha-signup-form']
        ])->label(false) ?>
    <?php endif; ?>


    <?= Html::submitButton(Yii::_t('users.signup.register'), ['class' => 'btn']); ?>

<?php endif; ?>
<?php ActiveForm::end() ?>
