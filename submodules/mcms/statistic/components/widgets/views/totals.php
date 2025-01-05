<?php
use mcms\common\helpers\Html;
use mcms\statistic\components\ResellerStatisticPaymentsLink as PayLink;
use mcms\statistic\components\widgets\assets\TotalsAsset;
use mcms\statistic\models\resellerStatistic\Item;
use mcms\statistic\models\resellerStatistic\ItemSearch;
use yii\web\View;

/** @var View $this */
/** @var Item $item */
/** @var ItemSearch $searchModel */
TotalsAsset::register($this);
$paymentLink = new PayLink;
?>


<div class="total-awaiting-payments">
  <div class="total-awaiting-payments__title">
    <?= Yii::_t('statistic.reseller_profit.awaiting_payments') ?>:
  </div>
  <div class="total-awaiting-payments__list total-awaiting-payments__list_collapsed">
    <div class="total-awaiting-payments__item">
      <div class="total-awaiting-payments__item-name">
        <?= Yii::$app->formatter->asCurrency($item->await->getValue('rub'), 'rub') ?>
        (<?= $item->awaitCount->getValue('rub') ?>)
      </div>
      <div class="total-awaiting-payments__item-data">
        <?= Yii::_t('statistic.reseller_profit.reseller') ?>:
        <?= $item->resAwait->getValue('rub')
          ? Html::a(
            Yii::$app->formatter->asDecimal($item->resAwait->getValue('rub'), 2),
            $paymentLink->getItemLink($item, PayLink::RESELLERS, PayLink::AWAITING, 'rub'),
            ['data-pjax' => 0, 'target' => '_blank']
          )
          : Yii::$app->formatter->asCurrency(0) ?>
          (<?= $item->resAwaitCount->getValue('rub') ?>)
        <br>
        <?= Yii::_t('statistic.reseller_profit.partners') ?>:
        <?= $item->partAwait->getValue('rub')
        ? Html::a(
          Yii::$app->formatter->asDecimal($item->partAwait->getValue('rub'), 2),
          $paymentLink->getItemLink($item, PayLink::PARTNERS, PayLink::AWAITING, 'rub'),
            ['data-pjax' => 0, 'target' => '_blank']
          )
        : Yii::$app->formatter->asCurrency(0) ?>
          (<?= $item->partAwaitCount->getValue('rub') ?>)
      </div>
    </div>
    <div class="total-awaiting-payments__item">
      <div class="total-awaiting-payments__item-name">
        <?= Yii::$app->formatter->asCurrency($item->await->getValue('usd'), 'usd') ?>
        (<?= $item->awaitCount->getValue('usd') ?>)

      </div>
        <div class="total-awaiting-payments__item-data">
          <?= Yii::_t('statistic.reseller_profit.reseller') ?>:
          <?= $item->resAwait->getValue('usd')
            ? Html::a(
              Yii::$app->formatter->asDecimal($item->resAwait->getValue('usd'), 2),
              $paymentLink->getItemLink($item, PayLink::RESELLERS, PayLink::AWAITING, 'usd'),
              ['data-pjax' => 0, 'target' => '_blank']
            )
            : Yii::$app->formatter->asCurrency(0) ?>
            (<?= $item->resAwaitCount->getValue('usd') ?>)
            <br>
          <?= Yii::_t('statistic.reseller_profit.partners') ?>:
          <?= $item->partAwait->getValue('usd')
          ? Html::a(
            Yii::$app->formatter->asDecimal($item->partAwait->getValue('usd'), 2),
            $paymentLink->getItemLink($item, PayLink::PARTNERS, PayLink::AWAITING, 'usd'),
              ['data-pjax' => 0, 'target' => '_blank']
            )
          : Yii::$app->formatter->asCurrency(0)?>
            (<?= $item->partAwaitCount->getValue('usd') ?>)
        </div>
    </div>
    <div class="total-awaiting-payments__item">
      <div class="total-awaiting-payments__item-name">
        <?= Yii::$app->formatter->asCurrency($item->await->getValue('eur'), 'eur') ?>
        (<?= $item->awaitCount->getValue('eur') ?>)
      </div>
        <div class="total-awaiting-payments__item-data">
          <?= Yii::_t('statistic.reseller_profit.reseller') ?>:
          <?= $item->resAwait->getValue('eur')
            ? Html::a(
              Yii::$app->formatter->asDecimal($item->resAwait->getValue('eur'), 2),
              $paymentLink->getItemLink($item, PayLink::RESELLERS, PayLink::AWAITING, 'eur'),
              ['data-pjax' => 0, 'target' => '_blank']
            )
            : Yii::$app->formatter->asCurrency(0) ?>
            (<?= $item->resAwaitCount->getValue('eur') ?>)
            <br>
          <?= Yii::_t('statistic.reseller_profit.partners') ?>:
          <?= $item->partAwait->getValue('eur')
          ? Html::a(
            Yii::$app->formatter->asDecimal($item->partAwait->getValue('eur'), 2),
            $paymentLink->getItemLink($item, PayLink::PARTNERS, PayLink::AWAITING, 'eur'),
              ['data-pjax' => 0, 'target' => '_blank']
            )
          : Yii::$app->formatter->asCurrency(0)?>
            (<?= $item->partAwaitCount->getValue('eur') ?>)
        </div>
    </div>
  </div>
</div>

<div class="total-debt">
  <div class="total-debt__title">
    <?= Yii::_t('statistic.reseller_profit.total_debt') ?>:
  </div>
  <?php $viewPath = '@mcms/statistic/views/reseller-profit/_debt_cell'; ?>
  <div class="total-debt__list total-debt__list_collapsed">
    <div class="total-debt__item">
      <?= $this->render($viewPath, ['searchModel' => $searchModel, 'currency' => 'rub']) ?>
    </div>
    <div class="total-debt__item">
      <?= $this->render($viewPath, ['searchModel' => $searchModel, 'currency' => 'usd']) ?>
    </div>
    <div class="total-debt__item">
      <?= $this->render($viewPath, ['searchModel' => $searchModel, 'currency' => 'eur']) ?>
    </div>
  </div>
</div>