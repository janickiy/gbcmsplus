<?php

use mcms\common\grid\ContentViewPanel;
use mcms\common\widget\AdminGridView;
use mcms\common\widget\Select2;
use mcms\payments\components\UserBalance;
use yii\widgets\Pjax;
use kartik\date\DatePicker;

/** @var \mcms\holds\models\PartnerHoldSearch $searchModel */
/** @var \yii\data\ArrayDataProvider $dataProvider */
/** @var array $countries */
/** @var bool canViewLastUnholdDate */
?>
<?php ContentViewPanel::begin([ 'padding' => false ]); ?>
<?php Pjax::begin(); ?>

<?= AdminGridView::widget([
  'dataProvider' => $dataProvider,
  'filterModel' => $searchModel,
  'export' => false,
  'columns' => [
    [
      'attribute' => 'date',
      'format' => 'date',
      'filter' => DatePicker::widget([
        'model' => $searchModel,
        'attribute' => 'dateFrom',
        'attribute2' => 'dateTo',
        'type' => DatePicker::TYPE_RANGE,
        'separator' => '<i class="glyphicon glyphicon-calendar"></i>',
        'pluginOptions' => ['format' => 'yyyy-mm-dd', 'autoclose' => true, 'orientation' => 'bottom']
      ]),
      'contentOptions' => ['style' => 'width: 200px;']
    ],
    [
      'attribute' => 'countryId',
      'filter' => Select2::widget([
        'model' => $searchModel,
        'attribute' => 'countryId',
        'data' => $countries,
        'options' => [
          'placeholder' => '',
        ],
        'pluginOptions' => [
          'allowClear' => true
        ]
      ]),
      'format' => 'raw',
      'value' => 'countryLink'
    ],
    [
      'attribute' => 'unholdDate',
      'filter' => false,
      'format' => 'date',
    ],
    [
      'attribute' => 'holdProfit',
      'filter' => false,
      'format' => 'decimal',
    ],
    [
      'attribute' => 'userCurrency',
      'format' => 'raw',
      'filter' => Select2::widget([
        'model' => $searchModel,
        'attribute' => 'userCurrency',
        'data' => UserBalance::getCurrencies(),
        'options' => [
          'multiple' => true,
          'placeholder' => '',
        ],
        'pluginOptions' => [
          'allowClear' => true,
        ]
      ]),
    ],
    [
      'attribute' => 'rule',
      'filter' => false,
      'format' => 'raw',
    ],
    [
      'attribute' => 'lastUnholdDate',
      'format' => 'date',
      'filter' => DatePicker::widget([
        'model' => $searchModel,
        'attribute' => 'lastUnholdDateFrom',
        'attribute2' => 'lastUnholdDateTo',
        'type' => DatePicker::TYPE_RANGE,
        'separator' => '<i class="glyphicon glyphicon-calendar"></i>',
        'pluginOptions' => ['format' => 'yyyy-mm-dd', 'autoclose' => true, 'orientation' => 'bottom']
      ]),
      'contentOptions' => ['style' => 'width: 200px;'],
      'visible' => $canViewLastUnholdDate,
    ],
  ]
]);
?>
<?php Pjax::end(); ?>
<?php ContentViewPanel::end() ?>
