<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

?>
<div class="login-form-top">
    <h3><?= Yii::_t('users.forms.reset_password_title') ?></h3>
</div>
<?php $form = ActiveForm::begin([
    'id' => 'reset-password-form',
    'action' => Url::to(['users/api/reset-password']),
    'options' => ['class' => 'rega-form reset-password-form']]); ?>

<?= $form->field($model, 'password')->passwordInput(['placeholder' => Yii::_t('users.login.password'),
    'class' => 'form-control password '])->label() ?>

<div class="form-group">
    <?= Html::submitButton(Yii::_t('users.forms.send'), ['class' => 'btn-reg']); ?>
</div>
<?php ActiveForm::end() ?>
