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
    'options' => ['class' => 'modal-content']
]); ?>
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
        <h1 class="modal-title text-center" id="myModalLabel"><?= Yii::_t('users.login.publisher_signup_form') ?></h1>
    </div>
<?php if ($module->isRegistrationTypeClosed()): ?>
    <div class="modal-body wp-caption-text"><?= Yii::_t('users.signup.closed') ?></div>
    <div id="signup-footer" class="modal-footer text-center"></div>
<?php else: ?>
    <div id="signup" class="modal-body">

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

    </div>
    <div id="signup-footer" class="modal-footer text-center">
        <?= Html::submitButton(Yii::_t('users.signup.register'), ['class' => 'btn custom-button custom-red-btn']); ?>
    </div>
<?php endif; ?>
<?php ActiveForm::end() ?>