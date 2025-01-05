<?php
use yii\widgets\DetailView;
use yii\helpers\Html;

?>

<?= DetailView::widget([
  'model' => $model,
  'attributes' => [
    'id',
    [
      'attribute' => 'user_id',
      'value' => $model->user->username
    ],
    'url:url',
    [
      'attribute' => 'status',
      'format' => 'raw',
      'value' => Html::tag('span', $model->currentStatusName, ['class' => 'bg-' . $model->getStatusColors()[$model->status]])
    ],
    [
      'attribute' => 'type',
      'value' => $model->currentTypeName
    ],
    [
      'attribute' => 'is_system',
      'format' => 'raw',
      'value' => $model->is_system ? Yii::_t('app.common.Yes') : Yii::_t('app.common.No')
    ],
    'created_at:datetime',
    'updated_at:datetime',
    [
      'attribute' => 'created_by',
      'value' => $model->createdBy->username
    ]
  ]
]); ?>