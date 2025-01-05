<?php

use mcms\partners\assets\DatePickerAsset;
use kartik\grid\GridView;
use mcms\partners\assets\StatAsset;
use mcms\partners\components\mainStat\ComplainLink;
use mcms\partners\components\mainStat\FiltersDataProvider;
use mcms\partners\components\mainStat\FormModel;
use mcms\partners\components\mainStat\TBLink;
use mcms\statistic\components\mainStat\DataProvider;
use mcms\statistic\components\mainStat\Group;
use mcms\partners\components\mainStat\Row;
use yii\widgets\Pjax;
use mcms\common\helpers\ArrayHelper;

StatAsset::register($this);
DatePickerAsset::register($this);

/** @var DataProvider $dataProvider */
/** @var FormModel $model */
/** @var string[] $groupBy */
/** @var mcms\common\web\View $this */
/** @var mcms\partners\components\PartnerFormatter $formatter */
///** @var bool $tbSellIsEnabled */
/** @var bool $isRatioByUniquesEnabled */
/** @var bool $isVisibleComplains */
/** @var string[] $revshareOrCpaFilter */
/** @var array $filterDatePeriods */
/** @var string $exportFileName */
/** @var string $exportWidgetId */
/** @var FiltersDataProvider $filtersDataProvider */

$formatter = Yii::$app->formatter;
$groupKey = reset($model->groups);

$gridColumns = [
  [
    'label' => Group::getGroupColumnLabel($groupKey),
    'format' => 'raw',
    'footer' => Yii::_t('statistic.main_statistic_refactored.footer_total'),
    'value' => function (Row $row) use ($groupKey) {
      /** @var Group  $group */
      $group = ArrayHelper::getValue($row->getGroups(), $groupKey);
      if (!$group || $group->getValue() === false) {
        return null;
      }
      return $group->getFormattedValue();
    },
    'contentOptions' => function (Row $row) use ($groupKey) {
      /** @var Group  $group */
      $group = ArrayHelper::getValue($row->getGroups(), $groupKey);
      $dataSort = $group->getValue();
      if ($groupKey === Group::BY_HOURS) {
        $dataSort = mktime($dataSort);
      }
      if (in_array($groupKey, [Group::BY_PLATFORMS, Group::BY_OPERATORS, Group::BY_COUNTRIES], true)) {
        // иначе показывал в гриде название платформы, а сортировал по айди
        $dataSort = $group->getFormattedValue();
      }
      return [
        'data-label' => Group::getGroupColumnLabel($groupKey),
        'data-group' => 0,
        'data-sort' => $dataSort
      ];
    },
    'headerOptions' => [
      'class' => 'min-tablet-l',
    ],
    'footerOptions' => [
      'data-label' => Yii::_t('statistic.statistic_total'),
      'data-group' => 0,
    ],

  ],
  /** TRAFFIC */
  [
    'attribute' => 'hits',
    'format' => 'integer',
    'label' => Yii::_t('statistic.statistic_traffic-hits'),
    'headerOptions' => [
      'data-group' => '1',
      'data-column' => '1',
      'class' => 'min-tablet-l',
    ],
    'contentOptions' => function (Row $row) {
      return ['data-group' => '1', 'data-info' => Yii::_t('statistic.uniques', ['n' => $row->getUniques()])];
    },
    'footerOptions' => [
      'data-label' => Yii::_t('statistic.statistic_traffic-hits'),
      'data-group' => 1,
      'data-info' => Yii::_t('statistic.uniques', ['n' => $dataProvider->footerRow->getHits()])
    ],
    'footer' => $formatter->asInteger($dataProvider->footerRow->getHits())
  ],
  [
    'attribute' => 'uniques',
    'format' => 'integer',
    'label' => Yii::_t('statistic.statistic_traffic-uniques'),
    'headerOptions' => [
      'data-group' => '1',
      'data-column' => '2',
      'class' => 'min-tablet-l',
    ],
    'contentOptions' => [
      'data-group' => '1',
    ],
    'footerOptions' => [
      'data-label' => Yii::_t('statistic.statistic_traffic-uniques'),
      'data-group' => 1,
    ],
    'footer' => $formatter->asInteger($dataProvider->footerRow->getUniques())
  ],
  [
    'format' => 'raw',
    'label' => Yii::_t('statistic.statistic_traffic-tb'),
    'value' => function (Row $row) {
      return TBLink::create($row)->toString();
    },
    'headerOptions' => [
      'data-group' => '1',
      'data-column' => '3',
      'class' => 'min-tablet-l',
    ],
    'contentOptions' => function (Row $row) {
      return [
        'data-group' => '1',
        'data-sort' => $row->getTb()
      ];
    },
    'footerOptions' => [
      'data-label' => Yii::_t('statistic.statistic_traffic-tb'),
      'data-group' => 1,
    ],
    'footer' => $formatter->asInteger($dataProvider->footerRow->getTb())
  ],
  [
    'attribute' => 'accepted',
    'label' => Yii::_t('statistic.statistic_traffic-accepted'),
    'format' => 'integer',
    'headerOptions' => [
      'data-group' => '1',
      'data-column' => '4',
      'class' => 'min-tablet-l',
    ],
    'contentOptions' => [
      'data-group' => '1',
    ],
    'footerOptions' => [
      'data-label' => Yii::_t('statistic.statistic_traffic-accepted'),
      'data-group' => 1,
    ],
    'footer' => $formatter->asInteger($dataProvider->footerRow->getAccepted())
  ],

  /** REVSHARE */
  [
    'attribute' => 'ons',
    'format' => 'integer',
    'label' => Yii::_t('statistic.statistic_revshare-ons'),
    'headerOptions' => [
      'data-group' => '2',
      'data-column' => '5',
      'class' => 'min-tablet-l',
    ],
    'contentOptions' => function (Row $row) {
      return ['data-group' => '2', 'data-info' => Yii::_t('statistic.subscriptions', ['n' => $row->getOns()])];
    },
    'visible' => $model->revshareOrCpa !== FormModel::SELECT_CPA,
    'footerOptions' => [
      'data-label' => Yii::_t('statistic.statistic_revshare-ons'),
      'data-group' => 2,
      'data-info' => Yii::_t('statistic.subscriptions', ['n' => $dataProvider->footerRow->getOns()])
    ],
    'footer' => $formatter->asInteger($dataProvider->footerRow->getOns())
  ],
  [
    'label' => $showRatio
      ? Yii::_t('statistic.statistic_revshare-ratio')
      : Yii::_t('statistic.statistic_revshare-cr'),
    'value' => function (Row $row) use ($showRatio) {
      return $showRatio
        ? sprintf('%s (%s%%)', $row->getRevshareRatio(), round($row->getRevshareCr(), 3))
        : sprintf('%s%%', round($row->getRevshareCr(), 3));
    },
    'headerOptions' => [
      'data-group' => '2',
      'data-column' => '6',
      'class' => 'min-tablet-l',
    ],
    'contentOptions' => function (Row $row) {
      return [
        'data-sort' => $row->getRevshareRatio('%s'),
        'data-group' => '2',
      ];
    },
    'visible' => $model->revshareOrCpa !== FormModel::SELECT_CPA,
    'footerOptions' => [
      'data-label' => Yii::_t('statistic.statistic_revshare-ratio'),
      'data-group' => 2,
    ],
    'footer' => $showRatio
      ? sprintf(
        '%s (%s%%)',
        $dataProvider->footerRow->getRevshareRatio('1:%s'),
        round($dataProvider->footerRow->getRevshareCr(), 3)
      )
      : sprintf('%s%%', round($dataProvider->footerRow->getRevshareCr(), 3))
  ],
  [
    'attribute' => 'offs',
    'format' => 'integer',
    'label' => Yii::_t('statistic.statistic_revshare-offs'),
    'headerOptions' => [
      'data-group' => '2',
      'data-column' => '7',
      'class' => 'min-tablet-l',
    ],
    'contentOptions' => [
      'data-group' => '2',
    ],
    'visible' => $model->revshareOrCpa !== FormModel::SELECT_CPA,
    'footerOptions' => [
      'data-label' => Yii::_t('statistic.statistic_revshare-offs'),
      'data-group' => 2,
    ],
    'footer' => $formatter->asInteger($dataProvider->footerRow->getOffs())
  ],
  [
    'attribute' => 'rebills',
    'format' => 'integer',
    'label' => Yii::_t('statistic.statistic_revshare-rebills'),
    'headerOptions' => [
      'data-group' => '2',
      'data-column' => '8',
      'class' => 'min-tablet-l',
    ],
    'contentOptions' => [
      'data-group' => '2',
    ],
    'visible' => $model->revshareOrCpa !== FormModel::SELECT_CPA,
    'footerOptions' => [
      'data-label' => Yii::_t('statistic.statistic_revshare-rebills'),
      'data-group' => 2,
    ],
    'footer' => $formatter->asInteger($dataProvider->footerRow->getRebills())
  ],
  [
    'label' => Yii::_t('statistic.statistic_revshare-sum', ['currency' => '']),
    'encodeLabel' => false,
    'attribute' => 'partnerRevshareProfit',
    'value' => function (Row $row) use ($formatter) {
      return $formatter->asStatisticSum($row->getPartnerRevshareProfit()) . ' ' . $formatter->asCurrencyIcon($row->getCurrency());
    },
    'format' => 'raw',
    'visible' => $model->revshareOrCpa !== FormModel::SELECT_CPA,
    'headerOptions' => [
      'data-group' => '2',
      'data-column' => '9',
      'class' => 'min-tablet-l',
    ],
    'contentOptions' => [
      'data-group' => '2',
    ],
    'footerOptions' => [
      'data-label' => Yii::_t('statistic.statistic_revshare-sum', ['currency' => $model->getCurrency()]),
      'data-group' => 2,
    ],
    'footer' => $formatter->asStatisticSum($dataProvider->footerRow->getPartnerRevshareProfit()) . ' ' . $formatter->asCurrencyIcon($dataProvider->footerRow->getCurrency())
  ],

  /** CPA */
  [
    'attribute' => 'cpaOns',
    'label' => Yii::_t('statistic.statistic_cpa-count'),
    'format' => 'integer',
    'contentOptions' => function (Row $row) {
      return ['data-group' => '3', 'data-info' => Yii::_t('statistic.accepted', ['n' => $row->getCpaOns()])];
    },
    'headerOptions' => [
      'data-group' => '3',
      'data-column' => '10',
      'class' => 'min-tablet-l',
    ],
    'visible' => $model->revshareOrCpa !== FormModel::SELECT_REVSHARE,
    'footerOptions' => [
      'data-label' => Yii::_t('statistic.statistic_cpa-count'),
      'data-group' => 3,
      'data-info' => Yii::_t('statistic.accepted', ['n' => $dataProvider->footerRow->getCpaOns()])
    ],
    'footer' => $formatter->asInteger($dataProvider->footerRow->getCpaOns())
  ],
  [
    'attribute' => 'eCPM',
    'label' => Yii::_t('statistic.statistic_cpa-ecpm', ['currency' => '']),
    'encodeLabel' => false,
    'format' => 'raw',
    'value' => function (Row $row) use ($formatter) {
      return $formatter->asStatisticSum($row->getECPM()) . ' ' . $formatter->asCurrencyIcon($row->getCurrency());
    },
    'visible' => $model->revshareOrCpa !== FormModel::SELECT_REVSHARE,
    'headerOptions' => [
      'data-group' => '3',
      'data-column' => '11',
      'class' => 'min-tablet-l',
    ],
    'contentOptions' => [
      'data-group' => '3',
    ],
    'footerOptions' => [
      'data-label' => Yii::_t('statistic.statistic_cpa-ecpm', ['currency' => $model->getCurrency()]),
      'data-group' => 3,
    ],
    'footer' => $formatter->asStatisticSum($dataProvider->footerRow->getECPM()) . ' ' . $formatter->asCurrencyIcon($dataProvider->footerRow->getCurrency())
  ],

  [
    'label' => $showRatio
      ? Yii::_t('statistic.statistic_cpa-ratio')
      : Yii::_t('statistic.statistic_cpa-cr'),
    'value' => function (Row $row) use ($showRatio) {
      return $showRatio
        ? sprintf('%s (%s%%)', $row->getCpaRatio(), round($row->getCpaCr(), 3))
        : sprintf('%s%%', round($row->getCpaCr(), 3));
    },
    'headerOptions' => [
      'data-group' => '3',
      'data-column' => '12',
      'class' => 'min-tablet-l',
    ],
    'contentOptions' => function (Row $row) {
      return [
        'data-group' => '3',
        'data-sort' => $row->getCpaRatio('%s')
      ];
    },
    'visible' => $model->revshareOrCpa !== FormModel::SELECT_REVSHARE,
    'footerOptions' => [
      'data-label' => Yii::_t('statistic.statistic_cpa-ratio'),
      'data-group' => 3,
    ],
    'footer' => $showRatio
      ? sprintf(
        '%s (%s%%)',
        $dataProvider->footerRow->getCpaRatio(),
        round($dataProvider->footerRow->getCpaCr(), 3)
      )
      : sprintf('%s%%', round($dataProvider->footerRow->getCpaCr(), 3))
  ],
  [
    'attribute' => 'cpaPartnerProfit',
    'label' => Yii::_t('statistic.statistic_cpa-sum', ['currency' => '']),
    'encodeLabel' => false,
    'value' => function (Row $row) use ($formatter) {
      return $formatter->asStatisticSum($row->getCpaPartnerProfit()) . ' ' . $formatter->asCurrencyIcon($row->getCurrency());
    },
    'format' => 'raw',
    'visible' => $model->revshareOrCpa !== FormModel::SELECT_REVSHARE,
    'headerOptions' => [
      'data-group' => '3',
      'data-column' => '13',
      'class' => 'min-tablet-l',
    ],
    'contentOptions' => [
      'data-group' => '3',
    ],
    'footerOptions' => [
      'data-label' => Yii::_t('statistic.statistic_cpa-sum', ['currency' => $model->getCurrency()]),
      'data-group' => 3,
    ],
    'footer' => $formatter->asStatisticSum($dataProvider->footerRow->getCpaPartnerProfit()) . ' ' . $formatter->asCurrencyIcon($dataProvider->footerRow->getCurrency())
  ],

  [
    'attribute' => 'partnerTotalProfit',
    'label' => Yii::_t('statistic.statistic_total-sum', ['currency' => '']),
    'encodeLabel' => false,
    'value' => function (Row $row) use ($formatter) {
      return $formatter->asStatisticSum($row->getPartnerTotalProfit()) . ' ' . $formatter->asCurrencyIcon($row->getCurrency());
    },
    'format' => 'raw',
    'headerOptions' => [
      'data-group' => '4',
      'data-column' => '16',
      'class' => 'min-tablet-l',
    ],
    'contentOptions' => function (Row $row) use ($formatter) {
      return ['data-group' => '4', 'data-info' => $formatter->asStatisticSum($row->getPartnerTotalProfit())];
    },
    'footerOptions' => [
      'data-label' => Yii::_t('statistic.statistic_total-sum', ['currency' => $model->getCurrency()]),
      'data-group' => 4,
      'data-info' => $formatter->asStatisticSum($dataProvider->footerRow->getPartnerTotalProfit())
    ],
    'footer' => $formatter->asStatisticSum($dataProvider->footerRow->getPartnerTotalProfit()) . ' ' . $formatter->asCurrencyIcon($dataProvider->footerRow->getCurrency())
  ],


  [
    'attribute' => 'complains',
    'label' => Yii::_t('statistic.statistic.complains_count'),
    'encodeLabel' => false,
    'format' => 'raw',
    'visible' => $isVisibleComplains,
    'value' => function (Row $row) {
      return ComplainLink::create($row)->toString();
    },
    'headerOptions' => [
      'data-group' => '4',
      'data-column' => '15',
      'class' => 'min-tablet-l',
    ],
    'contentOptions' => function (Row $row) use ($formatter) {
      return [
        'data-group' => '4',
        'data-info' => $formatter->asInteger($row->getComplains()),
        'data-sort' => $row->getComplains()
      ];
    },
    'footerOptions' => [
      'data-label' => Yii::_t('statistic.statistic.complains_count'),
      'data-group' => 4,
      'data-info' => $formatter->asInteger($dataProvider->footerRow->getComplains()),
    ],
    'footer' => $formatter->asInteger($dataProvider->footerRow->getComplains()),
  ],
];
$t=1;
?>


<div class="container-fluid">
  <div class="bgf">
    <div class="statistics">

      <?= $this->render('_search', [
        'model' => $model,
        'filterDatePeriods' => $filterDatePeriods,
        'groupBy' => $groupBy,
        'isRatioByUniquesEnabled' => $isRatioByUniquesEnabled,
        'revshareOrCpaFilter' => $revshareOrCpaFilter,
        'filtersDataProvider' => $filtersDataProvider,
      ]) ?>

      <?= $this->render('_export_menu', [
        'exportFileName' => $exportFileName,
        'dataProvider' => $dataProvider,
        'gridColumns' => $gridColumns,
        'exportWidgetId' => $exportWidgetId,
      ]);
      ?>

    </div>

    <?php Pjax::begin(['id' => 'statistic-pjax']); ?>

    <?php if (!$dataProvider->totalCount) : ?>
      <div class="empty_data">
        <i class="icon-no_data"></i>
        <span><?= Yii::_t('main.no_results_found') ?></span>
      </div>

    <?php else : ?>
      <div id="example_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">

        <?= GridView::widget([
          'dataProvider' => $dataProvider,
          'resizableColumns' => false,
          'tableOptions' => [
            'id' => 'example',
            'class' => 'table table-striped table-head_group nowrap main_dt text-center dataTable ' . (0 ? 'by-hour' : '') . (1 ? 'by-date' : ''),
            'data-skip-summary-calculation' => '0',
            'data-empty-result' => Yii::t('yii', 'No results found')
          ],
          'options' => [
            'class' => 'grid-view',
            'style' => 'overflow:auto' // иначе таблица растягивается за пределы экрана.
          ],
          'export' => false,
          'layout' => '{items}',
          'beforeHeader' => '<th class="sorting" colspan="1"></th>
          <th data-group="1" colspan="4">' . Yii::_t('statistic.statistic_traffic') . '</th>' .
            ($model->revshareOrCpa !== FormModel::SELECT_CPA ? '<th data-group="2" colspan="5">' . Yii::_t('statistic.statistic_revshare') . '</th>' : '') .
            ($model->revshareOrCpa !== FormModel::SELECT_REVSHARE ? '<th data-group="3" colspan="4">' . Yii::_t('statistic.statistic_cpa') . '</th>' : '') .
//            ($tbSellIsEnabled ? '<th data-group="4" colspan="1"></th> ' : '') .
            '<th data-group="5" colspan="2"></td> ',
          'showFooter' => true,
          'emptyCell' => 0,
          'bordered' => false,
          'columns' => $gridColumns,
          'formatter' => Yii::$app->formatter,
        ]) ?>

      </div>
    <?php endif; ?>
    <?php Pjax::end(); ?>
  </div>
</div>
