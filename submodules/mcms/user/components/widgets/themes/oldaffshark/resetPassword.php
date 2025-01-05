<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

?>

<?php $form = ActiveForm::begin([
    'id' => 'password-reset-form',
    'action' => Url::to(['users/api/reset-password']),
    'options' => ['class' => 'modal-content reset-password-form']]); ?>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
    </button>
    <h1 class="modal-title text-center" id="myModalLabel"><?= Yii::_t('users.forms.reset_password_title') ?></h1>
</div>
<div id="reset-password" class="modal-body">
    <?= $form->field($model, 'password')->passwordInput(['placeholder' => Yii::_t('users.login.password')])->label(false) ?>
</div>
<div id="reset-password-footer" class="modal-footer text-center">
    <div class="form-group">
        <?= Html::submitButton(Yii::_t('users.forms.send'), ['class' => 'btn custom-button custom-red-btn']); ?>
    </div>
</div>
<?php ActiveForm::end() ?>
