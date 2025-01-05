<?php
use yii\widgets\DetailView;
use yii\helpers\Html;

/** @var \mcms\promo\models\LandingUnblockRequest $model */
?>

<?= DetailView::widget([
  'model' => $model,
  'attributes' => [
    'id',
    [
      'attribute' => 'status',
      'format' => 'raw',
      'value' => Html::tag('span', $model->currentStatusName, ['class' => 'bg-' . $model->getStatusColors()[$model->status]])
    ],
    [
      'attribute' => 'traffic_type',
      'value' => implode(', ', \mcms\promo\models\TrafficType::getNamesByIds($model->traffic_type))
    ],
    'description',
    [
      'attribute' => 'user_id',
      'format' => 'raw',
      'value' => $model->userLink
    ],
    [
      'attribute' => 'landing_id',
      'format' => 'raw',
      'value' => $model->landingLink
    ],
    'created_at:datetime',
    'updated_at:datetime'
  ]
]); ?>