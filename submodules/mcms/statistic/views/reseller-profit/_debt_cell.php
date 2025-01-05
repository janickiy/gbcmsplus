<?php

use mcms\statistic\models\resellerStatistic\Item;
use mcms\statistic\models\resellerStatistic\ItemSearch;
use rgk\utils\widgets\modal\Modal;
use mcms\statistic\components\ResellerStatisticHoldLink as HoldLink;
use mcms\statistic\components\ResellerStatisticInvoicesLink as InvoicesLink;
use yii\helpers\Html;

/** @var Item $item */
/** @var ItemSearch $searchModel */
/** @var string $currency */
/** @var bool $hideHolds Прятать холды в футере.
 * Приходит только в том случае, если нужно скрыть холды в футере таблицы,
 * поэтому проверяется функцией isset()
 */

// TRICKY Эта вьюха ещё используется в шапке таблицы, где показываются суммы Totals
$debtLink = new HoldLink();
$invoicesLink = new InvoicesLink();
$link = isset($item) ? $debtLink->getItemLink($item, $currency) : $debtLink->getFooterLink($searchModel, $currency);
$debtValues = isset($item) ? $item->debt : $searchModel->getResultValue('debt');
$penaltiesValues = isset($item) ? $item->penalties : $searchModel->getResultValue('penalties');
$penaltiesCountValues = isset($item) ? $item->penaltiesCount : $searchModel->getResultValue('penaltiesCount');
$compensationsValues = isset($item) ? $item->compensations : $searchModel->getResultValue('compensations');
$compensationsCountValues = isset($item) ? $item->compensationsCount : $searchModel->getResultValue('compensationsCount');
$holdedValues = isset($item) ? $item->holded : $searchModel->getResultValue('holded');
$unholdedValues = isset($item) ? $item->unholded : $searchModel->getResultValue('unholded');
$awaitValues = isset($item) ? $item->await : $searchModel->getResultValue('await');
$paidValues = isset($item) ? $item->paid : $searchModel->getResultValue('paid');
$awaitCountValues = isset($item) ? $item->awaitCount : $searchModel->getResultValue('awaitCount');
$paidCountValues = isset($item) ? $item->paidCount : $searchModel->getResultValue('paidCount');
$convertIncreaseValues = isset($item) ? $item->convertIncreases : $searchModel->getResultValue('convertIncreases');
$convertIncreaseCount = isset($item) ? $item->convertIncreasesCount : $searchModel->getResultValue('convertIncreasesCount');
$convertDecreaseValues = isset($item) ? $item->convertDecreases : $searchModel->getResultValue('convertDecreases');
$convertDecreaseCount = isset($item) ? $item->convertDecreasesCount : $searchModel->getResultValue('convertDecreasesCount');

$creditsValues = isset($item) ? $item->credits : $searchModel->getResultValue('credits');
$creditsCountValues = isset($item) ? $item->creditsCount : $searchModel->getResultValue('creditsCount');
$creditChargesValues = isset($item) ? $item->creditCharges : $searchModel->getResultValue('creditCharges');

$debt = round($debtValues->getValue($currency), 3);
$holdValue = round($holdedValues->getValue($currency), 3);
?>

<?= ($debt != 0
  ? Html::a(Yii::$app->formatter->asCurrency($debt), 'javascript:void(0)', [
    'data' => [
      'content' =>
        Yii::_t('statistic.reseller_profit.unholded') . ': ' .
        Yii::$app->formatter->asCurrency($unholdedValues->getValue($currency)) .
        '<br>' .
        Yii::_t('statistic.reseller_profit.penalties') . ': ' .
        ($penaltiesValues->getValue($currency) != 0
          ? Html::a(
            Yii::$app->formatter->asCurrency($penaltiesValues->getValue($currency)) .
            ' (' . $penaltiesCountValues->getValue($currency) . ')',
            isset($item)
              ? $invoicesLink->getItemLink($item, InvoicesLink::PENALTY, $currency)
              : $invoicesLink->getFooterLink($searchModel, InvoicesLink::PENALTY, $currency)
            ,
            ['data-pjax' => 0, 'target' => '_blank']
          )
          : Yii::$app->formatter->asCurrency(0) . ' (0)'
        ) .
        '<br>' .
        Yii::_t('statistic.reseller_profit.compensations') . ': ' .
        ($compensationsValues->getValue($currency) != 0
          ? Html::a(
            Yii::$app->formatter->asCurrency($compensationsValues->getValue($currency)) .
            ' (' . $compensationsCountValues->getValue($currency) . ')',
            isset($item)
              ? $invoicesLink->getItemLink($item, InvoicesLink::COMPENSATION, $currency)
              : $invoicesLink->getFooterLink($searchModel, InvoicesLink::COMPENSATION, $currency)
            ,
            ['data-pjax' => 0, 'target' => '_blank']
          )
          : Yii::$app->formatter->asCurrency(0) . ' (0)'
        ) .
        '<br>' .
        Yii::_t('statistic.reseller_profit.awaiting_payments') . ': ' .
        Yii::$app->formatter->asCurrency($awaitValues->getValue($currency)) .
        ' (' . $awaitCountValues->getValue($currency) . ')' .
        '<br>' .
        Yii::_t('statistic.reseller_profit.credits') . ': ' .
        Yii::$app->formatter->asCurrency($creditsValues->getValue($currency)) .
        ' (' . $creditsCountValues->getValue($currency) . ')' .
        '<br>' .
        Yii::_t('statistic.reseller_profit.credit_charges') . ': ' .
        Yii::$app->formatter->asCurrency($creditChargesValues->getValue($currency)) .
        '<br>' .
        Yii::_t('statistic.reseller_profit.convert_increase') . ': ' .
        Yii::$app->formatter->asCurrency($convertIncreaseValues->getValue($currency)) .
        ' (' . $convertIncreaseCount->getValue($currency) . ')' .
        '<br>' .
        Yii::_t('statistic.reseller_profit.convert_decrease') . ': ' .
        Yii::$app->formatter->asCurrency($convertDecreaseValues->getValue($currency)) .
        ' (' . $convertDecreaseCount->getValue($currency) . ')'
      ,
      'toggle' => 'popover',
      'trigger' => 'focus',
      'placement' => 'left',
    ],
    'tabindex' => 0,
    'role' => 'button',
    'class' => 'mcms-popover'
  ])
  : Yii::$app->formatter->asCurrency(0)) . (
!isset($hideHolds)
  ? ' (' . ($holdValue != 0 ?
    Modal::widget([
      'toggleButtonOptions' => [
        'tag' => 'a',
        'label' => Yii::$app->formatter->asCurrency($holdValue),
        'title' => Yii::_t('statistic.reseller_profit.hold_title'),
        'class' => 'mcms-popover',
      ],
      // TRICKY НЕ СТАВИТЬ СЮДА Url::to(), ссылка должна быть массивом, иначе не пройдет проверку прав
      'url' => $link,
    ])
    : Yii::$app->formatter->asCurrency(0)) . ')'
  : ''
); ?>