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
$awaitValues = isset($item) ? $item->await : $searchModel->getResultValue('await');
$resAwaitValues = isset($item) ? $item->resAwait : $searchModel->getResultValue('resAwait');
$partAwaitValues = isset($item) ? $item->partAwait : $searchModel->getResultValue('partAwait');

$awaitCountValues = isset($item) ? $item->awaitCount : $searchModel->getResultValue('awaitCount');
$resAwaitCountValues = isset($item) ? $item->resAwaitCount : $searchModel->getResultValue('resAwaitCount');
$partAwaitCountValues = isset($item) ? $item->partAwaitCount : $searchModel->getResultValue('partAwaitCount');
?>
<?php if (!$totalAwait = $awaitValues->getValue($currency)) { ?>
  <?= Yii::$app->formatter->asDecimal(0, 2) . ' (0)'; ?>
<?php } else { ?>
  <?= Html::a(
    Yii::$app->formatter->asDecimal($totalAwait, 2) . ' (' . $awaitCountValues->getValue($currency) . ')',
    'javascript:void(0)',
    [
      'data' => [
        'content' => Yii::_t('statistic.reseller_profit.reseller') . ': ' .
          Yii::$app->formatter->asDecimal($resAwaitValues->getValue($currency), 2)
          . ' (' . $resAwaitCountValues->getValue($currency) . ')' .
          '<br>' .
          Yii::_t('statistic.reseller_profit.partners') . ': ' .
          Yii::$app->formatter->asDecimal($partAwaitValues->getValue($currency), 2)
          . ' (' . $partAwaitCountValues->getValue($currency) . ')',
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




