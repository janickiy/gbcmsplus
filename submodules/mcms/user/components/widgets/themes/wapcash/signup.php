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
    'options' => ['class' => 'login-form login-form_modal']
]); ?>

    <div class="title title_mini title_modal"><?= Yii::_t('users.login.publisher_signup_form') ?></div>

<?php if ($module->isRegistrationTypeClosed()): ?>
    <div class="login-form__input-item">
        <div style="text-align: center"><?= Yii::_t('users.signup.closed') ?></div>
    </div>
<?php else: ?>
    <div class="login-form__input-item">
        <?= $form->field($model, 'email', ['options' => ['class' => 'custom-div custom-div_login']])
            ->textInput(['placeholder' => Yii::_t('users.signup.email'), 'class' => 'form-control custom-input'])->label(false) ?>
    </div>
    <div class="login-form__input-item">
        <?= $form->field($model, 'password', ['options' => ['class' => 'custom-div custom-div_pass']])
            ->passwordInput(['placeholder' => Yii::_t('users.signup.password'), 'class' => 'form-control custom-input'])->label(false) ?>
    </div>
    <div class="login-form__input-item">
        <?= $form->field($model, 'passwordRepeat', ['options' => ['class' => 'custom-div custom-div_pass']])
            ->passwordInput(['placeholder' => Yii::_t('users.signup.passwordRepeat'), 'class' => 'form-control custom-input'])->label(false) ?>
    </div>
    <div class="login-form__input-item">
        <?= $module->registrationWithLanguage()
            ? $form->field($model, 'language', ['options' => ['class' => 'custom-div custom-select']])
                ->dropDownList(['ru' => Yii::_t('users.signup.russian'), 'en' => Yii::_t('users.signup.english')],
                    ['placeholder' => Yii::_t('users.signup.language'), 'class' => 'form-control custom-input'])->label(false)
            : '' ?>
    </div>
    <div class="login-form__input-item">
        <?= $module->registrationWithCurrency()
            ? $form->field($model, 'currency', ['options' => ['class' => 'custom-div custom-select']])
                ->dropDownList($currencyList, ['placeholder' => Yii::_t('users.signup.currency'), 'class' => 'form-control custom-input'])->label(false)
            : '' ?>
    </div>
    <div class="login-form__input-item">
        <small><?= Yii::_t('users.signup.contact') ?></small>
        <?= $form->field($model, 'contact_type', ['options' => ['class' => 'custom-div custom-select']])
            ->dropDownList(UserContact::getTypes(true),
                ['placeholder' => Yii::_t('users.signup.contact_type'), 'class' => 'form-control custom-input'])->label(false) ?>
    </div>
    <div class="login-form__input-item">
        <?= $form->field($model, 'contact_data', ['options' => ['class' => 'custom-div custom-div_phone']])
            ->textInput(['placeholder' => Yii::_t('users.signup.contact_data'), 'class' => 'form-control custom-input'])->label(false) ?>
    </div>

    <div class="login-form__input-item">
        <div class="form-group">
            <?= Html::submitButton(Yii::_t('users.signup.register'), ['class' => 'btn custom-submit uk-button uk-button-large uk-button-primary']); ?>
        </div>
    </div>
<?php endif; ?>
    <div class="modal__footer">

    </div>
<?php ActiveForm::end() ?>