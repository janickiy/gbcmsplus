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
    'id' => $formId,
    'action' => Url::to(['users/api/signup']),
    'options' => ['class' => 'text-center', 'autocomplete' => 'off']
]); ?>

<?php if ($module->isRegistrationTypeClosed()): ?>
    <?= Yii::_t('users.signup.closed') ?>
<?php else: ?>

    <div class="row">
        <div class="col-12">
            <?= $form->field($model, 'email', ['options' => ['class' => 'form-group input-email']])
                ->textInput(['class' => 'form-control']) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-6">
            <?= $form->field($model, 'password', ['options' => ['class' => 'form-group input-password']])
                ->passwordInput(['class' => 'form-control']) ?>
        </div>
        <div class="col-6">
            <?= $form->field($model, 'passwordRepeat', ['options' => ['class' => 'form-group input-password']])
                ->passwordInput(['class' => 'form-control']) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-6">
            <?= $module->registrationWithLanguage()
                ? $form->field($model, 'language', ['options' => ['class' => 'form-group select input-language']])
                    ->dropDownList(['ru' => Yii::_t('users.signup.russian'), 'en' => Yii::_t('users.signup.english')])
                : '' ?>
        </div>
        <div class="col-6">
            <?= $module->registrationWithCurrency()
                ? $form->field($model, 'currency')
                    ->dropDownList($currencyList)
                : '' ?>
        </div>
    </div>
    <div class="row">
        <div class="col-6">
            <?= $form->field($model, 'contact_type', ['options' => ['class' => 'form-group select input-language']])
                ->dropDownList(UserContact::getTypes(true)) ?>
        </div>
        <div class="col-6">
            <?= $form->field($model, 'contact_data', ['options' => ['class' => 'form-group input-contacts']])
                ->textInput(['class' => 'form-control']) ?>
        </div>
    </div>
    <div class="align-center">
        <?= Html::submitButton(Yii::_t('users.signup.register'), ['class' => 'btn-generic btn-reg']); ?>
    </div>


<?php endif; ?>
<?php ActiveForm::end() ?>