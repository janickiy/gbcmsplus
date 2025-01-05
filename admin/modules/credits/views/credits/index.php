<?php

use admin\modules\credits\models\Credit;
use admin\modules\credits\models\form\CreditPaymentForm;
use admin\modules\credits\widgets\CreditStatusesDropdown;
use mcms\common\widget\AdminGridView;
use mcms\common\grid\ContentViewPanel;
use mcms\common\widget\Select2;
use rgk\utils\assets\AjaxButtonsAsset;
use rgk\utils\widgets\AmountRange;
use rgk\utils\widgets\modal\Modal;
use yii\bootstrap\Html;
use rgk\utils\widgets\DateRangePicker;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel admin\modules\credits\models\search\CreditSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

AjaxButtonsAsset::register($this);

$this->title = Yii::_t('credits.main.credits');
$this->params['breadcrumbs'][] = $this->title;

$this->blocks['actions'] = Modal::widget([
  'toggleButtonOptions' => [
    'label' => Html::icon('plus') . ' ' . Yii::_t('credits.credit.create'),
    'class' => 'btn btn-xs btn-success',
  ],
  'url' => ['/credits/credits/create-modal'],
]);
?>

<?php ContentViewPanel::begin([
  'padding' => false,
  'header' => $this->title,
]); ?>

<?php Pjax::begin(['id' => 'credits-pjax']); ?>
<?= AdminGridView::widget([
  'dataProvider' => $dataProvider,
  'filterModel' => $searchModel,
  'options' => [
    'class' => 'text-nowrap'
  ],
  'rowOptions' => function (Credit $model) {
    if ($model->status === Credit::STATUS_REQUESTED) {
      return ['class' => 'warning'];
    }
    if ($model->status === Credit::STATUS_DECLINED) {
      return ['class' => 'danger'];
    }
    if ($model->status === Credit::STATUS_DONE) {
      return ['class' => 'success'];
    }
    return '';
  },
  'columns' => [
    'id',
    [
      'attribute' => 'amount',
      'format' => 'decimal',
      'filter' => AmountRange::widget([
        'model' => $searchModel,
        'attribute1' => 'fromAmount',
        'attribute2' => 'toAmount',
      ]),
      'contentOptions' => function () {
        return ['style' => 'width: 100px;'];
      },
    ],
    [
      'attribute' => 'debtSum',
      'format' => 'decimal',
      'filter' => AmountRange::widget([
        'model' => $searchModel,
        'attribute1' => 'fromDebtSum',
        'attribute2' => 'toDebtSum',
      ]),
      'contentOptions' => function () {
        return ['style' => 'width: 100px;'];
      },
    ],
    [
      'attribute' => 'currency',
      'filter' => Select2::widget([
        'model' => $searchModel,
        'attribute' => 'currency',
        'data' => Credit::getCurrencyList(),
        'options' => [
          'multiple' => false,
          'placeholder' => '',
        ],
        'pluginOptions' => [
          'allowClear' => true,
        ]
      ]),
      'filterOptions' => ['class' => 'column-select'],
    ],
    'percent',
    [
      'attribute' => 'status',
      'filter' => CreditStatusesDropdown::widget([
        'model' => $searchModel,
        'attribute' => 'status',
      ]),
      'value' => 'statusName'
    ],
    [
      'attribute' => 'created_at',
      'format' => 'datetime',
      'filter' => DateRangePicker::widget([
        'model' => $searchModel,
        'attribute' => 'createdDateRange',
        'align' => DateRangePicker::ALIGN_LEFT
      ])
    ],
    [
      'attribute' => 'closed_at',
      'format' => 'datetime',
      'filter' => DateRangePicker::widget([
        'model' => $searchModel,
        'attribute' => 'closedDateRange',
        'align' => DateRangePicker::ALIGN_LEFT
      ])
    ],
    [
      'class' => 'rgk\utils\widgets\grid\ActionColumn',
      'template' => '{create-payment} {view}',
      'contentOptions' => ['style' => 'min-width: 100px'],
      'visibleButtons' => [
        'create-payment' => function (Credit $model) {
          return CreditPaymentForm::isAvailableByCredit($model);
        },
      ],
      'buttons' => [
        'create-payment' => function ($url, Credit $model) {
          return Modal::widget([
            'toggleButtonOptions' => [
              'label' => Html::icon('credit-card'),
              'class' => 'btn btn-xs btn-default',
            ],
            'url' => ['/credits/credit-payments/create-modal', 'creditId' => $model->id],
          ]);
        },
      ]
    ],
  ],
]); ?>
<?php Pjax::end(); ?>
<?php ContentViewPanel::end();
