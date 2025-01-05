<?php

use kartik\grid\GridView;
use mcms\common\grid\ContentViewPanel;
use rgk\export\ExportMenu;
use mcms\common\grid\SortIcons;
use mcms\common\helpers\Html;
use mcms\common\widget\AdminGridView;
use mcms\statistic\Module;
use mcms\statistic\models\mysql\Analytics;
use mcms\statistic\assets\StatisticAsset;
use yii\data\DataProviderInterface;

StatisticAsset::register($this);
SortIcons::register($this);
$promoModule = Yii::$app->getModule('promo');
$userModule = Yii::$app->getModule('users');
/** @var Analytics $model */
/** @var DataProviderInterface $dataProvider */
/** @var array $countriesId */
/** @var array $operatorsId */
/** @var string $exportWidgetId */

$canViewColumnsDecimals = Yii::$app->user->can(Module::VIEW_COLUMNS_DECIMALS);
$formatter = Yii::$app->formatter;
?>

<div id="page-content-wrapper">
  <div class="container-fluid xyz">


    <?php
    $gridColumns = [
      [
        'label' => $model->getGridColumnLabel('date'),
        'attribute' => 'date',
        'format' => 'date',
        'footer' =>  Yii::_t('statistic.statistic_total'),
      ],
      [
        'label' => $model->getGridColumnLabel('count_ons'),
        'attribute' => 'count_ons',
        'value' => function ($row) use ($model) {
          return $model->getCountOns($row);
        },
        'format' => 'integer',
        'footer' => $formatter->asInteger($model->getResultValue('count_ons'))
      ],
      [
        'label' => $model->getGridColumnLabel('count_offs'),
        'attribute' => 'count_offs',
        'value' => function ($row) use ($model) {
          return $model->getCountOffs($row);
        },
        'format' => 'integer',
        'footer' => $formatter->asInteger($model->getResultValue('count_offs'))
      ],
      [
        'label' => $model->getGridColumnLabel('count_rebills'),
        'attribute' => 'count_rebills',
        'value' => function ($row) use ($model) {
          return $model->getCountRebills($row);
        },
        'format' => 'integer',
        'footer' => $formatter->asInteger($model->getResultValue('count_rebills'))
      ],
      [
        'label' => $model->getGridColumnLabel('sum_profit'),
        'attribute' => 'sum_profit',
        'value' => function ($row) use ($model) {
          return $model->getSumProfit($row);
        },
        'format' => 'statisticSum',
        'footer' => $formatter->asStatisticSum($model->getResultValue('sum_profit'))
      ],
      [
        'attribute' => 'ltv_rebills',
        'label' => $model->getGridColumnLabel('scope_count_rebills'),
        'format' => 'integer',
        'value' => function ($row) use ($model) {
          return $model::getLtvRebills($row);
        },
        'footer' => $formatter->asInteger($model->getResultValue('ltv_rebills'))
      ],
      [
        'label' => $model->getGridColumnLabel('active_database'),
        'format' => ['percent', 2],
        'value' => function ($row) use ($model) {
          return $model->getActiveDatabase($row);
        },
        'footer' => $formatter->asPercent($model->getResultValue('active_database'), 2)
      ],
      [
        'attribute' => 'scope_count_offs',
        'label' => $model->getGridColumnLabel('scope_count_offs'),
        'format' => 'integer',
        'value' => function ($row) use ($model) {
          return $model->getScopeCountOffs($row);
        },
        'footer' => $formatter->asInteger($model->getResultValue('scope_count_offs'))
      ],
      [
        'attribute' => 'ltv_profit',
        'label' => $model->getGridColumnLabel('scope_sum_profit'),
        'format' => 'statisticSum',
        'value' => function ($row) use ($model) {
          return $model::getLtvProfit($row);
        },
        'footer' => $formatter->asStatisticSum($model->getResultValue('ltv_profit'))
      ],
      [
        'attribute' => 'avg_rebills',
        'headerOptions' => [
          'title' => sprintf('(%s) / (%s)', $model->getGridColumnLabel('scope_count_rebills'), $model->getGridColumnLabel('count_ons'))
        ],
        'label' => $model->getGridColumnLabel('average_rebills'),
        'format' => $canViewColumnsDecimals ? ['decimal', 4] : 'decimal',
        'value' => function ($row) use ($model) {
          return $model->getAverageRebills($row);
        },
        'footer' => $formatter->asDecimal($model->getResultValue('avg_rebills'), ($canViewColumnsDecimals ? 4 : 2))
      ],
      [
        'attribute' => 'avg_profit',
        'headerOptions' => [
          'title' => sprintf('(%s) / (%s)', $model->getGridColumnLabel('scope_sum_profit'), $model->getGridColumnLabel('count_ons'))
        ],
        'label' => $model->getGridColumnLabel('average_profit'),
        'format' => $canViewColumnsDecimals ? ['decimal', 4] : 'decimal',
        'value' => function ($row) use ($model) {
          return $model->getAverageProfit($row);
        },
        'footer' => $formatter->asDecimal($model->getResultValue('avg_profit'), ($canViewColumnsDecimals ? 4 : 2))
      ],
      [
        'attribute' => 'cpr',
        'label' => $model->getGridColumnLabel('cpr'),
        'headerOptions' => [
          'title' => $model->getCalcAverCpaAllSubs()
          ? sprintf('(%s) / (%s)', $model->getGridColumnLabel('sold_partner_profit'), $model->getGridColumnLabel('count_ons'))
              : sprintf('(%s) / (%s)', $model->getGridColumnLabel('sold_partner_profit'), $model->getGridColumnLabel('count_sold'))
        ],
        'visible' => !in_array($model->type, [Analytics::REVSHARE, Analytics::ALL]),
        'format' => $canViewColumnsDecimals ? ['decimal', 4] : 'decimal',
        'value' => function ($row) use ($model) {
          return $model->getCPR($row, $model->currency);
        },
        'footer' => $formatter->asDecimal($model->getResultValue('cpr_' . $model->currency), ($canViewColumnsDecimals ? 4 : 2))
      ],
      [
        'attribute' => 'sold_partner_profit_' . $model->currency,
        'label' => $model->getGridColumnLabel('sold_partner_profit'),
        'visible' => in_array($model->type, [Analytics::CPA, Analytics::SOLD], false),
        'format' => 'statisticSum',
        'footer' => $formatter->asStatisticSum($model->getResultValue('sold_partner_profit_' . $model->currency))
      ],
      [
        'attribute' => 'roi',
        'label' => $model->getGridColumnLabel('roi'),
        'headerOptions' => [
          'title' => sprintf('((%s) / (%s) - 1) * 100', $model->getGridColumnLabel('scope_sum_profit'), $model->getGridColumnLabel('sold_partner_profit'))
        ],
        'visible' => in_array($model->type, [Analytics::CPA, Analytics::SOLD], false),
        'format' => ['percent', 2],
        'value' => function ($row) use ($model) {
          return $model->getROI($row, $model->currency);
        },
        'footer' => $formatter->asPercent($model->getResultValue('roi_' . $model->currency), ($canViewColumnsDecimals ? 4 : 2))
      ],
    ];

    $gridView = AdminGridView::widget([
      'dataProvider' => $dataProvider,
      'exportConfig' => [GridView::CSV => []],
      'resizableColumns' => false,
      'pjax' => true,
      'pjaxSettings' => ['options' => ['id' => 'statistic-pjax']],
      'tableOptions' => [
        'id' => 'statistic-data-table',
        'class' => 'table table-striped nowrap text-center detail-table dataTables_scrollHeadInner',
        'data-empty-result' => Yii::t('yii', 'No results found.')
      ],
      'options' => [
        'class' => 'grid-view',
        'style' => 'overflow:hidden; width: 100%;'  // иначе таблица растягивается за пределы экрана.
      ],
      'emptyCell' => 0,
      'columns' => $gridColumns,
      'showFooter' => true,
    ]);

    $toolbar = ExportMenu::widget([
      'id' => $exportWidgetId,
      'dataProvider' => $dataProvider,
      'dropdownOptions' => ['class' => 'btn-xs btn-success', 'menuOptions' => ['class' => 'pull-right']],
      'template'=>'{menu}',
      'columns' => $gridColumns,
      'target' => ExportMenu::TARGET_BLANK,
      'pjaxContainerId' => 'statistic-pjax',
      'filename' => Yii::_t('main.analytics'),
      'exportConfig' => [
        ExportMenu::FORMAT_HTML => false,
        ExportMenu::FORMAT_PDF => false,
        ExportMenu::FORMAT_EXCEL =>  false,
      ],
    ]);
    $toolbar .=  Html::dropDownList('table-filter', null, array_values($model->gridColumnLabels()), [
      'class' => 'selectpicker menu-right col-i',
      'id' => 'table-filter',
      'multiple' => true,
      'title' => yii\bootstrap\Html::icon('cog') . ' ' . Yii::_t('statistic.statistic.filter_table'),
      'data-count-selected-text' => yii\bootstrap\Html::icon('cog') . ' ' . Yii::_t('statistic.statistic.filter_table'),
      'data-selected-text-format' => 'count>1',
    ]);
    ?>
    <?php ContentViewPanel::begin([
      'padding' => false,
      'toolbar' => $toolbar,
    ]); ?>

    <?= $this->render('_search', [
      'model' => $model,
      'operatorsId' => $operatorsId,
      'countriesId' => $countriesId,
      'countries' => $countries,
      'filterDatePeriods' => isset($filterDatePeriods) ? $filterDatePeriods : null,
      'showIsVisibleToPartnerFilter' => true,
      'showLtvDateFilter' => true,
    ]) ?>

    <?php
    $gridColumns = array_map(function($value) {
      if (empty($value['attribute'])) {
        return $value;
      }

      if (!isset($value['headerOptions'])) {
        $value['headerOptions'] = [];
      }
      $value['headerOptions']['data-code'] = $value['attribute'];
      return $value;
    }, $gridColumns);
    ?>
    <?= $gridView; ?>
    <?php ContentViewPanel::end() ?>
  </div>
</div>
