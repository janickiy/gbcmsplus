<?php

use yii\widgets\ActiveForm;
use yii\bootstrap\Html;
use yii\helpers\Url;

/* @var array $currencyList */
/* @var \mcms\user\Module $module */
$module = Yii::$app->getModule('users');
?>

<?php $form = ActiveForm::begin([
    'id' => 'signup-form',
    'action' => Url::to(['users/api/signup']),
    'options' => ['autocomplete' => 'off']
]); ?>

<?php if ($module->isRegistrationTypeClosed()): ?>
    <?= Yii::_t('users.signup.closed') ?>
<?php else: ?>

    <?= $form->field($model, 'email', ['options' => ['class' => 'form-group custom-field-partners-username icon-mail']])
        ->textInput(['placeholder' => Yii::_t('users.signup.email'), 'class' => 'form-control'])->label(false) ?>

    <?= $form->field($model, 'password', ['options' => ['class' => 'form-group custom-field-partners-password icon-pass']])
        ->passwordInput(['placeholder' => Yii::_t('users.signup.password'), 'class' => 'form-control'])->label(false) ?>

    <?= $form->field($model, 'passwordRepeat', ['options' => ['class' => 'form-group custom-field-partners-password icon-pass']])
        ->passwordInput(['placeholder' => Yii::_t('users.signup.passwordRepeat'), 'class' => 'form-control'])->label(false) ?>

    <?= $module->registrationWithCurrency()
        ? $form->field($model, 'currency', ['options' => ['class' => 'form-group select custom-select_money icon-currency']])
            ->dropDownList($currencyList, ['placeholder' => Yii::_t('users.signup.currency')])->label(false)
        : '' ?>

    <?= $module->registrationWithLanguage()
        ? $form->field($model, 'language', ['options' => ['class' => 'form-group select custom-select_lang icon-country']])
            ->dropDownList(['ru' => Yii::_t('users.signup.russian'), 'en' => Yii::_t('users.signup.english')],
                ['placeholder' => Yii::_t('users.signup.language')])->label(false)
        : '' ?>

    <?= $form->field($model, 'skype', ['options' => ['class' => 'form-group custom-field-partners-contact icon-messangers']])
        ->textInput(['placeholder' => Yii::_t('users.signup.skype'), 'class' => 'form-control'])->label(false) ?>

    <?= Html::submitButton(Yii::_t('users.signup.register'), ['class' => 'btn']); ?>

<?php endif; ?>
<?php ActiveForm::end() ?>