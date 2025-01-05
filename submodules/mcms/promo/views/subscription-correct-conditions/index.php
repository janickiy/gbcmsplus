<?php

use mcms\common\widget\AdminGridView;
use mcms\common\grid\ContentViewPanel;
use mcms\common\widget\modal\Modal;
use mcms\promo\models\search\SubscriptionCorrectConditionSearch;
use yii\widgets\Pjax;
use yii\helpers\Url;
use mcms\common\widget\UserSelect2;
use mcms\promo\components\widgets\OperatorsDropdown;
use mcms\promo\components\widgets\LandingsDropdown;

/**
 * @var \yii\data\ActiveDataProvider $dataProvider
 * @var SubscriptionCorrectConditionSearch $searchModel
 */
?>

<?php $this->beginBlock('actions'); ?>
<?= Modal::widget([
  'toggleButtonOptions' => [
    'tag' => 'a',
    'label' => \yii\bootstrap\Html::icon('plus') . ' ' . Yii::_t('promo.buyout_conditions.create'),
    'class' => 'btn btn-xs btn-success',
    'data-pjax' => 0,
  ],
  'url' => Url::to(['/promo/subscription-correct-conditions/create']),
]); ?>
<?php $this->endBlock() ?>

<?php ContentViewPanel::begin([
    'padding' => false,
]);
?>

<?php Pjax::begin(['id' => 'conditionsPjax']); ?>

<?= AdminGridView::widget([
  'dataProvider' => $dataProvider,
  'filterModel' => $searchModel,
  'export' => false,
  'columns' => [
    'id',
    'name',
    [
      'attribute' => 'operator_id',
      'format' => 'raw',
      'value' => 'operatorLink',
      'filter' => OperatorsDropdown::widget([
        'model' => $searchModel,
        'attribute' => 'operator_id',
        'options' => [
          'placeholder' => '',
        ],
        'pluginOptions' => ['allowClear' => true],
        'useSelect2' => true
      ]),
    ],
    [
      'attribute' => 'user_id',
      'format' => 'raw',
      'width' => '200px',
      'filter' => UserSelect2::widget([
          'model' => $searchModel,
          'attribute' => 'user_id',
          'initValueUserId' => $searchModel->user_id,
          'options' => [
            'placeholder' => '',
          ],
        ]
      ),
      'value' => 'userLink',
    ],
    [
      'attribute' => 'landing_id',
      'format' => 'raw',
      'value' => 'landingLink',
      'filter' => LandingsDropdown::widget([
        'model' => $searchModel,
        'attribute' => 'landing_id',
        'options' => [
          'placeholder' => '',
        ],
        'pluginOptions' => ['allowClear' => true],
        'useSelect2' => true
      ]),
    ],
    'percent',
    'is_active:boolean',
    [
      'class' => mcms\common\grid\ActionColumn::class,
      'template' => '{update-modal} {delete}',
      'contentOptions' => ['class' => ' col-min-width-100']
    ],

  ],
]); ?>
<?php Pjax::end(); ?>


<?php ContentViewPanel::end() ?>