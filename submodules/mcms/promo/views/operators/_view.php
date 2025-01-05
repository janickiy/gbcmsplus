<?php

use mcms\common\widget\Editable;
use yii\widgets\DetailView;

/** @var \yii\web\View $this */
/** @var $model \mcms\promo\models\Operator */
/** @var $statisticModule \mcms\statistic\Module */
?>
<?php $canViewDetails = Yii::$app->user->can('PromoOperatorsDetailView'); ?>
<?= DetailView::widget([
  'model' => $model,
  'attributes' => [
    'id',
    'name',
    [
      'attribute' => 'country_id',
      'format' => 'raw',
      'value' => $model->countryLink
    ],
    [
      'attribute' => 'status',
      'value' => $model->currentStatusName,
      'visible' => $canViewDetails,
    ],
    [
      'attribute' => 'is_3g',
      'value' => $model->is_3g ? Yii::_t('app.common.Yes') : Yii::_t('app.common.No')
    ],
    [
      'attribute' => 'is_trial',
      'value' => $model->is_trial ? Yii::_t('app.common.Yes') : Yii::_t('app.common.No')
    ],
    [
      'attribute' => 'is_disallow_replace_landing',
      'format' => 'raw',
      'value' =>
      /* @var $model  \mcms\promo\models\Operator */
        $model->canUpdateParams() ?
          Editable::getWidget([
            'name' => 'is_disallow_replace_landing',
            'value' => (is_null($model->is_disallow_replace_landing)
              ? Yii::_t('promo.operators.global_no')
              : Yii::_t(sprintf('app.common.%s', $model->is_disallow_replace_landing ? 'Yes' : 'No'))),
            'header' => Yii::_t('promo.operators.is_disallow_replace_landing'),
            'inputType' => Editable::INPUT_DROPDOWN_LIST,
            'data' => [0 => Yii::_t('app.common.No'), 1 => Yii::_t('app.common.Yes')], // any list of values
            'options' => [
              'class' => 'form-control',
            ],
          ], [
            'update-params',
            'operatorId' => $model->id,
            'attribute' => 'is_disallow_replace_landing',
          ], false, true) :

          (is_null($model->is_disallow_replace_landing)
            ? Yii::_t('promo.operators.global_no')
            : Yii::_t(sprintf('app.common.%s', $model->is_disallow_replace_landing ? 'Yes' : 'No'))),
      'visible' => $model->canUpdateParams(),
    ],
    [
      'label' => Yii::_t('promo.landings.main'),
      'format' => 'raw',
      'value' => $model->landingsLink
    ],
    [
      'attribute' => 'created_at',
      'format' => 'datetime',
      'visible' => $canViewDetails,
    ],
    [
      'attribute' => 'updated_at',
      'format' => 'datetime',
      'visible' => $canViewDetails,
    ],
    [
      'attribute' => 'created_by',
      'value' => $model->createdBy->username,
      'visible' => $canViewDetails,
    ]

  ]
]); ?>

<?php if (count($model->operatorIp) > 0): ?>
  <h3><?= Yii::_t('promo.operators.attribute-ipTextarea') ?></h3>
  <pre><?php foreach ($model->operatorIp as $ip): ?><?= $ip->from_ip . '/' . $ip->mask . "\n"; ?><?php endforeach; ?></pre>
<?php endif ?>
