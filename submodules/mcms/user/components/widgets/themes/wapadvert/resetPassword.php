<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

?>

<?php $form = ActiveForm::begin([
    'id' => 'password-reset-form',
    'action' => Url::to(['users/api/reset-password']),
    'options' => ['class' => 'reset-password-form login-form login-form_modal uk-form']]); ?>


    <div class="title title_mini title_modal"><?= Yii::_t('users.forms.reset_password_title') ?></div>

    <div id="reset-password" class="summary">
        <div class="login-form__input-item">
            <div class="form-group field-password custom-login-field required custom-input-pass">
                <?= $form->field($model, 'password', ['options' => ['class' => 'custom-div custom-input-pass']])
                    ->passwordInput(['placeholder' => Yii::_t('users.login.password'), 'class' => 'form-control custom-pass'])->label(false) ?>
            </div>
        </div>
        <div class="form-group custom-login-button-cont">
            <?= Html::submitButton(Yii::_t('users.forms.send'), ['class' => 'btn custom-icon-login custom-icon-login_text']); ?>
        </div>

    </div>
    <div class="modal__footer">

    </div>
<?php ActiveForm::end() ?>