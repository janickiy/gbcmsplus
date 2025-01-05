<?php
use yii\widgets\DetailView;

?>

<?= DetailView::widget([
  'model' => $model,
  'attributes' => [
    'id',
    'name',
    'label1',
    'description1',
    'label2',
    'description2',
    'created_at:datetime',
    'updated_at:datetime',
    [
      'attribute' => 'status',
      'value' => $model->currentStatusName
    ],
  ]
]); ?>