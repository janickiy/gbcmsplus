<?php

use yii\widgets\ActiveForm;
use yii\bootstrap\Html;
use yii\helpers\Url;

?>

<?php $form = ActiveForm::begin([
    'id' => 'contact-form',
    'action' => Url::to(['users/api/contact']),
    'options' => ['class' => 'contact-form']]); ?>

<div data-sr-complete="true" data-sr-init="true" class="col-lg-4 col-sm-4 zerif-rtl-contact-name"
     data-scrollreveal="enter left after 0s over 1s">
    <?= $form->field($model, 'username')->textInput(['placeholder' => Yii::_t('users.contact.username'), 'class' => 'form-control custom-input-box'])->label(false) ?>
</div>

<div data-sr-complete="true" data-sr-init="true" class="col-lg-4 col-sm-4 zerif-rtl-contact-email"
     data-scrollreveal="enter left after 0s over 1s">
    <?= $form->field($model, 'email')->textInput(['placeholder' => Yii::_t('users.contact.email'), 'class' => 'form-control custom-input-box'])->label(false) ?>
</div>

<div data-sr-complete="true" data-sr-init="true" class="col-lg-4 col-sm-4 zerif-rtl-contact-subject"
     data-scrollreveal="enter left after 0s over 1s">
    <?= $form->field($model, 'subject')->textInput(['placeholder' => Yii::_t('users.contact.subject'), 'class' => 'form-control custom-input-box  '])->label(false) ?>
</div>

<div data-sr-complete="true" data-sr-init="true" class="col-lg-12 col-sm-12"
     data-scrollreveal="enter right after 0s over 1s">
    <?= $form->field($model, 'message')->textarea(['placeholder' => Yii::_t('users.contact.message'), 'class' => 'form-control custom-textarea-box']) ?>
</div>

<?= Html::submitButton(Yii::_t('users.forms.send_message'), ['class' => 'btn btn-primary custom-button red-btn']); ?>

<?php ActiveForm::end() ?>

