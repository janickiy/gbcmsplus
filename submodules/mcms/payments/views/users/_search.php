<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;
?>
<div class="user-search-form">
  <?php $form = ActiveForm::begin([
    'method' => 'get',
    'options' => [
      'data-pjax' => true,
    ]
  ]); ?>

  <?= $form->field($userSearch, 'username'); ?>

  <div class="clearfix">
    <?= Html::button("Submit", [
      'type' => 'submit',
      'class' => 'btn btn-primary pull-right'
    ]); ?>
  </div>
</div>

<?php ActiveForm::end(); ?>