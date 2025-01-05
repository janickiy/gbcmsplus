<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

?>

<?php $form = ActiveForm::begin([
    'id' => 'password-reset-form',
    'action' => Url::to(['users/api/reset-password']),
    'options' => ['class' => 'modal-content reset-password-form']]); ?>

<?= $form->field($model, 'password', ['options' => ['class' => 'form-group form-password']])->passwordInput(['placeholder' => Yii::_t('users.login.password')])->label(false) ?>
<?= Html::submitButton(Yii::_t('users.forms.send'), ['class' => 'btn']); ?>
<?php ActiveForm::end() ?>
