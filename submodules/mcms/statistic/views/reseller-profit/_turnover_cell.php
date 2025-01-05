<?php

use mcms\statistic\models\resellerStatistic\Item;
use mcms\statistic\models\resellerStatistic\ItemSearch;
use yii\helpers\Html;

/** @var Item $item */
/** @var ItemSearch $searchModel */
/** @var string $currency */
?>

<?php
$formatter = Yii::$app->formatter;
$resProfit = isset($item) ? $item->resProfit : $searchModel->getResultValue('resProfit');
$revshareValues = isset($item) ? $item->resProfitRevshare : $searchModel->getResultValue('resProfitRevshare');
$cpaValues = isset($item) ? $item->resProfitCpa : $searchModel->getResultValue('resProfitCpa');
$cpaSoldValues = isset($item) ? $item->resProfitCpaSold : $searchModel->getResultValue('resProfitCpaSold');
$cpaRejectedValues = isset($item) ? $item->resProfitCpaRejected : $searchModel->getResultValue('resProfitCpaRejected');
$onetimeValues = isset($item) ? $item->resProfitOnetime : $searchModel->getResultValue('resProfitOnetime');
?>

<?php
if (!$totalProfit = $resProfit->getValue($currency)) { ?>
  <?= $formatter->asDecimal(0, 2); ?>
<?php } else { ?>
  <?php
  $revshare = $revshareValues->getValue($currency);
  $cpa = $cpaValues->getValue($currency);
  $onetime = $onetimeValues->getValue($currency);
  $content = 'Revshare: ' . $formatter->asDecimal($revshare, 2) . '<br>' .
    'CPA: ' . $formatter->asDecimal($cpa, 2) .
    ' (' . $formatter->asDecimal($cpaSoldValues->getValue($currency), 2) .
    ' + ' . $formatter->asDecimal($cpaRejectedValues->getValue($currency), 2) . ')<br>' .
    'OTP: ' . $formatter->asDecimal($onetime, 2) . '<br>';
  if (!$revshare && !$cpa && !$onetime) $content = 'Детальная информация отсутствует';
  ?>

  <?= Html::a($formatter->asDecimal($totalProfit, 2), 'javascript:void(0)', [
    'data' => [
      'content' =>
        $content,
      'toggle' => 'popover',
      'trigger' => 'focus',
      'placement' => 'left',
    ],
    'tabindex' => 0,
    'role' => 'button',
    'class' => 'mcms-popover'
  ]); ?>
<?php } ?>