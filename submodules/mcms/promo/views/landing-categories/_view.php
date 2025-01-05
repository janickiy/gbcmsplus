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
      'value' => $model->currentStatusName
    ],
    [
      'label' => Yii::_t('promo.landings.main'),
      'format' => 'raw',
      'value' => $model->landingsLink
    ],
    [
      'attribute' => 'alter_categories',
      'value' => implode($model::DELIMITER, $model->alter_categories),
    ],
    'tb_url',
    'created_at:datetime',
    'updated_at:datetime',
    [
      'attribute' => 'created_by',
      'value' => $model->createdBy->username
    ],
    [
      'attribute' => 'is_not_mainstream',
      'format' => 'raw',
      'value' => $model->is_not_mainstream ? Yii::_t('app.common.Yes') : Yii::_t('app.common.No')
    ],
  ]
]); ?>