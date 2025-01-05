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
    'options' => ['class' => 'login-form login-form_modal uk-form']
]); ?>

<div class="title title_mini title_modal"><?= Yii::_t('users.login.publisher_signup_form') ?></div>

<?php if ($module->isRegistrationTypeClosed()): ?>
    <div style="text-align: center"><?= Yii::_t('users.signup.closed') ?></div>
<?php else: ?>
    <div id="signup" class="summary">
        <div class="uk-grid uk-grid-small uk-grid-width-medium-1-2">
            <div class="login-form__input-item">
                <div class="form-group field-email custom-login-field required custom-input-login">
                    <?= $form->field($model, 'email', ['options' => ['class' => ' custom-input-login']])
                        ->textInput(['placeholder' => Yii::_t('users.signup.email'), 'class' => 'form-control custom-login'])->label(false) ?>
                </div>
            </div>
            <div class="login-form__input-item">
                <div class="form-group field-password custom-password-field required custom-input-pass">
                    <?= $form->field($model, 'password', ['options' => ['class' => 'custom-input-pass']])
                        ->passwordInput(['placeholder' => Yii::_t('users.signup.password'), 'class' => 'form-control custom-password'])->label(false) ?>
                </div>
            </div>
            <div class="login-form__input-item">
                <div class="form-group field-passwordRepeat custom-password-field required custom-input-pass">
                    <?= $form->field($model, 'passwordRepeat', ['options' => ['class' => 'custom-div custom-div_pass']])
                        ->passwordInput(['placeholder' => Yii::_t('users.signup.passwordRepeat'), 'class' => 'form-control custom-password'])->label(false) ?>
                </div>
            </div>
            <div class="login-form__input-item">
                <div class="form-group field-skype required custom-input-phone">
                    <?= $form->field($model, 'skype', ['options' => ['class' => ' custom-input-phone']])
                        ->textInput(['placeholder' => Yii::_t('users.signup.skype'), 'class' => 'form-control custom-input'])->label(false) ?>
                </div>
            </div>
            <?php if ($module->registrationWithLanguage()): ?>
                <div class="login-form__input-item">
                    <?= $form->field($model, 'language', ['options' => ['class' => 'custom-select']])
                        ->dropDownList(['ru' => Yii::_t('users.signup.russian'), 'en' => Yii::_t('users.signup.english')],
                            ['placeholder' => Yii::_t('users.signup.language'), 'class' => 'form-control custom-input'])->label(false) ?>
                </div>
            <?php endif; ?>
            <?php if ($module->registrationWithCurrency()): ?>
                <div class="login-form__input-item">
                    <?= $form->field($model, 'currency', ['options' => ['class' => 'custom-select']])
                        ->dropDownList($currencyList, ['placeholder' => Yii::_t('users.signup.currency'), 'class' => 'form-control custom-input'])->label(false) ?>
                </div>
            <?php endif; ?>
            <div class="form-group custom-login-button-cont">
                <?= Html::submitButton(Yii::_t('users.signup.register'), ['class' => 'btn custom-icon-login custom-icon-login_text']); ?>
            </div>
        </div>
    </div>


<?php endif; ?>
<?php ActiveForm::end() ?>
