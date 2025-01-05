<?php
/**
 * @var array $currencyList
 * @var $model
 */

use yii\widgets\ActiveForm;
use yii\bootstrap\Html;
use yii\helpers\Url;

/** @var \mcms\user\Module $module */
$module = Yii::$app->getModule('users');

$form = ActiveForm::begin([
    'id' => 'signup-form',
    'action' => Url::to(['users/api/signup']),
    'options' => ['class' => 'text-center', 'autocomplete' => 'off']
]);

if ($module->isRegistrationTypeClosed()) {
    echo Yii::_t('users.signup.closed');
} else {

    echo $form->field($model, 'email', ['options' => ['class' => 'form-group input-email']])
        ->textInput(['placeholder' => Yii::_t('users.signup.email'), 'class' => 'form-control'])->label(false);

    echo $form->field($model, 'password', ['options' => ['class' => 'form-group input-password']])
        ->passwordInput(['placeholder' => Yii::_t('users.signup.password'), 'class' => 'form-control'])->label(false);

    echo $form->field($model, 'passwordRepeat', ['options' => ['class' => 'form-group input-password']])
        ->passwordInput(['placeholder' => Yii::_t('users.signup.passwordRepeat'), 'class' => 'form-control'])->label(false);

    echo $form->field($model, 'skype', ['options' => ['class' => 'form-group input-contacts']])
        ->textInput(['placeholder' => Yii::_t('users.signup.skype'), 'class' => 'form-control'])->label(false);

    echo $module->registrationWithLanguage()
        ? $form->field($model, 'language', ['options' => ['class' => 'form-group select input-language']])
            ->dropDownList(['ru' => Yii::_t('users.signup.russian'), 'en' => Yii::_t('users.signup.english')],
                ['placeholder' => Yii::_t('users.signup.language')])->label(false)
        : '';

    echo $module->registrationWithCurrency()
        ? $form->field($model, 'currency', ['options' => ['class' => 'form-group select input-currency']])
            ->dropDownList($currencyList, ['placeholder' => Yii::_t('users.signup.currency')])->label(false)
        : '';

    echo Html::tag(
        'div',
        Html::submitButton(Yii::_t('users.signup.register'), ['class' => 'btn btn-primary']),
        ['class' => 'modal-footer']
    );
}
ActiveForm::end() ?>