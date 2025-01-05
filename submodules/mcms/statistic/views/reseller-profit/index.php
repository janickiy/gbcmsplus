<?php

use mcms\common\grid\ContentViewPanel;
use rgk\theme\smartadmin\widgets\grid\GridView;
use rgk\utils\components\CurrenciesValues;
use mcms\statistic\components\widgets\Totals;
use mcms\statistic\models\resellerStatistic\Item;
use mcms\statistic\models\resellerStatistic\ItemSearch;
use yii\data\ArrayDataProvider;
use yii\web\View;
use yii\widgets\Pjax;

/** @var View $this */
/** @var ItemSearch $searchModel */
/** @var ArrayDataProvider $dataProvider */
/** @var CurrenciesValues $unpaid */
/** @var array $awaitingPaymentsSummary */
$this->title = Yii::_t('statistic.reseller_profit.reseller_settlement_statistic');
$this->params['breadcrumbs'][] = $this->title;
?>

<?= Totals::widget(); ?>

<?php ContentViewPanel::begin([
  'padding' => false,
  'toolbar' => $this->render('_group_switcher', ['groupType' => $searchModel->groupType])
]); ?>

<?= $this->render('_search', ['searchModel' => $searchModel]) ?>

<?php Pjax::begin(['id' => 'statistic-pjax']); ?>

<?= GridView::widget([
  'dataProvider' => $dataProvider,
  'resizableColumns' => false,
  'layout' => '{items}',
  'showFooter' => $dataProvider->getTotalCount() > 1,
  'options' => ['class' => 'text-nowrap'],
  'tableOptions' => ['class' => 'table-condensed'],
  'groupHeader' => [
    'empty' => '',
    'profit' => Yii::_t('statistic.reseller_profit.total_turnover'),
    'awaiting' => Yii::_t('statistic.reseller_profit.awaiting_payments'),
    'paid' => Yii::_t('statistic.reseller_profit.paid'),
    'debt' => Yii::_t('statistic.reseller_profit.debt'),
  ],
  'columns' => [
    [
      'label' => $searchModel->getGroupByLabel(),
      'headerOptions' => [
        'group' => 'empty',
      ],
      'attribute' => 'group',
      'format' => 'dateRangeFormat',
      'footer' => Yii::_t('statistic.reseller_profit.total') . ':',
    ],

    // Total turnover
    [
      'label' => strtoupper('rub'),
      'format' => 'raw',
      'headerOptions' => ['group' => 'profit'],
      'value' => function (Item $item) {
        return $this->render('_turnover_cell', ['item' => $item, 'currency' => 'rub']);
      },
      'footer' => $this->render('_turnover_cell', ['searchModel' => $searchModel, 'currency' => 'rub'])
    ],
    [
      'label' => strtoupper('usd'),
      'format' => 'raw',
      'headerOptions' => ['group' => 'profit'],
      'value' => function (Item $item) {
        return $this->render('_turnover_cell', ['item' => $item, 'currency' => 'usd']);
      },
      'footer' => $this->render('_turnover_cell', ['searchModel' => $searchModel, 'currency' => 'usd'])
    ],
    [
      'label' => strtoupper('eur'),
      'format' => 'raw',
      'headerOptions' => ['group' => 'profit'],
      'value' => function (Item $item) {
        return $this->render('_turnover_cell', ['item' => $item, 'currency' => 'eur']);
      },
      'footer' => $this->render('_turnover_cell', ['searchModel' => $searchModel, 'currency' => 'eur'])
    ],

    // Awaiting (Отправлено в RGK)
    [
      'label' => strtoupper('rub'),
      'format' => 'raw',
      'headerOptions' => ['class' => 'warning', 'group' => 'awaiting'],
      'contentOptions' => ['class' => 'warning'],
      'value' => function (Item $item) {
        return $this->render('_awaiting_cell', ['item' => $item, 'currency' => 'rub']);
      },
      'footer' => $this->render('_awaiting_cell', ['searchModel' => $searchModel, 'currency' => 'rub'])
    ],
    [
      'label' => strtoupper('usd'),
      'format' => 'raw',
      'headerOptions' => ['class' => 'warning', 'group' => 'awaiting'],
      'contentOptions' => ['class' => 'warning'],
      'value' => function (Item $item) {
        return $this->render('_awaiting_cell', ['item' => $item, 'currency' => 'usd']);
      },
      'footer' => $this->render('_awaiting_cell', ['searchModel' => $searchModel, 'currency' => 'usd'])
    ],
    [
      'label' => strtoupper('eur'),
      'format' => 'raw',
      'headerOptions' => ['class' => 'warning', 'group' => 'awaiting'],
      'contentOptions' => ['class' => 'warning'],
      'value' => function (Item $item) {
        return $this->render('_awaiting_cell', ['item' => $item, 'currency' => 'eur']);
      },
      'footer' => $this->render('_awaiting_cell', ['searchModel' => $searchModel, 'currency' => 'eur'])
    ],

    // Paid (Выплачено через RGK)
    [
      'label' => strtoupper('rub'),
      'format' => 'raw',
      'headerOptions' => ['class' => 'success', 'group' => 'paid'],
      'contentOptions' => ['class' => 'success'],
      'value' => function (Item $item) use ($searchModel) {
        return $this->render('_paid_cell', ['item' => $item, 'currency' => 'rub']);
      },
      'footer' => $this->render('_paid_cell', ['searchModel' => $searchModel, 'currency' => 'rub'])
    ],
    [
      'label' => strtoupper('usd'),
      'format' => 'raw',
      'headerOptions' => ['class' => 'success', 'group' => 'paid'],
      'contentOptions' => ['class' => 'success'],
      'value' => function (Item $item) use ($searchModel) {
        return $this->render('_paid_cell', ['item' => $item, 'currency' => 'usd']);
      },
      'footer' => $this->render('_paid_cell', ['searchModel' => $searchModel, 'currency' => 'usd'])
    ],
    [
      'label' => strtoupper('eur'),
      'format' => 'raw',
      'headerOptions' => ['class' => 'success', 'group' => 'paid'],
      'contentOptions' => ['class' => 'success'],
      'value' => function (Item $item) use ($searchModel) {
        return $this->render('_paid_cell', ['item' => $item, 'currency' => 'eur']);
      },
      'footer' => $this->render('_paid_cell', ['searchModel' => $searchModel, 'currency' => 'eur'])
    ],

    // Debt
    [
      'label' => strtoupper('rub'),
      'format' => 'raw',
      'headerOptions' => ['class' => 'info', 'group' => 'debt'],
      'contentOptions' => ['class' => 'info'],
      'value' => function (Item $item) {
        return $this->render('_debt_cell', ['item' => $item, 'currency' => 'rub']);
      },
      'footer' => $this->render('_debt_cell', ['searchModel' => $searchModel, 'currency' => 'rub', 'hideHolds' => true])
    ],
    [
      'label' => strtoupper('usd'),
      'format' => 'raw',
      'headerOptions' => ['class' => 'info', 'group' => 'debt'],
      'contentOptions' => ['class' => 'info'],
      'value' => function (Item $item) {
        return $this->render('_debt_cell', ['item' => $item, 'currency' => 'usd']);
      },
      'footer' => $this->render('_debt_cell', ['searchModel' => $searchModel, 'currency' => 'usd', 'hideHolds' => true])
    ],
    [
      'label' => strtoupper('eur'),
      'format' => 'raw',
      'headerOptions' => ['class' => 'info', 'group' => 'debt'],
      'contentOptions' => ['class' => 'info'],
      'value' => function (Item $item) {
        return $this->render('_debt_cell', ['item' => $item, 'currency' => 'eur']);
      },
      'footer' => $this->render('_debt_cell', ['searchModel' => $searchModel, 'currency' => 'eur', 'hideHolds' => true])
    ],

  ]
])
?>
<?php
// todo возможно надо вынести в ассет и подключить в лейауте? или сделать виджеты нормальные, а не как у картика
$js = '$("[data-toggle=tooltip]").tooltip({html: true}); 
$("[data-toggle=popover]").popover({html: true})';
$this->registerJs($js);
?>
<?php Pjax::end(); ?>
<?php ContentViewPanel::end() ?>

