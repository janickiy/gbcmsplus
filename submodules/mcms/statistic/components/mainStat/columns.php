<?php

use mcms\statistic\components\mainStat\mysql\ComplainLink;
use mcms\statistic\components\mainStat\mysql\Row;
use mcms\statistic\components\mainStat\Grid;
use mcms\statistic\models\ColumnsTemplate;
use mcms\statistic\models\Complain;

/**
 * Конфиг колонок для статы
 * TRICKY обратите внимание, что начальных колонок группировок тут нет
 */
// TODO все получать через геттеры
return [
  Grid::HEAD_GROUP_TRAFFIC => [
    [
      'attribute' => 'hits',
      'format' => 'integer',
      'template' => [ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
    ],
    [
      'attribute' => 'uniques',
      'format' => 'integer',
    ],
    [
      'attribute' => 'accepted',
      'format' => 'integer',
      'hint' => '{{hits}} - {{tb}}',
      'template' => [ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
    ],
    [
      'attribute' => 'tb',
      'format' => 'integer',
    ],
  ],
  Grid::HEAD_GROUP_EFFICIENCY => [
    [
      'attribute' => 'ecpc',
      'format' => ['decimal', 5],
      'hint' => '{{partnerTotalProfit}} / {{hits}}',
      'template' => [ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
    ],
  ],
  Grid::HEAD_GROUP_REVSHARE => [
    [
      'attribute' => 'revshareAccepted',
      'format' => 'integer',
      'template' => [ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
    ],
    [
      'attribute' => 'ons',
      'format' => 'integer',
      'template' => [ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
    ],
    [
      'attribute' => 'offs',
      'format' => 'integer',
      'template' => [ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
    ],
    [
      'attribute' => 'scopeOffsData',
      'format' => 'raw',
    ],
    [
      'attribute' => 'revshareRatio',
      'format' => 'raw',
      'hint' => '{{revshareAccepted}} / {{ons}}',
    ],
    [
      'attribute' => 'revshareCr',
      'format' => 'statisticSum',
      'hint' => '{{ons}} / {{revshareAccepted}} * 100',
      'template' => [ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
    ],
    [
      'attribute' => 'rebillsDateByDate',
      'format' => 'integer',
      'visible' => 'canViewAdditionalStatistic',
      'template' => [ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
    ],
    [
      'attribute' => 'chargeRatio',
      'format' => ['percent', 2],
      'hint' => '{{rebillsDateByDate}} / {{ons}} * 100',
      'visible' => 'canViewAdditionalStatistic',
      'template' => [ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
    ],
    [
      'attribute' => 'profitDateByDate',
      'format' => 'statisticSum',
      'visible' => 'canViewAdditionalStatistic',
    ],
    [
      'attribute' => 'rebills',
      'format' => 'integer',
      'template' => [ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
    ],
    [
      'attribute' => 'revshareResellerProfit',
      'format' => 'statisticSum',
      'visible' => 'canViewResellerProfit',
    ],
    [
      'attribute' => 'revshareResellerNetProfit',
      'format' => 'statisticSum',
      'hint' => '{{revshareResellerProfit}} - {{partnerRevshareProfit}}',
      'visible' => 'canViewResellerProfit',
    ],
    [
      'attribute' => 'partnerRevshareProfit',
      'format' => 'statisticSum',
      'visible' => 'canViewPartnerProfit',
    ],
    [
      'attribute' => 'ecpcRevshare',
      'format' => ['decimal', 5],
      'hint' => '{{partnerRevshareProfit}} / {{revshareAccepted}}',
      'template' => [ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
    ],
  ],
  Grid::HEAD_GROUP_CPA => [
    [
      'attribute' => 'cpaAccepted',
      'format' => 'integer',
      'template' => [ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
    ],
    [
      'attribute' => 'cpaOns',
      'format' => 'integer',
      'template' => [ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
    ],
    [
      'attribute' => 'sold',
      'format' => 'integer',
    ],
    [
      'attribute' => 'rejectedOns',
      'format' => 'integer',
    ],
    [
      'attribute' => 'soldVisible',
      'format' => 'integer',
    ],
    [
      'attribute' => 'cpaOffs',
      'format' => 'integer',
      'template' => [ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
    ],
    [
      'attribute' => 'cpaScopeOffsData',
      'format' => 'raw',
    ],
    [
      'attribute' => 'cpaRatio',
      'format' => 'raw',
      'hint' => '{{cpaAccepted}} / {{cpaOns}}',
    ],
    [
      'attribute' => 'cpaCr',
      'format' => 'statisticSum',
      'hint' => '{{cpaOns}} / {{cpaAccepted}} * 100',
      'template' => [ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
    ],
    [
      'attribute' => 'cpaCrSold',
      'format' => 'statisticSum',
      'hint' => '{{sold}} / {{cpaAccepted}} * 100',
      'template' => [ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
    ],
    [
      'attribute' => 'cpaCrVisible',
      'format' => 'statisticSum',
      'hint' => '{{soldVisible}} / {{cpaAccepted}} * 100',
    ],
    [
      'attribute' => 'cpaRebillsDateByDate',
      'format' => 'integer',
      'visible' => 'canViewAdditionalStatistic',
      'template' => [ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
    ],
    [
      'attribute' => 'cpaChargeRatio',
      'format' => ['percent', 2],
      'hint' => '{{cpaRebillsDateByDate}} / {{cpaOns}} * 100',
      'visible' => 'canViewAdditionalStatistic',
      'template' => [ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
    ],
    [
      'attribute' => 'cpaProfitDateByDate',
      'format' => 'statisticSum',
      'visible' => 'canViewAdditionalStatistic',
    ],
    [
      'attribute' => 'cpaRebills',
      'hint' => '{{soldRebills}} + {{rejectedRebills}}',
      'format' => 'integer',
      'template' => [ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
    ],
    [
      'attribute' => 'soldRebills',
      'format' => 'integer',
      'template' => [ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
    ],
    [
      'attribute' => 'rejectedRebills',
      'format' => 'integer',
      'template' => [ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
    ],
    [
      'attribute' => 'cpaProfit',
      'hint' => '{{soldRebillsProfit}} + {{rejectedProfit}}',
      'format' => 'statisticSum',
      'visible' => 'canViewResellerProfit',
    ],
    [
      'attribute' => 'soldRebillsProfit',
      'format' => 'statisticSum',
      'visible' => 'canViewResellerProfit',
    ],
    [
      'attribute' => 'rejectedProfit',
      'format' => 'statisticSum',
      'visible' => 'canViewResellerProfit',
    ],
    [
      'attribute' => 'cpaResellerNetProfit',
      'format' => 'statisticSum',
      'hint' => '{{soldRebillsProfit}} - {{soldPartnerProfit}}',
      'visible' => 'canViewResellerProfit',
    ],
    [
      'attribute' => 'soldPartnerProfit',
      'format' => 'statisticSum',
      'visible' => 'canViewPartnerProfit'
    ],
    [
      'attribute' => 'cpaEcp',
      'hint' => Yii::_t('statistic.main_statistic_refactored.soldPartnerPrice') . ' / {{hits}}',
      'format' => ['decimal', 5],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
    ],
    [
      'attribute' => 'cpaEcpc',
      'hint' => '{{soldPartnerProfit}} / {{cpaAccepted}}',
      'format' => ['decimal', 5],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
    ],
    [
      'attribute' => 'cpaCpr',
      'hint' => Yii::_t('statistic.main_statistic_refactored.soldPartnerPrice') . ' / {{sold}}',
      'format' => ['decimal', 3],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
    ],
    [
      'attribute' => 'avgCpa',
      'hint' => '{{soldPartnerProfit}} / {{sold}}',
      'format' => ['decimal', 3],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
    ],
    [
      'attribute' => 'revSub',
      'hint' => Yii::_t('statistic.main_statistic_refactored.cpaProfitDateByDate') . ' / {{sold}}',
      'format' => ['decimal', 4],
      'visible' => 'canViewAdditionalStatistic',
      'template' => [ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
    ],
    [
      'attribute' => 'roiOnDate',
      'hint' => '((' . Yii::_t('statistic.main_statistic_refactored.cpaProfitDateByDate') . ' / ' .
        Yii::_t('statistic.main_statistic_refactored.soldPartnerPrice') . ') - 1) * 100',
      'format' => ['decimal', 4],
      'visible' => 'canViewAdditionalStatistic',
      'template' => [ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
    ],


  ],
  Grid::HEAD_GROUP_ONETIME => [
    [
      'attribute' => 'acceptedOnetime',
      'format' => 'integer',
    ],
    [
      'attribute' => 'onetime',
      'format' => 'integer',
    ],
    [
      'attribute' => 'visibleOnetime',
      'format' => 'integer',
    ],
    [
      'attribute' => 'onetimeRatio',
      'format' => 'raw',
      'hint' => '{{acceptedOnetime}} / {{onetime}}',
    ],
    [
      'attribute' => 'onetimeCr',
      'format' => 'statisticSum',
      'hint' => '{{onetime}} / {{acceptedOnetime}} * 100',
    ],
    [
      'attribute' => 'visibleOnetimeCr',
      'format' => 'statisticSum',
      'hint' => '{{visibleOnetime}} / {{acceptedOnetime}} * 100',
    ],
    [
      'attribute' => 'onetimeResellerProfit',
      'format' => 'statisticSum',
      'visible' => 'canViewResellerProfit',
    ],
    [
      'attribute' => 'onetimeResellerNetProfit',
      'format' => 'statisticSum',
      'hint' => '{{onetimeResellerProfit}} - {{onetimeProfit}}',
      'visible' => 'canViewResellerProfit',
    ],
    [
      'attribute' => 'onetimeProfit',
      'format' => 'statisticSum',
      'visible' => 'canViewPartnerProfit',
    ],
    [
      'attribute' => 'ecpcOnetime',
      'format' => ['decimal', 5],
      'hint' => '{{onetimeProfit}} / {{acceptedOnetime}}',
      'template' => [ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
    ],
  ],
  Grid::HEAD_GROUP_SELL_TB => [
    [
      'attribute' => 'sellTbAccepted',
      'visible' => 'canViewSellTb',
      'format' => 'integer',
    ],
    [
      'attribute' => 'soldTb',
      'visible' => 'canViewSellTb',
      'format' => 'integer',
    ],
    [
      'attribute' => 'soldTbProfit',
      'visible' => 'canViewSellTb',
      'format' => 'statisticSum',
    ],
  ],
  Grid::HEAD_GROUP_TOTAL => [
    [
      'attribute' => 'resellerTotalProfit',
      'format' => 'statisticSum',
      'hint' => '{{revshareResellerProfit}} + {{cpaProfit}} + {{onetimeResellerProfit}} + {{soldTbProfit}}',
      'visible' => 'canViewResellerProfit',
    ],
    [
      'attribute' => 'resellerNetProfit',
      'format' => 'statisticSum',
      'hint' => '{{resellerTotalProfit}} - {{partnerTotalProfit}}',
      'visible' => 'canViewResellerProfit',
    ],
    [
      'attribute' => 'partnerTotalProfit',
      'hint' => '{{partnerRevshareProfit}} + {{soldPartnerProfit}} + {{onetimeProfit}}',
      'format' => 'statisticSum',
      'visible' => 'canViewPartnerProfit',
      'template' => [ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
    ],
  ],

  Grid::HEAD_GROUP_COMPLAINS => [
    [
      'attribute' => 'complains',
      'value' => function (Row $row) {
        return ComplainLink::create($row, Complain::TYPE_TEXT)->toString();
      },
      'format' => 'raw',
      'visible' => 'canViewComplainsStatistic',
      'template' => [ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
    ],
    [
      'attribute' => 'calls',
      'value' => function (Row $row) {
        return ComplainLink::create($row, Complain::TYPE_CALL)->toString();
      },
      'format' => 'raw',
      'visible' => 'canViewComplainsStatistic',
      'template' => [ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
    ],
    [
      'attribute' => 'callsMno',
      'value' => function (Row $row) {
        return ComplainLink::create($row, Complain::TYPE_CALL_MNO)->toString();
      },
      'format' => 'raw',
      'visible' => 'canViewComplainsStatistic',
      'template' => [ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
    ],
    [
      'attribute' => 'complainsRate',
      'hint' => '({{complains}} + {{calls}} + {{callsMno}}) / ({{ons}} + {{sold}} + {{onetime}})',
      'format' => 'percent',
      'visible' => 'canViewComplainsStatistic',
      'template' => [ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
    ],
  ],
];
