<?php

use mcms\common\grid\ContentViewPanel;
use mcms\common\widget\AdminGridView;
use mcms\common\widget\UserSelect2;
use mcms\promo\components\widgets\OperatorsDropdown;
use mcms\promo\models\search\UserOperatorTrafficFiltersOffSearch;
use rgk\utils\helpers\Html;
use mcms\common\grid\ActionColumn;
use rgk\utils\widgets\modal\Modal;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;
use yii\widgets\Pjax;


/** @var ActiveDataProvider $dataProvider */
/** @var UserOperatorTrafficFiltersOffSearch $searchModel */
?>


<?php ContentViewPanel::begin([
  'padding' => false,
  'toolbar' => Modal::widget([
      'toggleButtonOptions' => [
        'tag' => 'a',
        'label' => Html::icon('plus') . ' ' . Yii::_t('promo.traffic_block.create'),//todo
        'class' => 'btn btn-xs btn-success',
        'data-pjax' => 0,
      ],
      'url' => ['/promo/traffic-filters-off/create-modal/', 'userId' => $userId],
    ])
]); ?>
<?php Pjax::begin(['id' => 'TrafficFiltersOffGrid']); ?>

<?= AdminGridView::widget([
  'dataProvider' => $dataProvider,
  'filterModel' => $searchModel,
  'columns' => [
    [
      'attribute' => 'operator_id',
      'format' => 'raw',
      'filter' => OperatorsDropdown::widget([
        'model' => $searchModel,
        'attribute' => 'operator_id',
        'options' => [
          'placeholder' => '',
        ],
        'pluginOptions' => ['allowClear' => true],
        'useSelect2' => true
      ]),
      'value' => 'operator.viewLink',
    ],
    [
      'class' => ActionColumn::class,
      'controller' => '/promo/traffic-filters-off',
      'template' => '{update-modal} {delete}',
    ],
  ],
]); ?>

<?php Pjax::end(); ?>
<?php ContentViewPanel::end();
