<?php

use yii\widgets\ActiveForm;
use yii\helpers\Url;

?>

<?php $form = ActiveForm::begin([
    'id' => 'contact-form',
    'action' => Url::to(['users/api/contact']),
    'options' => ['class' => 'callback-form']]); ?>

<div class="success">
    <span>Thanks for the application!</span>
</div>
<?= $form->field($model, 'username')->textInput(['placeholder' => Yii::_t('users.contact.username'), 'class' => 'form-control custom-input-box'])->label(false) ?>
<?= $form->field($model, 'subject')->hiddenInput(['value' => 'subject can not be blank'])->label(false) ?>
<?= $form->field($model, 'email')->textInput(['placeholder' => Yii::_t('users.contact.email'), 'class' => 'form-control custom-input-box'])->label(false) ?>
<?= $form->field($model, 'message')->textarea(['placeholder' => Yii::_t('users.contact.message'), 'class' => 'form-control custom-textarea-box', 'colls' => 30, 'rows' => 10])->label(false) ?>
<button class="button"><?= Yii::_t('users.forms.send_message') ?></button>
<?php ActiveForm::end() ?>

