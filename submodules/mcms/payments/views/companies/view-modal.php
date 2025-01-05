<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use mcms\payments\models\Company;

?>

<div class="modal-header">
  <?= Html::button('<span aria-hidden="true">&times;</span>', ['class' => 'close', 'data-dismiss' => 'modal']); ?>
  <h4 class="modal-title"><?= $model->name ?></h4>
</div>

<div class="modal-body">
  <?= DetailView::widget([
    'model' => $model,
    'attributes' => [
      'id',
      'name',
      'country',
      'city',
      'address',
      'post_code',
      'tax_code',
      [
        'attribute' => 'logo',
        'format' => 'raw',
        'value' => function (Company $model) {
          return $model->logo
            ? Html::img(['/payments/companies/get-logo/', 'id' => $model->id, 't' => time()], ['style' => 'max-width:100%; max-height:100%;'])
            : null;
        }
      ],
      'created_at:datetime',
      'updated_at:datetime',
    ]
  ]); ?>
</div>
<div class="modal-footer">
  <?= Html::button(Yii::_t('app.common.Close'), ['class' => 'btn btn-default', 'data-dismiss' => 'modal']) ?>
</div>
