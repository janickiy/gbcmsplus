<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

?>

<?php $form = ActiveForm::begin([
    'id' => 'reset-password-form',
    'action' => Url::to(['users/api/reset-password']),
    'options' => ['class' => 'form-modal reset-password-form', 'autocomplete' => 'off']]); ?>

<p><?= Yii::_t('users.forms.please_choose_your_new_password') ?></p>

<div class="form-group field-resetpasswordform-password">
    <div class="form-group-icon">
        <i class="fa fa-unlock-alt" aria-hidden="true"></i>
    </div>
    <div class="form-group-field">
        <?= Html::activePasswordInput($model, 'password', ['class' => 'form-control', 'placeholder' => Yii::_t('users.signup.password')]) ?>
    </div>
    <div class="help-block"></div>
</div>

<?= Html::submitButton('<i class="fa fa-check-circle" aria-hidden="true"></i> ' .
    Yii::_t('users.forms.send'), ['class' => 'btn btn-green btn-reg']); ?>

<?php ActiveForm::end() ?>

