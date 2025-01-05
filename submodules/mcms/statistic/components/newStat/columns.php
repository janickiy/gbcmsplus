<?php

use mcms\statistic\components\newStat\FormModel;
use mcms\statistic\components\newStat\Grid;
use mcms\statistic\components\newStat\Group;
use mcms\statistic\components\newStat\mysql\Row;
use mcms\statistic\models\ColumnsTemplateNew as ColumnsTemplate;
use yii\helpers\Html;

/**
 * Конфиг колонок для статы
 * TRICKY обратите внимание, что начальных колонок группировок тут нет
 */

return [
  Grid::HEAD_GROUP_TRAFFIC_TOTAL => [
    [
      'attribute' => 'hits',
      'addAttribute' => 'unique',
      'popover' => 'hitsPopover',
      'format' => 'integer',
      'addFormat' => 'integer',
      'template' => [ColumnsTemplate::SYS_TEMPLATE_TOTAL, ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
      'hint' => 'When&nbsp;someone&nbsp;clicks&nbsp;your&nbsp;ad,<br />it’s&nbsp;counted&nbsp;here.<br />(Unique:&nbsp;number&nbsp;of&nbsp;unique&nbsp;visitors)'
    ],
    [
      'attribute' => 'accepted',
      'addAttribute' => 'acceptedRate',
      'format' => 'integer',
      'addFormat' => ['percent', 1],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_TOTAL, ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
      'hint' => 'Amount&nbsp;of&nbsp;clicks&nbsp;accepted&nbsp;by&nbsp;the&nbsp;system'
    ],
  ],


  Grid::HEAD_GROUP_CUSTOMER_BASE_TOTAL => [
    [
      'attribute' => 'totalSubscriptions',
      'format' => 'integer',
      'template' => [ColumnsTemplate::SYS_TEMPLATE_TOTAL, ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
      'hint' => 'It&nbsp;shows&nbsp;the&nbsp;number&nbsp;of&nbsp;subscriptions you&nbsp;received&nbsp;after&nbsp;ad&nbsp;
interactions.',
    ],
    [
      'attribute' => 'totalSubscriptionsRate',
      'format' => ['percent', 1],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_TOTAL, ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
      'hint' => 'Conversion&nbsp;rate&nbsp;(CR)&nbsp;shows&nbsp;how&nbsp;often&nbsp;on&nbsp;average an&nbsp;ad&nbsp;
interaction&nbsp;leads&nbsp;to&nbsp;a&nbsp;subscription. It’s&nbsp;“Subscriptions”&nbsp;divided&nbsp;by&nbsp;accepted&nbsp;clicks',
    ],
    [
      'attribute' => 'totalOffs',
      'addAttribute' => 'totalOffsRate',
      'format' => 'integer',
      'addFormat' => ['percent', 1],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_TOTAL, ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
      'hint' => 'It&nbsp;shows&nbsp;the&nbsp;total&nbsp;number&nbsp;of&nbsp;users&nbsp;that&nbsp;have&nbsp;already unsubscribed.<br>
(Churn&nbsp;Rate&nbsp;%:&nbsp;ratio&nbsp;between&nbsp;Unsubscriptions and&nbsp;Subscriptions)',
    ],
    [
      'attribute' => 'totalOnsWithoutOffs',
      'addAttribute' => 'totalAlive30daysOnsRate',
      'format' => 'integer',
      'addFormat' => ['percent', 1],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_TOTAL, ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
      'hint' => 'The&nbsp;total&nbsp;number&nbsp;of&nbsp;subscriptions&nbsp;that&nbsp;have&nbsp;not&nbsp;unsubscribed.<br>
(The&nbsp;%&nbsp;of&nbsp;Customer&nbsp;Base&nbsp;that&nbsp;has&nbsp;been&nbsp;charged&nbsp;at&nbsp;least&nbsp;once during&nbsp;the&nbsp;last&nbsp;30&nbsp;days)',
    ],
    [
      'attribute' => 'totalCharges',
      'addAttribute' => 'totalChargesRate',
      'format' => 'integer',
      'addFormat' => ['percent', 1],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_TOTAL, ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
      'hint' => 'The&nbsp;total&nbsp;number&nbsp;of&nbsp;full&nbsp;or&nbsp;partial&nbsp;charges&nbsp;(rebills) and&nbsp;One&nbsp;Time&nbsp;Payments.<br>
(Billability&nbsp;shows&nbsp;the&nbsp;share&nbsp;of&nbsp;CB&nbsp;that&nbsp;was successfully&nbsp;charged)',
      'hideTotalAdd' => true,
    ],
//    [
//      'attribute' => 'totalChargesNotified',
//      'addAttribute' => 'totalChargesNotifiedRate',
//      'format' => 'integer',
//      'addFormat' => ['percent', 1],
//      'template' => [ColumnsTemplate::SYS_TEMPLATE_TOTAL],
//      'hint' => '%&nbsp;of&nbsp;CB Charges:&nbsp;(Charges&nbsp;Notified/Charges)*100',
//    ],
  ],

  Grid::HEAD_GROUP_COHORTS_TOTAL => [
    [
      'attribute' => 'totalArpu',
      'addAttribute' => 'totalLtvRebillsAvg',
      'format' => ['currencyCustomDecimal', FormModel::DEFAULT_CURRENCY],
      'addFormat' => ['decimal', 2],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_TOTAL, ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
      'hint' => 'It\'s&nbsp;the&nbsp;cumulated&nbsp;value&nbsp;of&nbsp;each&nbsp;subscription&nbsp;over&nbsp;time.<br>
(Average&nbsp;number&nbsp;of&nbsp;charges&nbsp;performed&nbsp;per&nbsp;subscription).<br>
This&nbsp;value&nbsp;evolves&nbsp;on&nbsp;daily&nbsp;basis.&nbsp;Values&nbsp;are&nbsp;presented&nbsp;based&nbsp;on&nbsp;data&nbsp;we&nbsp;have&nbsp;today.
If&nbsp;you’d&nbsp;like&nbsp;to&nbsp;review&nbsp;the&nbsp;evolution&nbsp;of&nbsp;values&nbsp;please&nbsp;change “Cohorts&nbsp;date”&nbsp;in&nbsp;filters.'
    ],
    [
      'attribute' => 'aliveOns',
      'addAttribute' => 'aliveOnsRate',
      'format' => 'integer',
      'addFormat' => ['percent', 1],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_TOTAL, ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
      'hint' => 'It&nbsp;shows&nbsp;the&nbsp;subset&nbsp;of&nbsp;alive&nbsp;(which&nbsp;didn’t&nbsp;unsubscribe) subscriptions.<br>
(Retention&nbsp;rate&nbsp;is&nbsp;the&nbsp;%&nbsp;of&nbsp;Live&nbsp;subscriptions&nbsp;vs.&nbsp;value in&nbsp;Subscriptions&nbsp;column).<br>
This&nbsp;value&nbsp;evolves&nbsp;on&nbsp;daily&nbsp;basis.Values&nbsp;are&nbsp;presented&nbsp;based&nbsp;on&nbsp;data&nbsp;we&nbsp;have&nbsp;today.
If&nbsp;you’d&nbsp;like&nbsp;to&nbsp;review&nbsp;the&nbsp;evolution&nbsp;of&nbsp;values&nbsp;please&nbsp;change “Cohorts&nbsp;date”&nbsp;in&nbsp;filters.',
    ],
  ],

  Grid::HEAD_GROUP_AFFILIATE_TOTAL => [
    [
      'attribute' => 'totalBuyoutPartnerProfit',
      'addAttribute' => 'totalBuyoutVisibleOns',
      'format' => ['currencyCustomDecimal', FormModel::DEFAULT_CURRENCY],
      'addFormat' => 'integer',
      'template' => [ColumnsTemplate::SYS_TEMPLATE_TOTAL, ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
      'hint' => 'It&nbsp;is&nbsp;the&nbsp;amount&nbsp;you’re&nbsp;paying&nbsp;for&nbsp;customer&nbsp;acquisition on&nbsp;CPA&nbsp;basis.<br>
(This&nbsp;is&nbsp;the&nbsp;number&nbsp;of&nbsp;Subscriptions&nbsp;you&nbsp;are&nbsp;paying&nbsp;for)'
    ],
    [
      'attribute' => 'totalRevsharePartnerProfit',
      'addAttribute' => 'totalRevshareRebillsNotified',
      'format' => ['currencyCustomDecimal', FormModel::DEFAULT_CURRENCY],
      'addFormat' => 'integer',
      'template' => [ColumnsTemplate::SYS_TEMPLATE_TOTAL, ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
      'hint' => 'It&nbsp;is&nbsp;the&nbsp;amount&nbsp;you’re&nbsp;paying&nbsp;for&nbsp;customer&nbsp;acquisition on&nbsp;Revenue&nbsp;Share&nbsp;basis.<br>
(This&nbsp;is&nbsp;the&nbsp;number&nbsp;of&nbsp;Charges&nbsp;you&nbsp;are&nbsp;paying&nbsp;for)'
    ],
    [
      'attribute' => 'totalOtpPartnerProfit',
      'addAttribute' => 'totalOtpVisibleOns',
      'format' => ['currencyCustomDecimal', FormModel::DEFAULT_CURRENCY],
      'addFormat' => 'integer',
      'template' => [ColumnsTemplate::SYS_TEMPLATE_TOTAL, ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
      'hint' => 'It&nbsp;is&nbsp;the&nbsp;amount&nbsp;you’re&nbsp;paying&nbsp;for&nbsp;customer&nbsp;acquisition on&nbsp;One&nbsp;Time&nbsp;Payment.<br>
(This&nbsp;is&nbsp;the&nbsp;number&nbsp;of&nbsp;Payments&nbsp;you&nbsp;are&nbsp;paying&nbsp;for)'
    ],
    [
      'attribute' => 'totalPartnerProfit',
      'addAttribute' => 'totalPartnerProfitRate',
      'format' => ['currencyCustomDecimal', FormModel::DEFAULT_CURRENCY],
      'addFormat' => ['percent', 1],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_TOTAL, ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
      'hint' => 'It&nbsp;shows&nbsp;the&nbsp;total&nbsp;of&nbsp;what&nbsp;you’re&nbsp;paying&nbsp;for&nbsp;all the&nbsp;customer&nbsp;acquisition.<br>
(This&nbsp;%&nbsp;show&nbsp;the&nbsp;ratio&nbsp;between&nbsp;Total&nbsp;Payout and&nbsp;Gross&nbsp;Revenue)',
    ],
  ],

  Grid::HEAD_GROUP_REVENUES_TOTAL => [
    [
      'attribute' => 'totalResellerProfit',
      'format' => ['currencyCustomDecimal', FormModel::DEFAULT_CURRENCY],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_TOTAL, ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
      'hint' => 'This&nbsp;is&nbsp;the&nbsp;relevant&nbsp;accrual&nbsp;of&nbsp;revenue',
      'visible' => 'canViewResellerProfit',
    ],
    [
      'attribute' => 'buyoutResellerNetProfit',
      'addAttribute' => 'buyoutResellerNetProfitTotalRate',
      'format' => ['currencyCustomDecimal', FormModel::DEFAULT_CURRENCY],
      'addFormat' => ['percent', 1],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_TOTAL, ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
      'hint' => 'It&nbsp;is&nbsp;the&nbsp;Margin&nbsp;you&nbsp;generated&nbsp;on&nbsp;customer&nbsp;acquisition on&nbsp;CPA&nbsp;basis.<br>
(%&nbsp;of&nbsp;Margin&nbsp;respect&nbsp;to&nbsp;Gross)',
      'visible' => 'canViewResellerProfit',
    ],
    [
      'attribute' => 'totalRevshareResellerNetProfit',
      'addAttribute' => 'revshareResellerNetProfitTotalRate',
      'format' => ['currencyCustomDecimal', FormModel::DEFAULT_CURRENCY],
      'addFormat' => ['percent', 1],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_TOTAL, ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
      'hint' => 'It&nbsp;is&nbsp;the&nbsp;Margin&nbsp;you&nbsp;generated&nbsp;on&nbsp;customer&nbsp;acquisition on&nbsp;Revenue&nbsp;Share&nbsp;basis.<br>
(%&nbsp;of&nbsp;Margin&nbsp;respect&nbsp;to&nbsp;Gross)',
      'visible' => 'canViewResellerProfit',
    ],
    [
      'attribute' => 'totalOtpResellerNetProfit',
      'addAttribute' => 'otpResellerNetProfitTotalRate',
      'format' => ['currencyCustomDecimal', FormModel::DEFAULT_CURRENCY],
      'addFormat' => ['percent', 1],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_TOTAL, ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
      'hint' => 'It&nbsp;is&nbsp;the&nbsp;Margin&nbsp;you&nbsp;generated&nbsp;on&nbsp;customer&nbsp;acquisition on&nbsp;One&nbsp;Time&nbsp;Payment&nbsp;basis.<br>
(%&nbsp;of&nbsp;Margin&nbsp;respect&nbsp;to&nbsp;Gross)',
      'visible' => 'canViewResellerProfit',
    ],
    [
      'attribute' => 'totalResellerNetProfit',
      'addAttribute' => 'totalResellerNetProfitTotalRate',
      'format' => ['currencyCustomDecimal', FormModel::DEFAULT_CURRENCY],
      'addFormat' => ['percent', 1],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_TOTAL, ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
      'hint' => 'It’s&nbsp;the&nbsp;difference&nbsp;between&nbsp;Gross&nbsp;revenue&nbsp;and&nbsp;Total&nbsp;Payout. This&nbsp;is&nbsp;basically&nbsp;what&nbsp;you&nbsp;can&nbsp;put&nbsp;in&nbsp;your&nbsp;pocket.<br>
(%&nbsp;of&nbsp;Margin&nbsp;respect&nbsp;to&nbsp;Gross)',
      'visible' => 'canViewResellerProfit',
    ],
  ],

  Grid::HEAD_GROUP_CUSTOMER_CARE_TOTAL => [
    [
      'attribute' => 'rgkComplaints',
      'addAttribute' => 'rgkComplaintsRate',
      'format' => 'integer',
      'addFormat' => ['percent', 1],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_TOTAL, ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
      'hint' => 'Calls&nbsp;processed&nbsp;by&nbsp;Wap.Click’s&nbsp;Customer&nbsp;Care.<br>
(%&nbsp;of&nbsp;calls&nbsp;counted&nbsp;on&nbsp;the&nbsp;Customer&nbsp;Base)',
    ],
    [
      'attribute' => 'callMnoComplaints',
      'addAttribute' => 'callMnoComplaintsRate',
      'format' => 'integer',
      'addFormat' => ['percent', 1],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_TOTAL, ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
      'hint' => 'Calls&nbsp;processed&nbsp;by&nbsp;Carrier,&nbsp;Provider&nbsp;or&nbsp;Authority.<br>
(%&nbsp;of&nbsp;calls&nbsp;counted&nbsp;on&nbsp;the&nbsp;Customer&nbsp;Base)',
    ],
    [
      'attribute' => 'refundSum',
      'addAttribute' => 'refunds',
      'addFormat' => 'integer',
      'format' => ['currencyCustomDecimal', FormModel::DEFAULT_CURRENCY],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_TOTAL, ColumnsTemplate::SYS_TEMPLATE_DEFAULT],
      'hint' => 'Funds&nbsp;Operator&nbsp;had&nbsp;to&nbsp;compensate&nbsp;subscribers.<br>
(Number&nbsp;of&nbsp;subscribers&nbsp;affected)'
    ],
  ],

  Grid::HEAD_GROUP_TRAFFIC_REV => [
    [
      'attribute' => 'revshareHits',
      'addAttribute' => 'revshareUnique',
      'format' => 'integer',
      'addFormat' => 'integer',
      'template' => [ColumnsTemplate::SYS_TEMPLATE_REVSHARE],
      'hint' => 'When&nbsp;someone&nbsp;clicks&nbsp;your&nbsp;ad, it\'s&nbsp;counted&nbsp;here.<br>&nbsp;
(Unique:&nbsp;number&nbsp;of&nbsp;unique&nbsp;visitors)',
    ],
    [
      'attribute' => 'revshareAccepted',
      'addAttribute' => 'revshareAcceptedRate',
      'format' => 'integer',
      'addFormat' => ['percent', 1],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_REVSHARE],
      'hint' => 'Amount&nbsp;of&nbsp;clicks&nbsp;accepted&nbsp;by&nbsp;the&nbsp;system',
    ],
  ],
  Grid::HEAD_GROUP_PERFOMANCE_REV => [
    [
      'attribute' => 'revshareOns',
      'format' => 'integer',
      'template' => [ColumnsTemplate::SYS_TEMPLATE_REVSHARE],
      'hint' => 'It&nbsp;shows&nbsp;the&nbsp;number&nbsp;of&nbsp;subscriptions&nbsp;you&nbsp;received
       after&nbsp;ad&nbsp;interactions',
    ],
    [
      'attribute' => 'revshareCr',
      'format' => ['percent', 1],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_REVSHARE],
      'hint' => 'Conversion&nbsp;rate&nbsp;(CR) shows&nbsp;how&nbsp;often,&nbsp;on&nbsp;average, 
      an&nbsp;ad&nbsp;interaction&nbsp;leads&nbsp;to&nbsp;a&nbsp;subscription. 
      It’s&nbsp;“Subscriptions”&nbsp;divided&nbsp;by&nbsp;accepted&nbsp;clicks',
    ],
    [
      'attribute' => 'revshareRebills',
      'addAttribute' => 'revshareRebillsNotified',
      'format' => 'integer',
      'addFormat' => 'integer',
      'template' => [ColumnsTemplate::SYS_TEMPLATE_REVSHARE],
      'hint' => 'The&nbsp;total&nbsp;number&nbsp;of&nbsp;full&nbsp;or&nbsp;partial&nbsp;charges&nbsp;(rebills).<br>;
(Notified:&nbsp;number&nbsp;of&nbsp;Charges&nbsp;visible&nbsp;to&nbsp;affiliates)'
    ],
    [
      'attribute' => 'revshareRebills24',
      'addAttribute' => 'revshareRebills24Rate',
      'format' => 'integer',
      'addFormat' => ['percent', 1],
      'hint' => 'It&nbsp;shows&nbsp;the&nbsp;number&nbsp;of&nbsp;subscriptions&nbsp;that&nbsp;were&nbsp;billed 
successfully&nbsp;at&nbsp;the&nbsp;first&nbsp;attempt.<br>
(%&nbsp;of&nbsp;Subs:&nbsp;ratio&nbsp;between&nbsp;successful&nbsp;Initial&nbsp;Charges&nbsp;and&nbsp;Subscriptions)',
      'template' => [ColumnsTemplate::SYS_TEMPLATE_REVSHARE],
    ],
    [
      'attribute' => 'revshareOffs24',
      'addAttribute' => 'revshareOffs24Rate',
      'format' => 'integer',
      'addFormat' => ['percent', 1],
      'hint' => 'It&nbsp;shows&nbsp;the&nbsp;number&nbsp;of&nbsp;users&nbsp;that&nbsp;unsubscribed 
within&nbsp;24h.&nbsp;from&nbsp;Subscription&nbsp;time.<br>
(%&nbsp;of&nbsp;Subs:&nbsp;ratio&nbsp;between&nbsp;Instant&nbsp;Unsubscriptions&nbsp;and&nbsp;Subscriptions)',
      'template' => [ColumnsTemplate::SYS_TEMPLATE_REVSHARE],
    ],
  ],
  Grid::HEAD_GROUP_CUSTOMER_BASE_REV => [
    [
      'attribute' => 'revshareOffs',
      'addAttribute' => 'revshareOffsRate',
      'format' => 'integer',
      'addFormat' => ['percent', 1],
      'hint' => 'It&nbsp;shows&nbsp;the&nbsp;total&nbsp;number&nbsp;of&nbsp;users&nbsp;that&nbsp;have 
already&nbsp;unsubscribed.<br>
(%&nbsp;of&nbsp;CB:&nbsp;ratio&nbsp;between&nbsp;Unsubscriptions&nbsp;and&nbsp;Subscriptions)',
      'template' => [ColumnsTemplate::SYS_TEMPLATE_REVSHARE],
    ],
    [
      'attribute' => 'revshareTotalOnsWithoutOffs',
      'addAttribute' => 'revshareAlive30daysOnsRate',
      'format' => 'integer',
      'addFormat' => ['percent', 1],
      'hint' => 'The&nbsp;total&nbsp;number&nbsp;of&nbsp;subscriptions&nbsp;that&nbsp;have&nbsp;not&nbsp;unsubscribed.<br>
(The&nbsp;%&nbsp;of&nbsp;Customer&nbsp;Base&nbsp;that&nbsp;has&nbsp;been&nbsp;charged&nbsp;at&nbsp;least&nbsp;once 
during&nbsp;the&nbsp;last&nbsp;30&nbsp;days)',
      'template' => [ColumnsTemplate::SYS_TEMPLATE_REVSHARE],
    ],
//    [
//      'attribute' => 'revshareRebills',
//      'addAttribute' => 'revshareRebillsRate',
//      'format' => 'integer',
//      'addFormat' => ['percent', 1],
//      'hint' => 'The&nbsp;total&nbsp;number&nbsp;of&nbsp;full&nbsp;or&nbsp;partial&nbsp;charges&nbsp;(rebills).<br>&nbsp;
//(Billability&nbsp;shows&nbsp;the&nbsp;share&nbsp;of&nbsp;CB&nbsp;that&nbsp;was&nbsp;successfully&nbsp;charged)',
//      'hideTotalAdd' => true,
//      'template' => [ColumnsTemplate::SYS_TEMPLATE_REVSHARE],
//    ],

  ],
  Grid::HEAD_GROUP_COHORTS_REV => [
    [
      'attribute' => 'revshareArpu',
      'addAttribute' => 'revshareLtvRebillsAvg',
      'format' => ['currencyCustomDecimal', FormModel::DEFAULT_CURRENCY],
      'addFormat' => ['decimal', 2],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_REVSHARE],
      'hint' => 'It\'s&nbsp;the&nbsp;cumulated&nbsp;value&nbsp;of&nbsp;each&nbsp;subscription&nbsp;over&nbsp;time.<br>
(Average&nbsp;number&nbsp;of&nbsp;charges&nbsp;performed&nbsp;per&nbsp;subscription).<br>
This&nbsp;value&nbsp;evolves&nbsp;on&nbsp;daily&nbsp;basis.&nbsp;Values&nbsp;are&nbsp;presented&nbsp;based 
on&nbsp;data&nbsp;we&nbsp;have&nbsp;today.&nbsp;If&nbsp;you’d&nbsp;like&nbsp;to&nbsp;review&nbsp;the&nbsp;evolution 
of&nbsp;values&nbsp;please&nbsp;change&nbsp;“Cohorts&nbsp;date”&nbsp;in&nbsp;filters.'
    ],
    [
      'attribute' => 'revshareAliveOns',
      'addAttribute' => 'revshareAliveOnsRate',
      'format' => 'integer',
      'addFormat' => ['percent', 1],
      'hint' => 'It&nbsp;shows&nbsp;the&nbsp;subset&nbsp;of&nbsp;alive&nbsp;(which&nbsp;didn’t&nbsp;unsubscribe) 
subscriptions.<br>
(Retention&nbsp;rate&nbsp;is&nbsp;the&nbsp;%&nbsp;of&nbsp;Live&nbsp;subscriptions&nbsp;vs.&nbsp;value 
in&nbsp;Subscriptions&nbsp;column).<br>
This&nbsp;value&nbsp;evolves&nbsp;on&nbsp;daily&nbsp;basis.Values&nbsp;are&nbsp;presented&nbsp;based 
on&nbsp;data&nbsp;we&nbsp;have&nbsp;today.&nbsp;If&nbsp;you’d&nbsp;like&nbsp;to&nbsp;review&nbsp;the&nbsp;evolution 
of&nbsp;values&nbsp;please&nbsp;change&nbsp;“Cohorts&nbsp;date”&nbsp;in&nbsp;filters.',
      'template' => [ColumnsTemplate::SYS_TEMPLATE_REVSHARE],
    ],
  ],
  Grid::HEAD_GROUP_REVENUES_REV => [
    [
      'attribute' => 'revshareResellerProfit',
      'format' => ['currencyCustomDecimal', FormModel::DEFAULT_CURRENCY],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_REVSHARE],
      'hint' => 'This&nbsp;is&nbsp;the&nbsp;relevant&nbsp;accrual&nbsp;of&nbsp;revenue.',
      'visible' => 'canViewResellerProfit',
    ],
    [
      'attribute' => 'revsharePartnerProfit',
      'addAttribute' => 'revsharePartnerProfitRate',
      'format' => ['currencyCustomDecimal', FormModel::DEFAULT_CURRENCY],
      'addFormat' => ['percent', 1],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_REVSHARE],
      'hint' => 'Is&nbsp;the&nbsp;amount&nbsp;you’re&nbsp;paying&nbsp;for&nbsp;customer&nbsp;acquisition.<br>&nbsp;&nbsp;
(The&nbsp;%&nbsp;of&nbsp;Gross&nbsp;that&nbsp;covers&nbsp;your&nbsp;Marketing&nbsp;Cost)',
      'visible' => 'canViewResellerProfit',
    ],
    [
      'attribute' => 'revshareResellerNetProfit',
      'addAttribute' => 'revshareResellerNetProfitRate',
      'format' => ['currencyCustomDecimal', FormModel::DEFAULT_CURRENCY],
      'addFormat' => ['percent', 1],
      'hint' => 'This&nbsp;is&nbsp;the&nbsp;sum&nbsp;of&nbsp;fees&nbsp;you&nbsp;earned&nbsp;by&nbsp;processing 
each&nbsp;successful&nbsp;Charge.<br>
(It&nbsp;shows&nbsp;the&nbsp;ratio&nbsp;between&nbsp;Commission&nbsp;and&nbsp;Gross)',
      'template' => [ColumnsTemplate::SYS_TEMPLATE_REVSHARE],
      'visible' => 'canViewResellerProfit',
    ],
    [
      'attribute' => 'revshareAdjustment',
      'addAttribute' => 'revshareAdjustmentRate',
      'addFormat' => ['percent', 1],
      'format' => ['currencyCustomDecimal', FormModel::DEFAULT_CURRENCY],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_REVSHARE],
      'hint' => 'This&nbsp;is&nbsp;the&nbsp;sum&nbsp;of&nbsp;Adjustments&nbsp;applied&nbsp;to&nbsp;Charges 
due&nbsp;to&nbsp;certain&nbsp;rules.<br>
(It&nbsp;shows&nbsp;the&nbsp;ratio&nbsp;between&nbsp;Adjustment&nbsp;and&nbsp;Gross)',
      'visible' => 'canViewResellerProfit',
    ],
    [
      'attribute' => 'revshareTotalMargin',
      'addAttribute' => 'revshareResellerNetProfitRate',
      'format' => ['currencyCustomDecimal', FormModel::DEFAULT_CURRENCY],
      'addFormat' => ['percent', 1],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_REVSHARE],
      'hint' => 'It’s&nbsp;the&nbsp;difference&nbsp;between&nbsp;Gross&nbsp;revenue&nbsp;and&nbsp;Marketing&nbsp;Cost. 
This&nbsp;is&nbsp;basically&nbsp;what&nbsp;you&nbsp;can&nbsp;put&nbsp;in&nbsp;your&nbsp;pocket.<br>
(%&nbsp;of&nbsp;Margin&nbsp;respect&nbsp;to&nbsp;Gross)',
      'visible' => 'canViewResellerProfit',
    ],
  ],
  Grid::HEAD_GROUP_COMPLAINTS_REV => [
    [
      'attribute' => 'revshareRgkComplaints',
      'addAttribute' => 'revshareRgkComplaintsRate',
      'format' => 'integer',
      'addFormat' => ['percent', 1],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_REVSHARE],
      'hint' => 'Calls&nbsp;processed&nbsp;by&nbsp;Wap.Click’s&nbsp;Customer&nbsp;Care.<br>
(%&nbsp;of&nbsp;calls&nbsp;counted&nbsp;on&nbsp;the&nbsp;Customer&nbsp;Base)',
    ],
    [
      'attribute' => 'revshareCallMnoComplaints',
      'addAttribute' => 'revshareCallMnoComplaintsRate',
      'format' => 'integer',
      'addFormat' => ['percent', 1],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_REVSHARE],
      'hint' => 'Calls&nbsp;processed&nbsp;by&nbsp;Carrier,&nbsp;Provider&nbsp;or&nbsp;Authority.<br>
(%&nbsp;of&nbsp;calls&nbsp;counted&nbsp;on&nbsp;the&nbsp;Customer&nbsp;Base)',
    ],
    [
      'attribute' => 'revshareRefundSum',
      'addAttribute' => 'revshareRefunds',
      'addFormat' => 'integer',
      'format' => ['currencyCustomDecimal', FormModel::DEFAULT_CURRENCY],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_REVSHARE],
      'hint' => 'Funds&nbsp;Operator&nbsp;had&nbsp;to&nbsp;compensate&nbsp;subscribers.<br>
(Number&nbsp;of&nbsp;subscribers&nbsp;affected)'
    ],
  ],

  Grid::HEAD_GROUP_TRAFFIC_CPA => [
    [
      'attribute' => 'toBuyoutHits',
      'addAttribute' => 'toBuyoutUnique',
      'format' => 'integer',
      'addFormat' => 'integer',
      'template' => [ColumnsTemplate::SYS_TEMPLATE_CPA],
      'hint' => 'When&nbsp;someone&nbsp;clicks&nbsp;your&nbsp;ad, it\'s&nbsp;counted&nbsp;here.<br/>(Unique:&nbsp;number&nbsp;of&nbsp;unique&nbsp;visitors)',
    ],
    [
      'attribute' => 'toBuyoutAccepted',
      'addAttribute' => 'toBuyoutAcceptedRate',
      'format' => 'integer',
      'addFormat' => ['percent', 1],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_CPA],
      'hint' => 'Amount&nbsp;of&nbsp;clicks&nbsp;accepted&nbsp;by&nbsp;the&nbsp;system.',
    ],
  ],

  Grid::HEAD_GROUP_PERFOMANCE_CPA => [
    [
      'attribute' => 'toBuyoutOns',
      'addAttribute' => 'buyoutVisibleOns',
      'format' => 'integer',
      'addFormat' => 'integer',
      'template' => [ColumnsTemplate::SYS_TEMPLATE_CPA],
      'hint' => 'It&nbsp;shows&nbsp;the&nbsp;number&nbsp;of&nbsp;subscriptions&nbsp;you&nbsp;received 
after&nbsp;ad&nbsp;interactions.<br>
(Notified:&nbsp;number&nbsp;of&nbsp;subscriptions&nbsp;visible&nbsp;to&nbsp;affiliates)',
    ],
    [
      'attribute' => 'toBuyoutCr',
      'addAttribute' => 'buyoutVisibleCr',
      'format' => ['percent', 1],
      'addFormat' => ['percent', 1],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_CPA],
      'hint' => 'Conversion&nbsp;rate&nbsp;(CR) shows&nbsp;how&nbsp;often&nbsp;on&nbsp;average 
an&nbsp;ad&nbsp;interaction&nbsp;leads&nbsp;to&nbsp;a&nbsp;subscription.&nbsp;It’s&nbsp;“Subscriptions” 
divided&nbsp;by&nbsp;accepted&nbsp;clicks.<br>
(Notified:&nbsp;CR&nbsp;value&nbsp;visible&nbsp;to&nbsp;affiliates)',
    ],
    [
      'attribute' => 'buyoutAvgPartnerProfit',
      'addAttribute' => 'visibleBuyoutAvgPartnerProfit',
      'format' => ['currencyCustomDecimal', FormModel::DEFAULT_CURRENCY],
      'addFormat' => ['currencyCustomDecimal', FormModel::DEFAULT_CURRENCY],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_CPA],
      'hint' => 'Cost&nbsp;per&nbsp;Acquisition&nbsp;shows&nbsp;the&nbsp;average&nbsp;cost&nbsp;of&nbsp;a&nbsp;subscription. '
        . 'It’s&nbsp;the&nbsp;Marketing&nbsp;Cost&nbsp;divided&nbsp;by&nbsp;Subscriptions.<br/>'
        . '(Notified:&nbsp;CPA&nbsp;value&nbsp;visible&nbsp;to&nbsp;affiliates).',
    ],
    [
      'attribute' => 'buyoutRpm',
      'addAttribute' => 'buyoutNotifyRpm',
      'format' => ['currencyCustomDecimal', FormModel::DEFAULT_CURRENCY],
      'addFormat' => ['currencyCustomDecimal', FormModel::DEFAULT_CURRENCY],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_CPA],
      'hint' => 'The&nbsp;average&nbsp;amount&nbsp;earned&nbsp;per&nbsp;1,000&nbsp;Accepted&nbsp;clicks.<br/>'
        . '(Notified:&nbsp;the&nbsp;average&nbsp;amount&nbsp;earned&nbsp;by&nbsp;affiliate&nbsp;per&nbsp;1,000 Accepted&nbsp;clicks.&nbsp;'
        . 'It’s&nbsp;the&nbsp;marketing&nbsp;cost&nbsp;divided&nbsp;by&nbsp;Accepted Clicks&nbsp;per&nbsp;1,000)',
    ],
    [
      'attribute' => 'buyoutRebills24',
      'addAttribute' => 'buyoutRebills24Rate',
      'format' => 'integer',
      'addFormat' => ['percent', 1],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_CPA],
      'hint' => 'It&nbsp;shows&nbsp;the&nbsp;number&nbsp;of&nbsp;subscriptions&nbsp;that&nbsp;were&nbsp;billed 
successfully&nbsp;at&nbsp;the&nbsp;first&nbsp;attempt.<br>
(Initial&nbsp;Billability&nbsp;%:&nbsp;ratio&nbsp;between&nbsp;successful&nbsp;Initial&nbsp;Charges 
and&nbsp;Subscriptions)',
    ],
    [
      'attribute' => 'buyoutOffs24',
      'addAttribute' => 'buyoutOffs24Rate',
      'format' => 'integer',
      'addFormat' => ['percent', 1],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_CPA],
      'hint' => 'It&nbsp;shows&nbsp;the&nbsp;number&nbsp;of&nbsp;users&nbsp;that&nbsp;unsubscribed 
within&nbsp;24h.&nbsp;from&nbsp;Subscription&nbsp;time.<br>
(Instant&nbsp;Churn&nbsp;Rate&nbsp;%:&nbsp;ratio&nbsp;between&nbsp;Instant&nbsp;Churn and&nbsp;Subscriptions)',
    ],
  ],

  Grid::HEAD_GROUP_CUSTOMER_BASE_CPA => [
    [
      'attribute' => 'toBuyoutOffs',
      'addAttribute' => 'toBuyoutOffsRate',
      'format' => 'integer',
      'addFormat' => ['percent', 1],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_CPA],
      'hint' => 'It&nbsp;shows&nbsp;the&nbsp;total&nbsp;number&nbsp;of&nbsp;users&nbsp;that&nbsp;have 
already&nbsp;unsubscribed.<br>
(Churn&nbsp;Rate&nbsp;%:&nbsp;ratio&nbsp;between&nbsp;Unsubscriptions and&nbsp;Subscriptions)',
    ],
    [
      'attribute' => 'toBuyoutTotalOnsWithoutOffs',
      'addAttribute' => 'toBuyoutAlive30daysOnsRate',
      'format' => 'integer',
      'addFormat' => ['percent', 1],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_CPA],
      'hint' => 'The&nbsp;total&nbsp;number&nbsp;of&nbsp;subscriptions&nbsp;that&nbsp;have&nbsp;not unsubscribed.<br/>'
        . '(The&nbsp;%&nbsp;of&nbsp;Customer&nbsp;Base&nbsp;that&nbsp;has&nbsp;been&nbsp;charged at&nbsp;least&nbsp;once&nbsp;during&nbsp;the&nbsp;last&nbsp;30&nbsp;days)',
    ],
    [
      'attribute' => 'buyoutRebills',
      'addAttribute' => 'buyoutRebillsRate',
      'format' => 'integer',
      'addFormat' => ['percent', 1],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_CPA],
      'hideTotalAdd' => true,
      'hint' => 'The&nbsp;total&nbsp;number&nbsp;of&nbsp;full&nbsp;or&nbsp;partial&nbsp;charges&nbsp;(rebills).<br>
(Billability&nbsp;shows&nbsp;the&nbsp;share&nbsp;of&nbsp;CB&nbsp;that&nbsp;was successfully&nbsp;charged)',
    ],
  ],

  Grid::HEAD_GROUP_COHORDS_CPA => [
    [
      'attribute' => 'toBuyoutArpu',
      'addAttribute' => 'toBuyoutLtvRebillsAvg',
      'format' => ['currencyCustomDecimal', FormModel::DEFAULT_CURRENCY],
      'addFormat' => ['decimal', 2],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_CPA],
      'hint' => 'It\'s&nbsp;the&nbsp;cumulated&nbsp;value&nbsp;of&nbsp;each&nbsp;subscription&nbsp;over&nbsp;time.<br>
(Average&nbsp;number&nbsp;of&nbsp;charges&nbsp;performed&nbsp;per&nbsp;subscription).<br>
This&nbsp;value&nbsp;evolves&nbsp;on&nbsp;daily&nbsp;basis.Values&nbsp;are&nbsp;presented 
based&nbsp;on&nbsp;data&nbsp;we&nbsp;have&nbsp;today.&nbsp;If&nbsp;you’d&nbsp;like&nbsp;to&nbsp;review 
the&nbsp;evolution&nbsp;of&nbsp;values&nbsp;please&nbsp;change&nbsp;“Cohorts&nbsp;date”&nbsp;in&nbsp;filters.',
    ],
    [
      'attribute' => 'toBuyoutAliveOns',
      'addAttribute' => 'toBuyoutAliveOnsRate',
      'format' => 'integer',
      'addFormat' => ['percent', 1],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_CPA],
      'hint' => 'It&nbsp;shows&nbsp;the&nbsp;subset&nbsp;of&nbsp;alive&nbsp;(which&nbsp;didn’t&nbsp;unsubscribe) 
subscriptions.<br>
(Retention&nbsp;rate&nbsp;is&nbsp;the&nbsp;%&nbsp;of&nbsp;Live&nbsp;subscriptions&nbsp;vs.&nbsp;value 
in&nbsp;Subscriptions&nbsp;column).<br>
This&nbsp;value&nbsp;evolves&nbsp;on&nbsp;daily&nbsp;basis.Values&nbsp;are&nbsp;presented&nbsp;based 
on&nbsp;data&nbsp;we&nbsp;have&nbsp;today.&nbsp;If&nbsp;you’d&nbsp;like&nbsp;to&nbsp;review 
the&nbsp;evolution&nbsp;of&nbsp;values&nbsp;please&nbsp;change&nbsp;“Cohorts&nbsp;date”&nbsp;in&nbsp;filters.',
    ],
    [
      'attribute' => 'buyoutRoi',
      'addAttribute' => 'toBuyoutResellerLtvProfit',
      'format' => ['percent', 1],
      'addFormat' => ['currencyCustomDecimal', FormModel::DEFAULT_CURRENCY],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_CPA],
      'hint' => 'ROI&nbsp;is&nbsp;the&nbsp;most&nbsp;important&nbsp;KPI&nbsp;because&nbsp;it 
      shows&nbsp;how&nbsp;profitable&nbsp;subscriptions&nbsp;are&nbsp;against&nbsp;the 
      Marketing&nbsp;Cost.&nbsp;It&nbsp;is&nbsp;calculated&nbsp;as&nbsp;(relevant 
      Revenue&nbsp;-&nbsp;Marketing&nbsp;Cost)&nbsp;/&nbsp;Marketing&nbsp;Cost.This&nbsp;value 
      evolves&nbsp;on&nbsp;daily&nbsp;basis.Values&nbsp;are&nbsp;presented&nbsp;based 
      on&nbsp;data&nbsp;we&nbsp;have&nbsp;today.&nbsp;If&nbsp;you’d&nbsp;like 
      to&nbsp;review&nbsp;the&nbsp;evolution&nbsp;of&nbsp;values&nbsp;please&nbsp;change 
      “Cohorts&nbsp;date”&nbsp;in&nbsp;filters.',
    ],
  ],

  Grid::HEAD_GROUP_REVENUES_CPA => [
    [
      'attribute' => 'buyoutResellerProfit',
      'format' => ['currencyCustomDecimal', FormModel::DEFAULT_CURRENCY],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_CPA],
      'hint' => 'This&nbsp;is&nbsp;the&nbsp;relevant&nbsp;accrual&nbsp;of&nbsp;revenue',
      'visible' => 'canViewResellerProfit',
    ],
    [
      'attribute' => 'buyoutPartnerProfit',
      'format' => ['currencyCustomDecimal', FormModel::DEFAULT_CURRENCY],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_CPA],
      'hint' => 'Is&nbsp;the&nbsp;amount&nbsp;you’re&nbsp;paying&nbsp;for&nbsp;customer&nbsp;acquisition',
      'visible' => 'canViewResellerProfit',
    ],
    [
      'attribute' => 'buyoutMargin',
      'addAttribute' => 'buyoutMarginRate',
      'format' => ['currencyCustomDecimal', FormModel::DEFAULT_CURRENCY],
      'addFormat' => ['percent', 1],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_CPA],
      'hint' => 'It’s&nbsp;the&nbsp;difference&nbsp;between&nbsp;Gross&nbsp;revenue&nbsp;and Marketing&nbsp;Cost. 
This&nbsp;is&nbsp;basically&nbsp;what&nbsp;you&nbsp;can&nbsp;put&nbsp;in&nbsp;your&nbsp;pocket.<br>
(%&nbsp;of&nbsp;Margin&nbsp;respect&nbsp;to&nbsp;Gross)',
      'visible' => 'canViewResellerProfit',
    ],
  ],

  Grid::HEAD_GROUP_CUSTOMER_CARE_CPA => [
    [
      'attribute' => 'toBuyoutRgkComplaints',
      'addAttribute' => 'toBuyoutRgkComplaintsRate',
      'format' => 'integer',
      'addFormat' => ['percent', 1],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_CPA],
      'hint' => 'Calls&nbsp;processed&nbsp;by&nbsp;Wap.Click’s&nbsp;Customer&nbsp;Care.<br>
(%&nbsp;of&nbsp;calls&nbsp;counted&nbsp;on&nbsp;the&nbsp;Customer&nbsp;Base)',
    ],
    [
      'attribute' => 'toBuyoutCallMnoComplaints',
      'addAttribute' => 'toBuyoutCallMnoComplaintsRate',
      'format' => 'integer',
      'addFormat' => ['percent', 1],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_CPA],
      'hint' => 'Calls&nbsp;processed&nbsp;by&nbsp;Carrier,&nbsp;Provider&nbsp;or&nbsp;Authority.<br>
(%&nbsp;of&nbsp;calls&nbsp;counted&nbsp;on&nbsp;the&nbsp;Customer&nbsp;Base)',
    ],
    [
      'attribute' => 'toBuyoutRefundSum',
      'addAttribute' => 'toBuyoutRefunds',
      'addFormat' => 'integer',
      'format' => ['currencyCustomDecimal', FormModel::DEFAULT_CURRENCY],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_CPA],
      'hint' => 'Funds&nbsp;Operator&nbsp;had&nbsp;to&nbsp;compensate&nbsp;subscribers.<br>
(Number&nbsp;of&nbsp;subscribers&nbsp;affected)',
    ],
  ],

  Grid::HEAD_GROUP_TRAFFIC_OTP => [
    [
      'attribute' => 'otpHits',
      'addAttribute' => 'otpUnique',
      'format' => 'integer',
      'addFormat' => 'integer',
      'template' => [ColumnsTemplate::SYS_TEMPLATE_ONETIME],
      'hint' => 'When&nbsp;someone&nbsp;clicks&nbsp;your&nbsp;ad, it\'s&nbsp;counted&nbsp;here.<br>
(Unique:&nbsp;number&nbsp;of&nbsp;unique&nbsp;visitors)',
    ],
    [
      'attribute' => 'otpAccepted',
      'addAttribute' => 'otpAcceptedRate',
      'format' => 'integer',
      'addFormat' => ['percent', 1],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_ONETIME],
      'hint' => 'Amount&nbsp;of&nbsp;clicks&nbsp;accepted&nbsp;by&nbsp;the&nbsp;system',
    ],
  ],


  Grid::HEAD_GROUP_PERFOMANCE_OTP => [
    [
      'attribute' => 'otpOns',
      'addAttribute' => 'otpVisibleOns',
      'format' => 'integer',
      'addFormat' => 'integer',
      'template' => [ColumnsTemplate::SYS_TEMPLATE_ONETIME],
      'hint' => 'It&nbsp;shows&nbsp;the&nbsp;number&nbsp;of&nbsp;One&nbsp;Time&nbsp;Payments&nbsp;you received&nbsp;after&nbsp;ad&nbsp;interactions.<br>
(Notified:&nbsp;number&nbsp;of&nbsp;OTPs&nbsp;visible&nbsp;to&nbsp;affiliates).',
    ],
    [
      'attribute' => 'otpCr',
      'addAttribute' => 'otpVisibleCr',
      'format' => ['percent', 1],
      'addFormat' => ['percent', 1],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_ONETIME],
      'hint' => 'Conversion&nbsp;rate&nbsp;(CR) shows&nbsp;how&nbsp;often,&nbsp;on&nbsp;average, 
an&nbsp;ad&nbsp;interaction&nbsp;leads&nbsp;to&nbsp;a&nbsp;Payment. It’s&nbsp;“OTPs”&nbsp;divided&nbsp;by&nbsp;accepted&nbsp;clicks.<br>
(Notified:&nbsp;CR&nbsp;value&nbsp;visible&nbsp;to&nbsp;affiliates).',
    ],
    [
      'attribute' => 'otpAvgPartnerProfit',
      'addAttribute' => 'otpVisibleAvgPartnerProfit',
      'format' => ['currencyCustomDecimal', FormModel::DEFAULT_CURRENCY],
      'addFormat' => ['currencyCustomDecimal', FormModel::DEFAULT_CURRENCY],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_ONETIME],
      'hint' => 'Cost&nbsp;per&nbsp;Acquisition&nbsp;shows&nbsp;the&nbsp;average&nbsp;cost 
of&nbsp;a&nbsp;OTP.&nbsp;It’s&nbsp;the&nbsp;Marketing&nbsp;Cost&nbsp;divided&nbsp;by&nbsp;OTPs.<br>
(Notified:&nbsp;CPA&nbsp;value&nbsp;visible&nbsp;to&nbsp;affiliates).)'
    ],
    [
      'attribute' => 'otpRpm',
      'addAttribute' => 'otpNotifyRpm',
      'format' => ['currencyCustomDecimal', FormModel::DEFAULT_CURRENCY],
      'addFormat' => ['currencyCustomDecimal', FormModel::DEFAULT_CURRENCY],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_ONETIME],
      'hint' => 'The&nbsp;average&nbsp;amount&nbsp;earned&nbsp;per&nbsp;1,000&nbsp;Accepted&nbsp;clicks.<br>
(Notified:&nbsp;the&nbsp;average&nbsp;amount&nbsp;earned&nbsp;by&nbsp;affiliate 
per&nbsp;1,000&nbsp;Accepted&nbsp;clicks.&nbsp;It’s&nbsp;the&nbsp;marketing&nbsp;cost 
divided&nbsp;by&nbsp;Accepted&nbsp;Clicks&nbsp;per&nbsp;1,000)',
    ],
  ],

  Grid::HEAD_GROUP_REVENUES_OTP => [
    [
      'attribute' => 'otpResellerProfit',
      'format' => ['currencyCustomDecimal', FormModel::DEFAULT_CURRENCY],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_ONETIME],
      'hint' => 'This&nbsp;is&nbsp;the&nbsp;relevant&nbsp;accrual&nbsp;of&nbsp;revenue',
      'visible' => 'canViewResellerProfit',
    ],
    [
      'attribute' => 'otpPartnerProfit',
      'addAttribute' => 'otpPartnerProfitRate',
      'format' => ['currencyCustomDecimal', FormModel::DEFAULT_CURRENCY],
      'addFormat' => ['percent', 1],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_ONETIME],
      'hint' => 'Is&nbsp;the&nbsp;amount&nbsp;you’re&nbsp;paying&nbsp;for&nbsp;customer&nbsp;acquisition',
      'visible' => 'canViewResellerProfit',
    ],
    [
      'attribute' => 'otpResellerNetProfit',
      'addAttribute' => 'otpResellerNetProfitRate',
      'format' => ['currencyCustomDecimal', FormModel::DEFAULT_CURRENCY],
      'addFormat' => ['percent', 1],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_ONETIME],
      'hint' => 'This&nbsp;is&nbsp;the&nbsp;sum&nbsp;of&nbsp;fees&nbsp;you&nbsp;earned&nbsp;by&nbsp;processing 
each&nbsp;successful&nbsp;Payment.<br>
(It&nbsp;shows&nbsp;the&nbsp;ratio&nbsp;between&nbsp;Commission&nbsp;and&nbsp;Gross)',
      'visible' => 'canViewResellerProfit',
    ],
    [
      'attribute' => 'otpAdjustment',
      'addAttribute' => 'otpAdjustmentRate',
      'format' => ['currencyCustomDecimal', FormModel::DEFAULT_CURRENCY],
      'addFormat' => ['percent', 1],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_ONETIME],
      'hint' => 'This&nbsp;is&nbsp;the&nbsp;sum&nbsp;of&nbsp;Adjustments&nbsp;applied&nbsp;to&nbsp;Payments 
due&nbsp;to&nbsp;certain&nbsp;rules.<br>
(It&nbsp;shows&nbsp;the&nbsp;ratio&nbsp;between&nbsp;Adjustment&nbsp;and&nbsp;Gross)',
      'visible' => 'canViewResellerProfit',
    ],
    [
      'attribute' => 'otpTotalMargin',
      'addAttribute' => 'otpTotalMarginRate',
      'format' => ['currencyCustomDecimal', FormModel::DEFAULT_CURRENCY],
      'addFormat' => ['percent', 1],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_ONETIME],
      'hint' => 'It’s&nbsp;the&nbsp;difference&nbsp;between&nbsp;Gross&nbsp;revenue&nbsp;and&nbsp;Marketing&nbsp;Cost. 
This&nbsp;is&nbsp;basically&nbsp;what&nbsp;you&nbsp;can&nbsp;put&nbsp;in&nbsp;your&nbsp;pocket.<br>
(%&nbsp;of&nbsp;Margin&nbsp;respect&nbsp;to&nbsp;Gross)',
      'visible' => 'canViewResellerProfit',
    ],

  ],

  Grid::HEAD_GROUP_CUSTOMER_CARE_OTP => [
    [
      'attribute' => 'otpRgkComplaints',
      'addAttribute' => 'otpRgkComplaintsRate',
      'format' => 'integer',
      'addFormat' => ['percent', 1],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_ONETIME],
      'hint' => 'Calls&nbsp;processed&nbsp;by&nbsp;Wap.Click’s&nbsp;Customer&nbsp;Care.<br>
(%&nbsp;of&nbsp;calls&nbsp;counted&nbsp;on&nbsp;the&nbsp;Customer&nbsp;Base)',
    ],
    [
      'attribute' => 'otpCallMnoComplaints',
      'addAttribute' => 'otpCallMnoComplaintsRate',
      'format' => 'integer',
      'addFormat' => ['percent', 1],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_ONETIME],
      'hint' => 'Calls&nbsp;processed&nbsp;by&nbsp;Carrier,&nbsp;Provider&nbsp;or&nbsp;Authority.<br>
(%&nbsp;of&nbsp;calls&nbsp;counted&nbsp;on&nbsp;the&nbsp;Customer&nbsp;Base)',
    ],
    [
      'attribute' => 'otpRefundSum',
      'addAttribute' => 'otpRefunds',
      'addFormat' => 'integer',
      'format' => ['currencyCustomDecimal', FormModel::DEFAULT_CURRENCY],
      'template' => [ColumnsTemplate::SYS_TEMPLATE_ONETIME],
      'hint' => 'Funds&nbsp;Operator&nbsp;had&nbsp;to&nbsp;compensate&nbsp;subscribers.<br>
(Number&nbsp;of&nbsp;subscribers&nbsp;affected)'
    ],
  ],
];
