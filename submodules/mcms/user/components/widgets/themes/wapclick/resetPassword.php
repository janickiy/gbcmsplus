<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

?>

<?php $form = ActiveForm::begin([
    'id' => 'reset-password-form',
    'action' => Url::to(['users/api/reset-password']),
    'options' => ['class' => 'reset-password-form', 'autocomplete' => 'off']]); ?>

<p><?= Yii::_t('users.forms.please_choose_your_new_password') ?></p>

<?= $form->field($model, 'password', ['options' => ['class' => 'form-group input-password']])
    ->passwordInput(['placeholder' => Yii::_t('users.signup.password'), 'class' => 'form-control'])->label(false) ?>

<?= Html::submitButton(Yii::_t('users.forms.send'), ['class' => 'btn']); ?>

<?php ActiveForm::end() ?>

