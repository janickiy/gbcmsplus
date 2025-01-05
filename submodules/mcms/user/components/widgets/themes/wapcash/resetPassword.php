<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

?>

<?php $form = ActiveForm::begin([
    'id' => 'password-reset-form',
    'action' => Url::to(['users/api/reset-password']),
    'options' => ['class' => 'reset-password-form']]); ?>


<div class="title title_mini title_modal"><?= Yii::_t('users.forms.reset_password_title') ?></div>

<div id="reset-password" class="summary">
    <div class="login-form__input-item">
        <?= $form->field($model, 'password', ['options' => ['class' => 'custom-div custom-div_pass']])
            ->passwordInput(['placeholder' => Yii::_t('users.login.password'), 'class' => 'form-control custom-input'])->label(false) ?>
    </div>
    <div class="login-form__input-item">
        <div class="form-group">
            <?= Html::submitButton(Yii::_t('users.forms.send'), ['class' => 'btn custom-submit uk-button uk-button-large uk-button-primary']); ?>
        </div>
    </div>
</div>
<div class="modal__footer">

</div>
<?php ActiveForm::end() ?>
