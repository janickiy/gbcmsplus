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
    'name',
    [
      'attribute' => 'status',
      'format' => 'raw',
      'value' => Html::tag('span', $model->currentStatusName, ['class' => 'bg-' . $model->getStatusColors()[$model->status]])
    ],
    [
      'label' => Yii::_t('promo.arbitrary_sources.main'),
      'format' => 'raw',
      'value' => $model->sourcesLink
    ],
    'created_at:datetime',
    'updated_at:datetime',
  ]
]); ?>