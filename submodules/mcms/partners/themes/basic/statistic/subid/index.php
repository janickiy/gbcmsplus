<?php

use mcms\partners\components\subidStat\Group;
use mcms\partners\assets\LabelStatAsset;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\widgets\Pjax;
use rgk\export\ExportMenu;
use mcms\partners\components\widgets\StatisticPagerWidget;

LabelStatAsset::register($this);
/** @var ActiveDataProvider $dataProvider  */
/** @var \mcms\partners\components\subidStat\FormModel $searchModel  */
/** @var mcms\common\web\View $this */
/** @var \mcms\statistic\Module $statisticModule */
/** @var string $exportWidgetId */
/** @var \mcms\partners\components\mainStat\FiltersDataProvider $filtersDP */

$formatter = Yii::$app->formatter;
?>

<div class="container-fluid">
  <div class="bgf">
    <div class="statistics">

    <?php
    $gridColumns = [
      [
        'attribute' => 'subid1',
        'label' => Yii::_t('statistic.subid1'),
        'visible' => in_array(Group::BY_SUBID1, $searchModel->groups, true),
        'contentOptions' => [
          'data-label' => Yii::_t('statistic.subid1'),
          'data-group' => 0,
        ],
        'headerOptions' => [
          'class' => 'all',
        ]
      ],
      [
        'attribute' => 'subid2',
        'label' => Yii::_t('statistic.subid2'),
        'visible' => in_array(Group::BY_SUBID2, $searchModel->groups, true),
        'contentOptions' => [
          'data-label' => Yii::_t('statistic.subid2'),
          'data-group' => 0,
        ],
        'headerOptions' => [
          'class' => 'min-tablet-l',
        ]
      ],

      /** TRAFFIC */
      [
        'attribute' => 'hits',
        'format' => 'integer',
        'label' => Yii::_t('statistic.statistic_traffic-hits'),
        'headerOptions' => [
          'data-group' => 1,
          'data-column' => 1,
          'class' => 'min-tablet-l',
        ],
        'contentOptions' => [
          'data-group' => '1',
        ],
      ],
      [
        'attribute' => 'uniques',
        'format' => 'integer',
        'label' => Yii::_t('statistic.statistic_traffic-uniques'),
        'headerOptions' => [
          'data-group' => 1,
          'data-column' => 2,
          'class' => 'min-tablet-l',
        ],
        'contentOptions' => [
          'data-group' => '1',
        ],
      ],
      [
        'attribute' => 'tb',
        'format' => 'integer',
        'label' => Yii::_t('statistic.statistic_traffic-tb'),
        'contentOptions' => [
          'data-group' => '1',
        ],
        'headerOptions' => [
          'data-group' => 1,
          'data-column' => 3,
          'class' => 'min-tablet-l',
        ]
      ],
      [
        'attribute' => 'accepted',
        'label' => Yii::_t('statistic.statistic_traffic-accepted'),
        'format' => 'integer',
        'contentOptions' => [
          'data-group' => '1',
        ],
        'headerOptions' => [
          'data-group' => 1,
          'data-column' => 4,
          'class' => 'min-tablet-l',
        ]
      ],


      /** REVSHARE */
      [
        'attribute' => 'revshare_ons',
        'format' => 'integer',
        'label' => Yii::_t('statistic.statistic_revshare-ons'),
        'contentOptions' => [
          'data-group' => '2'
        ],
        'visible' => !$searchModel->isCPA(),
        'headerOptions' => [
          'data-group' => 2,
          'data-column' => 5,
          'class' => 'min-tablet-l',
        ],
      ],
      [
        'attribute' => 'revshare_ratio',
        'value' => function ($row) use ($searchModel) {
          $revshareCr = round(ArrayHelper::getValue($row, 'revshare_cr'), 3);
          $revshareRatio = round((float)ArrayHelper::getValue($row, 'revshare_ratio'), 1);
          if ($searchModel->isShowRatio()) {
            return sprintf('1:%s (%s%%)', $revshareRatio, $revshareCr);
          }
          return sprintf('%s%%', $revshareCr);
        },
        'label' => $searchModel->isShowRatio()
          ? Yii::_t('statistic.statistic_revshare-ratio')
          : Yii::_t('statistic.statistic_revshare-cr'),
        'contentOptions' => [
          'data-group' => '2',
        ],
        'visible' => !$searchModel->isCPA(),
        'headerOptions' => [
          'data-group' => 2,
          'data-column' => 6,
          'class' => 'min-tablet-l',
        ],
      ],
      [
        'attribute' => 'revshare_offs',
        'format' => 'integer',
        'label' => Yii::_t('statistic.statistic_revshare-offs'),
        'visible' => !$searchModel->isCPA(),
        'headerOptions' => [
          'data-group' => 2,
          'data-column' => 7,
          'class' => 'min-tablet-l',
        ],
        'contentOptions' => [
          'data-group' => '2',
        ],
      ],
      [
        'attribute' => 'revshare_rebills',
        'format' => 'integer',
        'label' => Yii::_t('statistic.statistic_revshare-rebills'),
        'visible' => !$searchModel->isCPA(),
        'headerOptions' => [
          'data-group' => 2,
          'data-column' => 8,
          'class' => 'min-tablet-l',
        ],
        'contentOptions' => [
          'data-group' => '2',
        ],
      ],
      [
        'attribute' => 'revshare_profit',
        'value' => function ($row) use ($formatter, $searchModel) {
          return $formatter->asStatisticSum(ArrayHelper::getValue($row, 'revshare_profit')) . ' ' . $formatter->asCurrencyIcon($searchModel->getCurrency());
        },
        'encodeLabel' => false,
        'label' => Yii::_t('statistic.statistic_revshare-sum', ['currency' => '']),
        'visible' => !$searchModel->isCPA(),
        'headerOptions' => [
          'data-group' => 2,
          'data-column' => 9,
          'class' => 'min-tablet-l',
        ],
        'contentOptions' => [
          'data-group' => '2',
        ],
      ],

      /** CPA */
      [
        'attribute' => 'cpa_ons',
        'label' => Yii::_t('statistic.statistic_cpa-count'),
        'format' => 'integer',
        'visible' => !$searchModel->isRevshare(),
        'contentOptions' => [
          'data-group' => '3',
        ],
        'headerOptions' => [
          'data-group' => 3,
          'data-column' => 10,
          'class' => 'min-tablet-l',
        ]
      ],

      [
        'attribute' => 'cpa_ecpm',
        'value' => function ($row) use ($formatter, $searchModel) {
          return $formatter->asStatisticSum(ArrayHelper::getValue($row, 'cpa_ecpm')) . ' ' . $formatter->asCurrencyIcon($searchModel->getCurrency());
        },
        'label' => Yii::_t('statistic.statistic_cpa-ecpm', ['currency' => '']),
        'encodeLabel' => false,
        'visible' => !$searchModel->isRevshare(),
        'headerOptions' => [
          'data-group' => 3,
          'data-column' => 11,
          'class' => 'min-tablet-l',
        ],
        'contentOptions' => [
          'data-group' => '3',
        ],
      ],

      [
        'attribute' => 'cpa_ratio',
        'value' => function ($row) use ($searchModel) {
          $cpaCr = round(ArrayHelper::getValue($row, 'cpa_cr'), 3);
          $cpaRatio = round((float)ArrayHelper::getValue($row, 'cpa_ratio'), 1);
          if ($searchModel->isShowRatio()) {
            return sprintf('1:%s (%s%%)', $cpaRatio, $cpaCr);
          }
          return sprintf('%s%%', $cpaCr);
        },
        'label' => $searchModel->isShowRatio()
          ? Yii::_t('statistic.statistic_cpa-ratio')
          : Yii::_t('statistic.statistic_cpa-cr'),
        'visible' => !$searchModel->isRevshare(),
        'headerOptions' => [
          'data-group' => 3,
          'data-column' => 12,
          'class' => 'min-tablet-l',
        ],
        'contentOptions' => [
          'data-group' => '3',
        ],
      ],

      [
        'attribute' => 'cpa_profit',
        'value' => function ($row) use ($formatter, $searchModel) {
          return $formatter->asStatisticSum(ArrayHelper::getValue($row, 'cpa_profit')) . ' ' . $formatter->asCurrencyIcon($searchModel->getCurrency());
        },
        'label' => Yii::_t('statistic.statistic_cpa-sum', ['currency' => '']),
        'encodeLabel' => false,
        'visible' => !$searchModel->isRevshare(),
        'headerOptions' => [
          'data-group' => 3,
          'data-column' => 13,
          'class' => 'min-tablet-l',
        ],
        'contentOptions' => [
          'data-group' => '3',
        ],
      ],

      /** SUM */
      [
        'attribute' => 'total_profit',
        'label' => Yii::_t('statistic.statistic_total-sum', ['currency' => '']),
        'value' => function ($row) use ($formatter, $searchModel) {
          return $formatter->asStatisticSum(ArrayHelper::getValue($row, 'total_profit')) . ' ' . $formatter->asCurrencyIcon($searchModel->getCurrency());
        },
        'encodeLabel' => false,
        'headerOptions' => [
          'data-group' => 4,
          'class' => 'min-tablet-l',
        ],
        'contentOptions' => [
          'data-group' => $searchModel->isRevshare() ? '4' : '3',
        ],
      ],
    ];

    ?>

    <?= $this->render('_search', [
      'model' => $searchModel,
      'filterDatePeriods' => isset($filterDatePeriods) ? $filterDatePeriods : [],
      'shouldHideGrouping' => $shouldHideGrouping,
      'revshareOrCpaFilter' => $revshareOrCpaFilter,
      'groupBy' => isset($groupBy) ? $groupBy : null,
      'filtersDP' => $filtersDP,
    ]) ?>
      <div id="export" class="statistics_collapsed">
        <div class="row">
          <div class="col-xs-12 export">
            <span><?= Yii::_t('statistic.select_export_format') ?>: </span>
            <?=
            ExportMenu::widget([
              'id' => $exportWidgetId,
              'dataProvider' => $dataProvider,
              'filterFormId' => 'statistic-filter-form',
              'isPartners' => true,
              'columns' => array_map(function($n) {
                if(isset($n['label'])) {
                  $n['label'] = str_replace('<i class="icon-euro"></i>', '€', $n['label']);
                  $n['label'] = str_replace('<i class="icon-ruble"></i>', '₽', $n['label']);
                }
                return $n;
              }, $gridColumns),
              'asDropdown' => false,
              'showConfirmAlert' => false,
              'target' => ExportMenu::TARGET_BLANK,
              'filename' => $exportFileName,
              'exportConfig' => [
                ExportMenu::FORMAT_CSV => [
                  'label' => Yii::_t('statistic.csv_text_format'),
                  'icon' => 'icon-csv',
                  'iconOptions' => ['class' => 'text-primary'],
                  'linkOptions' => [''],
                  'options' => ['class' => 'export-formate'],
                  'mime' => 'application/csv',
                  'extension' => 'csv',
                  'writer' => ExportMenu::FORMAT_CSV,
                ],
                ExportMenu::FORMAT_EXCEL_X => [
                  'label' => Yii::_t('statistic.document_excel'),
                  'icon' => 'icon-xls',
                  'linkOptions' => [],
                  'options' => ['class' => 'export-formate'],
                  'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                  'extension' => 'xlsx',
                  'writer' => ExportMenu::FORMAT_EXCEL_X,
                ],
                ExportMenu::FORMAT_HTML => false,
                ExportMenu::FORMAT_PDF => false,
                ExportMenu::FORMAT_TEXT => false,
                ExportMenu::FORMAT_EXCEL =>  false,
              ],
            ]);
            ?>
          </div>
        </div>
        <div class="export-bottom">
          <span><i class="icon-danger"></i> <?= Yii::_t('statistic.use_table_filter') ?></span>
        </div>
      </div>
    </div>

    <?php Pjax::begin(['id' => 'statistic-pjax', 'timeout' => false, 'enablePushState' => false]); ?>

    <?php if(!$dataProvider->totalCount):?>
      <div class="empty_data">
        <i class="icon-no_data"></i>
        <span><?= Yii::_t('main.no_results_found') ?></span>
      </div>

    <?php else:?>
    <div id="example_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">

      <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'resizableColumns' => false,
        'tableOptions' => [
          'id' => 'mark',
          'class' => 'table table-striped marks_table table-head_group nowrap main_dt text-center dataTable',
          'data-skip-summary-calculation' => '0',
          'data-offset' => ($searchModel->group === Group::BY_SUBID12) ? 2 : 1,
          'data-empty-result' => Yii::t('yii', 'No results found')
        ],
        'options' => [
          'class' => 'grid-view',
          'style' => 'overflow:auto' // иначе таблица растягивается за пределы экрана.
        ],
        'export' => false,
        'pager' => ['class' => StatisticPagerWidget::class],
        'layout' => '{items}{pager}',
        'beforeHeader' => '
          <th class="all" data-group="0" colspan="1"></th>
          ' . ($searchModel->group === Group::BY_SUBID12 ? '<th colspan="1" data-group="0"></th>' : '') . '
          <th data-group="1" colspan="4">' . Yii::_t('statistic.statistic_traffic') . '</th>'
          .
          (!$searchModel->isCPA() ? '<td data-group="2" colspan="5">' . Yii::_t('statistic.statistic_revshare') . '</td>' : '')
          .
          (!$searchModel->isRevshare() ? '<td data-group="3" colspan="4">' . Yii::_t('statistic.statistic_cpa') . '</td>' : '')
          .
          '<th colspan="1"></th>'
        ,
        'emptyCell' => 0,
        'bordered' => false,
        'columns' => $gridColumns,
      ]) ?>

    </div>
    <?php endif ?>
    <?php Pjax::end(); ?>
  </div>
</div>
