<?php

use mcms\statistic\components\ResellerStatisticPaymentsLink as PayLink;
use mcms\statistic\models\resellerStatistic\Item;
use mcms\statistic\models\resellerStatistic\ItemSearch;
use yii\helpers\Html;

/** @var Item $item */
/** @var ItemSearch $searchModel */
/** @var string $currency */
$paymentLink = new PayLink();
?>

<?php

$paidValues = isset($item) ? $item->paid : $searchModel->getResultValue('paid');
$resPaidValues = isset($item) ? $item->resPaid : $searchModel->getResultValue('resPaid');
$partPaidValues = isset($item) ? $item->partPaid : $searchModel->getResultValue('partPaid');

$paidCountValues = isset($item) ? $item->paidCount : $searchModel->getResultValue('paidCount');
$resPaidCountValues = isset($item) ? $item->resPaidCount : $searchModel->getResultValue('resPaidCount');
$partPaidCountValues = isset($item) ? $item->partPaidCount : $searchModel->getResultValue('partPaidCount');
?>

<?php if (!$totalPaid = $paidValues->getValue($currency)) { ?>
  <?= Yii::$app->formatter->asDecimal(0, 2) . ' (0)'; ?>
<?php } else { ?>

  <?= Html::a(
    Yii::$app->formatter->asDecimal($totalPaid, 2) . ' (' . $paidCountValues->getValue($currency) . ')',
    'javascript:void(0)',
    [
      'data' => [
        'content' => Yii::_t('statistic.reseller_profit.reseller') . ': ' .
          ($resPaidValues->getValue($currency) > 0
            ? Html::a(
              Yii::$app->formatter->asDecimal($resPaidValues->getValue($currency), 2),
              isset($item)
                ? $paymentLink->getItemLink($item, PayLink::RESELLERS, PayLink::PAID, $currency)
                : $paymentLink->getFooterLink($searchModel, PayLink::RESELLERS, PayLink::PAID, $currency),
              ['data-pjax' => 0, 'target' => '_blank']
            )
            : Yii::$app->formatter->asCurrency(0))
          . ' (' . $resPaidCountValues->getValue($currency) . ')' .
          '<br>' .
          Yii::_t('statistic.reseller_profit.partners') . ': ' .
          ($partPaidValues->getValue($currency) > 0
            ? Html::a(
              Yii::$app->formatter->asDecimal($partPaidValues->getValue($currency), 2),
              isset($item)
                ? $paymentLink->getItemLink($item, PayLink::PARTNERS, PayLink::PAID, $currency)
                : $paymentLink->getFooterLink($searchModel, PayLink::PARTNERS, PayLink::PAID, $currency),
              ['data-pjax' => 0, 'target' => '_blank']
            )
            : Yii::$app->formatter->asCurrency(0))
          . ' (' . $partPaidCountValues->getValue($currency) . ')',
        'toggle' => 'popover',
        'trigger' => 'focus',
        'placement' => 'left',
      ],
      'tabindex' => 0,
      'role' => 'button',
      'class' => 'mcms-popover',
    ]
  ); ?>
<?php } ?>





