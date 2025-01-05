<?php

use mcms\common\grid\ContentViewPanel;
use mcms\common\widget\AdminGridView;
use mcms\common\widget\Select2;
use mcms\payments\components\UserBalance;
use mcms\payments\models\search\ResellerInvoiceSearch;
use mcms\payments\models\UserBalanceInvoice;
use rgk\theme\smartadmin\widgets\panel\PanelWidget;
use rgk\utils\widgets\AmountRange;
use rgk\utils\widgets\DateRangePicker;
use yii\bootstrap\Html;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel ResellerInvoiceSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::_t('payments.menu.reseller_invoices');
$this->params['breadcrumbs'][] = $this->title;
?>

<?php ContentViewPanel::begin([
  'padding' => false,
]); ?>

<?php Pjax::begin([
  'id' => 'invoices-pjax',
]); ?>

<?= AdminGridView::widget([
  'dataProvider' => $dataProvider,
  'filterModel' => $searchModel,
  'options' => [
    'class' => 'text-nowrap'
  ],
  'rowOptions' => function (UserBalanceInvoice $model) {
    if ($model->type === UserBalanceInvoice::TYPE_PENALTY) return ['class' => 'danger'];
    if ($model->type === UserBalanceInvoice::TYPE_COMPENSATION) return ['class' => 'success'];
    return '';
  },
  'columns' => [
    [
      'attribute' => 'id',
    ],
    [
      'attribute' => 'date',
      'format' => 'date',
      'filter' => DateRangePicker::widget([
        'model' => $searchModel,
        'attribute' => 'dateDateRange'
      ])
    ],
    [
      'attribute' => 'type',
      'format' => 'raw',
      'filter' => [
        UserBalanceInvoice::TYPE_COMPENSATION => UserBalanceInvoice::getTypes(UserBalanceInvoice::TYPE_COMPENSATION),
        UserBalanceInvoice::TYPE_PENALTY => UserBalanceInvoice::getTypes(UserBalanceInvoice::TYPE_PENALTY),
      ],
      'value' => function (UserBalanceInvoice $model) {
        if (empty($model->description) && empty($model->file)) return $model->getTypeName();

        $info = [];

        if ($model->description) $info[] = $model->description;
        if ($model->file) $info[] = '<div style="text-overflow:ellipsis;max-width: 250px;overflow: hidden;">' . Html::a(
            basename($model->file),
            $model->getFileDownloadUrl(),
            [
              'data-pjax' => 0,
              'title' => basename($model->file),
              'target' => '_blank'
            ]
          ) . '</div>';

        return Html::a(
          $model->getTypeName(),
          'javascript:void(0)',
          [
            'data' => [
              'content' => implode('<br>', $info),
              'toggle' => 'popover',
              'trigger' => 'focus',
              'placement' => 'left',
            ],
            'tabindex' => 0,
            'role' => 'button',
            'class' => 'mcms-popover'
          ]
        );
      }
    ],
    [
      'attribute' => 'amount',
      'format' => 'decimal',
      'filter' => AmountRange::widget([
        'model' => $searchModel,
        'attribute1' => 'fromAmount',
        'attribute2' => 'toAmount',
      ]),
      'contentOptions' => function (UserBalanceInvoice $model) {
        return [
          'style' => 'width: 170px;',
          'class' => $model->amount < 0 ? 'text-danger' : 'text-success'
        ];
      },
    ],
    [
      'attribute' => 'currency',
      'format' => 'raw',
      'filter' => Select2::widget([
        'model' => $searchModel,
        'attribute' => 'currency',
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
  ],
]); ?>
<?php
// приходится после пиджакс инициализировать заново
// todo возможно надо вынести в ассет и подключить в лейауте? или сделать виджеты нормальные, а не как у картика
$js = <<<JS
$("[data-toggle=tooltip]").tooltip({html: true}); 
$("[data-toggle=popover]").popover({html: true});
JS;
$this->registerJs($js);

?>
<?php Pjax::end(); ?>
<?php ContentViewPanel::end();