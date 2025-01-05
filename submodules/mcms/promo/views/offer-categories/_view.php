<?php
use yii\widgets\DetailView;
use yii\helpers\Html;

?>

<?= DetailView::widget([
  'model' => $model,
  'attributes' => [
    'id',
    'code',
    'name',
    [
      'attribute' => 'status',
      'format' => 'raw',
      'value' => $model->statusLabel
    ],
    [
      'label' => Yii::_t('promo.landings.main'),
      'format' => 'raw',
      'value' => $model->landingsLink
    ],
    'created_at:datetime',
    'updated_at:datetime',
  ]
]); ?>