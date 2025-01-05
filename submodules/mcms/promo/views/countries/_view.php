<?php
use yii\widgets\DetailView;

/** @var \mcms\promo\models\Country $model */
?>

<?= DetailView::widget([
  'model' => $model,
  'attributes' => [
    'id',
    'name',
    'code',
    'local_currency',
    'created_at:datetime',
    'updated_at:datetime',
    [
      'attribute' => 'status',
      'value' => $model->currentStatusName
    ],
  ]
]);
