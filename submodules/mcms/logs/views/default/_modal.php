<?php

use yii\bootstrap\Html;
use yii\widgets\DetailView;

/** @var array $data */

$attributes = [];
foreach ($data as $attribute => $value) {
  $attributes[] = [
    'attribute' => $attribute,
    'format' => 'raw',
    'value' => Html::textarea($attribute, $value, ['class' => 'form-control'])
  ];
}
?>

<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
  <h4 class="modal-title"><?=Yii::_t('logs.main.full-info')?></h4>
</div>
<div class="modal-body">
  <?php
    echo DetailView::widget([
      'model' => $data,
      'attributes' => $attributes
    ]);
  ?>
</div>

