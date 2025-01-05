<?php
use yii\widgets\ActiveForm;
use yii\bootstrap\Html;

/**
 * @var bool $isRegistrationByEmail
 */
?>

<div class="form-container well">

  <div class="row">
    <div class="col-md-12">
      <?php $form = ActiveForm::begin(['id' => 'signup-form']); ?>
      <?= $form->field($model, 'email') ?>
      <?= $form->field($model, 'password')->passwordInput() ?>
      <?= $form->field($model, 'passwordRepeat')->passwordInput() ?>
      <?= $form->field($model, 'language')->dropDownList(['ru' => Yii::_t('signup.russian'), 'en' => Yii::_t('signup.english')]) ?>
      <?= $form->field($model, 'skype') ?>
      <?= $form->field($model, 'currency')->dropDownList($currencyList) ?>

      <div class="form-group">
        <?= Html::submitButton(Yii::_t('forms.register'), ['class' => 'btn btn-primary col-md-12']); ?>
      </div>

      <?php ActiveForm::end() ?>
    </div>
  </div>
</div>