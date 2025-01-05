<?php

use yii\widgets\ActiveForm;
use yii\bootstrap\Html;
use yii\helpers\Url;
use mcms\common\helpers\ArrayHelper;

/* @var array $currencyList */
/* @var \mcms\user\Module $module */
$module = Yii::$app->getModule('users');
?>

<?php $form = ActiveForm::begin([
    'id' => $formId,
    'action' => Url::to(['users/api/signup']),
    'options' => ['class' => 'rega-form']
]); ?>


<?php if ($module->isRegistrationTypeClosed()): ?>
    <div style="text-align: center"><?= Yii::_t('users.signup.closed') ?></div>
<?php else: ?>
    <p><?= ArrayHelper::getValue($options, 'modal_text') ?></p>
    <div class="rega-form_wrap<?= ArrayHelper::getValue($options, 'hidden-xs') ? ' hidden-xs' : '' ?>">
        <?= $form->field($model, 'email')->textInput(['placeholder' => Yii::_t('users.signup.email'), 'class' => 'form-control email'])->label() ?>

        <?= $form->field($model, 'skype')->textInput(['placeholder' => Yii::_t('users.signup.skype'), 'class' => 'form-control icq'])->label() ?>

        <?= $form->field($model, 'password')->passwordInput(['placeholder' => Yii::_t('users.signup.password'), 'class' => 'form-control password'])->label() ?>

        <?= $form->field($model, 'passwordRepeat')->passwordInput(['placeholder' => Yii::_t('users.signup.passwordRepeat'), 'class' => 'form-control password2'])->label() ?>

        <?= $module->registrationWithLanguage()
            ? $form->field($model, 'language')->dropDownList(['ru' => Yii::_t('users.signup.russian'), 'en' => Yii::_t('users.signup.english')],
                ['class' => 'form-control lang'])->label()
            : '' ?>
        <?= $module->registrationWithCurrency()
            ? $form->field($model, 'currency')->dropDownList($currencyList,
                ['class' => 'form-control money'])->label()
            : '' ?>
    </div>

    <br>
    <div class="form-group hidden-xs">
        <?= Html::submitButton(Yii::_t('users.signup.register'), ['class' => 'btn-reg']); ?>
    </div>
    <div class="form-group visible-xs">
        <a href="#modal-form_rega" class="btn-reg popup-with-move-anim"><?= Yii::_t('users.signup.register') ?></a>
    </div>
    <br>
    <div class="login-link">
        <span class=" hidden-xs"><?= Yii::_t('users.signup.already_have_an_account') ?>?</span>
        <a href="#modal-form_login"
           class="link-login popup-with-move-anim"><?= Yii::_t('users.login.sign_in_account') ?></a>
    </div>
<?php endif; ?>


<?php ActiveForm::end() ?>

