<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;

?>
<div class="form-container well">

  <div class="row">

    <div class="col-md-12">
      <p><?= Yii::_t('forms.please_choose_your_new_password')?>:</p>
      <?php $form = ActiveForm::begin(['id' => 'password-reset-form']); ?>

      <?= $form->field($model, 'password')->passwordInput() ?>

      <div class="form-group">
        <?= Html::submitButton(Yii::_t('forms.send'), ['class' => 'btn btn-primary']); ?>
      </div>

      <?php ActiveForm::end() ?>
    </div>
  </div>
</div>
