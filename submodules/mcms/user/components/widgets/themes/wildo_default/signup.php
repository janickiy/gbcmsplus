<?php

use mcms\user\models\UserContact;
use yii\widgets\ActiveForm;
use yii\bootstrap\Html;
use yii\helpers\Url;
use himiklab\yii2\recaptcha\ReCaptcha;

/* @var array $currencyList */
/* @var \mcms\user\Module $module */
$module = Yii::$app->getModule('users');

?>

<?php $form = ActiveForm::begin([
    'id' => 'signup-form',
    'action' => Url::to(['users/api/signup']),
    'options' => ['class' => 'modal-content']
]); ?>
<?php if ($module->isRegistrationTypeClosed()): ?>
    <p><?= Yii::_t('users.signup.closed') ?></p>
<?php else: ?>
    <?= $form->field($model, 'email', ['options' => ['class' => 'form-group form-login']])->textInput(['placeholder' => Yii::_t('users.signup.email')])->label(false) ?>

    <?= $form->field($model, 'password', ['options' => ['class' => 'form-group form-password']])->passwordInput(['placeholder' => Yii::_t('users.signup.password')])->label(false) ?>

    <?= $form->field($model, 'passwordRepeat', ['options' => ['class' => 'form-group form-password']])->passwordInput(['placeholder' => Yii::_t('users.signup.passwordRepeat')])->label(false) ?>

    <?= $form->field($model, 'contact_type', ['options' => ['class' => 'form-group select input-language']])
        ->dropDownList(UserContact::getTypes(true))->label(false) ?>

    <?= $form->field($model, 'contact_data', ['options' => ['class' => 'form-group input-contacts']])
        ->textInput(['class' => 'form-control'])->label(false) ?>

    <?= $module->registrationWithCurrency()
        ? $form->field($model, 'currency', ['options' => ['class' => 'form-group form-wallet']])->dropDownList($currencyList, ['placeholder' => Yii::_t('users.signup.currency')])->label(false)
        : '' ?>

    <?= $module->registrationWithLanguage()
        ? $form->field($model, 'language', ['options' => ['class' => 'form-group form-lang']])->dropDownList(['ru' => Yii::_t('users.signup.russian'), 'en' => Yii::_t('users.signup.english')],
            ['placeholder' => Yii::_t('users.signup.language')])->label(false)
        : '' ?>
    <?= $form->field($model, 'captcha', ['template' => '{input}', 'inputOptions' => ['required' => 'required']])->widget(ReCaptcha::class, [
        'widgetOptions' => [
            'id' => 're-captcha-signup-form',
        ]
    ])->label(false) ?>
    <div class="form-group col-xs-12 text-left">
        <?= $form->field($model, 'agreement', ['template' => '{input}{label}' .
            Html::a(Yii::_t('users.signup.privacy_policy'), 'https://wildo.click/PP.html', ['target' => '_blank', 'class' => 'agreement']),
            'options' => ['class' => 'checkbox']])->checkbox([], false)
        ?>
    </div>
    <?= Html::submitButton(Yii::_t('users.signup.register'), ['class' => 'btn btn-reg disabled', 'disabled' => 'disabled']); ?>
<?php endif; ?>
<?php ActiveForm::end() ?>