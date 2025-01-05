<?php
use mcms\common\grid\ContentViewPanel;
use rgk\export\ExportMenu;
use mcms\common\helpers\Html;
use mcms\common\widget\AdminGridView;
use mcms\statistic\assets\StatisticAsset;
use mcms\statistic\models\ResellerProfitStatistics;
use yii\widgets\Pjax;

/**
 * @var \mcms\statistic\models\ResellerProfitStatistics $model
 * @var \yii\data\ActiveDataProvider $dataProvider
 * @var string $exportWidgetId
 */

StatisticAsset::register($this);

$columns = [
  [
    'attribute' => $model->group,
    'label' => $model->getGroups($model->group),
    'format' => 'raw',
    'value' => function ($item) use ($model) {
      $startDate = new DateTime($model->formatDateDB($model->start_date));
      $endDate = new DateTime($model->formatDateDB($model->end_date));
      /** @var bool $isMultiYear Статистика включает данные за разные года */
      $isMultiYear = $startDate->format('Y') != $endDate->format('Y');

      if (!isset($item['group'])) return null; // Бывают пустые строки, без поля группы в том числе
      $value = is_array($item['group']) ? current($item['group']) : $item['group'];
      // По датам (делаем ссылку на стату по часам):
      if ($model->isGroupingByDate()) {
        return Yii::$app->formatter->asPartnerDate($value);
      }
      // По месяцам или по неделям
      if ($model->isGroupingByMonth() || $model->isGroupingByWeek()) {
        $title = null;
        $weekOrMonth = explode('.', $item['group'])[1];
        // $item['date'] хранит случайную дату недели, так как данные сгрупированы
        $weekPeriod = $model->isGroupingByMonth()
          ? $model->getMonthPeriod($item['date'], $weekOrMonth)
          : $model->getWeekPeriod($item['date'], $weekOrMonth);
        $periodBegin = $weekPeriod[0]->format('d.m.Y');
        $periodEnd = $weekPeriod[1]->format('d.m.Y');
        $title = $periodBegin == $periodEnd ? $periodBegin : $periodBegin . ' - ' . $periodEnd;
        return Html::tag('div', $isMultiYear ? $item['group'] : $weekOrMonth, ['title' => $title]
        );
      }
      return $model->formatGroup($item);
    },
    'contentOptions' => function ($item) use ($model) {
      if (!isset($item['group'])) return null; // Бывают пустые строки, без поля группы в том числе
      return [
        'data-sort' => $model->isGroupingByHour() ? mktime($item['group']) : $item['group'],
      ];
    },
    'footer' => Yii::_t('statistic.statistic_total'),
    'groupType' => ResellerProfitStatistics::GROUP_TYPE_GROUP,
  ],
  [
    'attribute' => 'revshareIncome',
    'label' => $model->getGridColumnLabel('revshareIncome'),
    'format' => 'statisticSum',
    'footer' => $model->getResultValue('revshareIncome'),
    'groupType' => ResellerProfitStatistics::GROUP_TYPE_INCOME,
    'headerOptions' => [
      'data-group' => Yii::_t('statistic.reseller_income.group_type_income'),
    ],
  ],
  [
    'attribute' => 'cpaIncome',
    'label' => $model->getGridColumnLabel('cpaIncome'),
    'format' => 'statisticSum',
    'footer' => $model->getResultValue('cpaIncome'),
    'groupType' => ResellerProfitStatistics::GROUP_TYPE_INCOME,
    'headerOptions' => [
      'data-group' => Yii::_t('statistic.reseller_income.group_type_income'),
    ],
  ],
  [
    'attribute' => 'onetimeIncome',
    'label' => $model->getGridColumnLabel('onetimeIncome'),
    'format' => 'statisticSum',
    'footer' => $model->getResultValue('onetimeIncome'),
    'groupType' => ResellerProfitStatistics::GROUP_TYPE_INCOME,
    'headerOptions' => [
      'data-group' => Yii::_t('statistic.reseller_income.group_type_income'),
    ],
  ],
  [
    'attribute' => 'revshareConsumption',
    'label' => $model->getGridColumnLabel('revshareConsumption'),
    'format' => 'statisticSum',
    'footer' => $model->getResultValue('revshareConsumption'),
    'groupType' => ResellerProfitStatistics::GROUP_TYPE_CONSUMPTION,
    'headerOptions' => [
      'data-group' => Yii::_t('statistic.reseller_income.group_type_consumption'),
    ],
  ],
  [
    'attribute' => 'cpaConsumption',
    'label' => $model->getGridColumnLabel('cpaConsumption'),
    'format' => 'statisticSum',
    'footer' => $model->getResultValue('cpaConsumption'),
    'groupType' => ResellerProfitStatistics::GROUP_TYPE_CONSUMPTION,
    'headerOptions' => [
      'data-group' => Yii::_t('statistic.reseller_income.group_type_consumption'),
    ],
  ],
  [
    'attribute' => 'onetimeConsumption',
    'label' => $model->getGridColumnLabel('onetimeConsumption'),
    'format' => 'statisticSum',
    'footer' => $model->getResultValue('onetimeConsumption'),
    'groupType' => ResellerProfitStatistics::GROUP_TYPE_CONSUMPTION,
    'headerOptions' => [
      'data-group' => Yii::_t('statistic.reseller_income.group_type_consumption'),
    ],
  ],

  [
    'attribute' => 'resCompensations',
    'label' => $model->getGridColumnLabel('resCompensations'),
    'format' => 'statisticSum',
    'footer' => $model->getResultValue('resCompensations'),
    'groupType' => ResellerProfitStatistics::GROUP_TYPE_CORRECTIONS,
    'headerOptions' => [
      'data-group' => Yii::_t('statistic.reseller_income.group_type_corrections'),
    ],
    'visible' => $model->isShowCorrections(),
  ],
  [
    'attribute' => 'partCompensations',
    'label' => $model->getGridColumnLabel('partCompensations'),
    'format' => 'statisticSum',
    'footer' => $model->getResultValue('partCompensations'),
    'groupType' => ResellerProfitStatistics::GROUP_TYPE_CORRECTIONS,
    'headerOptions' => [
      'data-group' => Yii::_t('statistic.reseller_income.group_type_corrections'),
    ],
    'visible' => $model->isShowCorrections(),
  ],
  [
    'attribute' => 'resPenalties',
    'label' => $model->getGridColumnLabel('resPenalties'),
    'format' => 'statisticSum',
    'footer' => $model->getResultValue('resPenalties'),
    'groupType' => ResellerProfitStatistics::GROUP_TYPE_CORRECTIONS,
    'headerOptions' => [
      'data-group' => Yii::_t('statistic.reseller_income.group_type_corrections'),
    ],
    'visible' => $model->isShowCorrections(),
  ],
  [
    'attribute' => 'partPenalties',
    'label' => $model->getGridColumnLabel('partPenalties'),
    'format' => 'statisticSum',
    'footer' => $model->getResultValue('partPenalties'),
    'groupType' => ResellerProfitStatistics::GROUP_TYPE_CORRECTIONS,
    'headerOptions' => [
      'data-group' => Yii::_t('statistic.reseller_income.group_type_corrections'),
    ],
    'visible' => $model->isShowCorrections(),
  ],



  [
    'attribute' => 'totalIncome',
    'label' => $model->getGridColumnLabel('totalIncome'),
    'format' => 'statisticSum',
    'value' => function ($statRow) use ($model) {
      return $model->getTotalValue($statRow, 'totalIncome');
    },
    'footer' => $model->getResultValue('totalIncome'),
    'groupType' => ResellerProfitStatistics::GROUP_TYPE_TOTAL,
    'headerOptions' => [
      'data-group' => Yii::_t('statistic.reseller_income.group_type_total'),
    ],
  ],
  [
    'attribute' => 'totalConsumption',
    'label' => $model->getGridColumnLabel('totalConsumption'),
    'format' => 'statisticSum',
    'value' => function ($statRow) use ($model) {
      return $model->getTotalValue($statRow, 'totalConsumption');
    },
    'footer' => $model->getResultValue('totalConsumption'),
    'groupType' => ResellerProfitStatistics::GROUP_TYPE_TOTAL,
    'headerOptions' => [
      'data-group' => Yii::_t('statistic.reseller_income.group_type_total'),
    ],
  ],
  [
    'attribute' => 'totalCorrections',
    'label' => $model->getGridColumnLabel('totalCorrections'),
    'format' => 'statisticSum',
    'value' => function ($statRow) use ($model) {
      return $model->getTotalValue($statRow, 'totalCorrections');
    },
    'visible' => $model->isShowCorrections(),
    'footer' => $model->getResultValue('totalCorrections'),
    'groupType' => ResellerProfitStatistics::GROUP_TYPE_TOTAL,
    'headerOptions' => [
      'data-group' => Yii::_t('statistic.reseller_income.group_type_total'),
    ],
  ],
  [
    'attribute' => 'totalProfit',
    'label' => $model->getGridColumnLabel('totalProfit'),
    'format' => 'statisticSum',
    'value' => function ($statRow) use ($model) {
      return $model->getTotalValue($statRow, 'totalProfit');
    },
    'footer' => $model->getResultValue('totalProfit'),
    'groupType' => ResellerProfitStatistics::GROUP_TYPE_TOTAL,
    'headerOptions' => [
      'data-group' => Yii::_t('statistic.reseller_income.group_type_total'),
    ],
    'visible' => $model->isShowCorrections(),
  ],
  [
    'attribute' => 'totalNetProfit',
    'label' => $model->getGridColumnLabel('totalNetProfit'),
    'format' => 'statisticSum',
    'value' => function ($statRow) use ($model) {
      return $model->getTotalValue($statRow, 'totalNetProfit');
    },
    'footer' => $model->getResultValue('totalNetProfit'),
    'groupType' => ResellerProfitStatistics::GROUP_TYPE_TOTAL,
    'headerOptions' => [
      'data-group' => Yii::_t('statistic.reseller_income.group_type_total'),
    ],
  ],
];
foreach ($columns as $key => $column) {
  $columns[$key]['class'] = \mcms\statistic\components\grid\StatisticColumn::class;
}

$toolbar = ExportMenu::widget([
  'id' => $exportWidgetId,
  'dataProvider' => $dataProvider,
  'filterFormId' => 'statistic-filter-form',
  'isPartners' => true,
  'statisticModel' => $model,
  'dropdownOptions' => [
    'label' => Yii::_t('main.export'),
    'class' => 'btn-xs btn-success',
    'menuOptions' => ['class' => 'pull-right']
  ],
  'columnSelectorOptions' => ['class' => 'btn-xs btn-success'],
  'columnSelectorMenuOptions' => ['class' => 'list-inline dropdown-menu pull-right js-status-update'],
  'columns' => $columns,
  'template'=>'{menu}',
  'target' => ExportMenu::TARGET_BLANK,
  'filename' => Yii::_t('main.reseller_income'),
  'exportConfig' => [
    ExportMenu::FORMAT_HTML => false,
    ExportMenu::FORMAT_PDF => false,
    ExportMenu::FORMAT_EXCEL => false,
  ],
]);
$toolbar .=  Html::dropDownList('table-filter', null, [], [
  'id' => 'table-filter',
  'class' => 'selectpicker menu-right col-i',
  'multiple' => true,
  'title' => yii\bootstrap\Html::icon('cog') . ' ' . Yii::_t('statistic.statistic.filter_table'),
  'data-count-selected-text' => yii\bootstrap\Html::icon('cog') . ' ' . Yii::_t('statistic.statistic.filter_table'),
  'data-selected-text-format' => 'count>1',
  'data-dropdown-align-right' => 1,
]);
?>

<?php ContentViewPanel::begin([
  'padding' => false,
  'toolbar' => $toolbar,
]);
?>

  <div class="default-filters-block">
    <?= $this->render('_search', [
      'model' => $model,
      'countries' => $countries,
      'countriesId' => $countriesId,
      'operatorsId' => $operatorsId,
      'filterDatePeriods' => isset($filterDatePeriods) ? $filterDatePeriods : null,
    ]) ?>
  </div>

<?php Pjax::begin(['id' => 'statistic-pjax']); ?>
<?= AdminGridView::widget([
  'dataProvider' => $dataProvider,
  'columns' => $columns,
  'showFooter' => $showFooter,
  'pageSummaryRowOptions' => ['class' => 'kv-page-summary'],
  'layout' => '{items}',
  'beforeHeader' => $model->getBeforeHeader($columns),
  'tableOptions' => [
    'class' => 'table nowrap text-center data-table dataTable',
    'id' => 'statistic-data-table',
    'data-empty-result' => Yii::t('yii', 'No results found.'),
  ],
]); ?>
<?php Pjax::end(); ?>

<?php ContentViewPanel::end() ?>