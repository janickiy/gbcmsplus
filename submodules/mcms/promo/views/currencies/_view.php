<?php
use yii\widgets\DetailView;

?>

<?= DetailView::widget([
  'model' => $model,
  'attributes' => [
    'id',
    'name',
    'code',
    'symbol',
    'created_at:datetime',
    'updated_at:datetime'
  ]
]); ?>