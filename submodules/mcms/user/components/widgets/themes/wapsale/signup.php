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
    'options' => ['class' => 'text-center']
]); ?>

<?php if ($module->isRegistrationTypeClosed()): ?>
    <?= Yii::_t('users.signup.closed') ?>
<?php else: ?>

    <?= $form->field($model, 'email')->textInput(['placeholder' => Yii::_t('users.signup.email')])->label(false) ?>

    <?= $form->field($model, 'password')->passwordInput(['placeholder' => Yii::_t('users.signup.password')])->label(false) ?>

    <?= $form->field($model, 'passwordRepeat')->passwordInput(['placeholder' => Yii::_t('users.signup.passwordRepeat')])->label(false) ?>

    <?= $form->field($model, 'skype')->textInput(['placeholder' => Yii::_t('users.signup.skype')])->label(false) ?>

    <?= $module->registrationWithLanguage()
        ? $form->field($model, 'language')->dropDownList(['ru' => Yii::_t('users.signup.russian'), 'en' => Yii::_t('users.signup.english')],
            ['placeholder' => Yii::_t('users.signup.language')])->label(false)
        : '' ?>

    <?= $module->registrationWithCurrency()
        ? $form->field($model, 'currency')->dropDownList($currencyList, ['placeholder' => Yii::_t('users.signup.currency')])->label(false)
        : '' ?>
    <div class="form-group">
        <?= Html::submitButton(Yii::_t('users.signup.register'), ['class' => 'btn custom-btn-success']); ?>
    </div>

<?php endif; ?>
<?php ActiveForm::end() ?>
