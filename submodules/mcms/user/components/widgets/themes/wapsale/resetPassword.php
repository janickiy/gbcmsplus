<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

?>

<?php $form = ActiveForm::begin([
    'id' => 'password-reset-form',
    'action' => Url::to(['users/api/reset-password']),
    'options' => ['class' => 'reset-password-form']]); ?>

<p><?= Yii::_t('users.forms.please_choose_your_new_password') ?></p>

<?= $form->field($model, 'password')->passwordInput(['placeholder' => Yii::_t('users.login.password')])->label(false) ?>

<div class="form-group"><?= Html::submitButton(Yii::_t('users.forms.send'), ['class' => 'btn custom-btn-dark custom-btn-sm custom-btn-login']); ?></div>

<?php ActiveForm::end() ?>

