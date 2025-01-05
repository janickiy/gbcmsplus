<?php

use mcms\user\models\UserContact;
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
    'options' => ['class' => 'modal-content']
]); ?>
<?php if ($module->isRegistrationTypeClosed()): ?>
    <p><?= Yii::_t('users.signup.closed') ?></p>
<?php else: ?>
    <?= $form->field($model, 'email', ['options' => ['class' => 'form-group form-login']])->textInput(['placeholder' => Yii::_t('users.signup.username')])->label(false) ?>

    <?= $form->field($model, 'email', ['options' => ['class' => 'form-group form-login']])->textInput(['placeholder' => Yii::_t('users.signup.email')])->label(false) ?>

    <?= $form->field($model, 'password', ['options' => ['class' => 'form-group form-password']])->passwordInput(['placeholder' => Yii::_t('users.signup.password')])->label(false) ?>

    <?= $form->field($model, 'passwordRepeat', ['options' => ['class' => 'form-group form-password']])->passwordInput(['placeholder' => Yii::_t('users.signup.passwordRepeat')])->label(false) ?>

    <?= $module->registrationWithCurrency()
        ? $form->field($model, 'currency', ['options' => ['class' => 'form-group form-wallet']])->dropDownList($currencyList, ['placeholder' => Yii::_t('users.signup.currency')])->label(false)
        : '' ?>

    <?= $module->registrationWithLanguage()
        ? $form->field($model, 'language', ['options' => ['class' => 'form-group form-lang']])->dropDownList(['ru' => Yii::_t('users.signup.russian'), 'en' => Yii::_t('users.signup.english')],
            ['placeholder' => Yii::_t('users.signup.language')])->label(false)
        : '' ?>

    <?= $form->field($model, 'contact_type', ['options' => ['class' => 'form-group form-contacts']])
        ->dropDownList(UserContact::getTypes(true), ['placeholder' => Yii::_t('users.signup.contact_type')])
        ->label(false) ?>
    <?= $form->field($model, 'contact_data', ['options' => ['class' => 'form-group form-contacts']])
        ->textInput(['placeholder' => Yii::_t('users.signup.contact_data')])
        ->label(false) ?>

    <?= Html::submitButton(Yii::_t('users.signup.register'), ['class' => 'btn']); ?>
<?php endif; ?>
<?php ActiveForm::end() ?>