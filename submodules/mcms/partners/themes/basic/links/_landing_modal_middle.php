<?php

use mcms\promo\components\LandingOperatorPrices;

/**
 * @var \mcms\promo\models\LandingOperator $landing
 * @var $currency
 * @var LandingOperatorPrices $prices
 */
?>

<?php if (!$landing->isOnetime) : ?>
  <div class="col-xs-<?= $xsSize ?>">
    <div class="lands__modal-info">
      <span class="addLinks__price"><?= Yii::$app->formatter->asLandingPrice($prices->getRebillPrice(), $currency) ?></span>
    </div>
    <span><?= Yii::_t('partners.links.rebill') ?></span>
  </div>
<?php endif; ?>

<?php if ($prices->getCpaPrice() !== 0) : ?>
  <div class="col-xs-<?= $xsSize ?>">
    <div class="lands__modal-info">
      <span class="addLinks__price"><?= Yii::$app->formatter->asLandingPrice($prices->getCpaPrice(), $currency); ?></span>
    </div>
    <span><?= Yii::_t('partners.links.buyout') ?></span>
  </div>
<?php endif; ?>

<?php if ($landing->days_hold) : ?>
<div class="col-xs-<?= $xsSize?>">
  <div class="lands__modal-info">
    <span class="addLinks__price"><?= Yii::_t('partners.links.hold_days', ['n' => $landing->days_hold]) ?></span>
  </div>
  <span><?= mb_strtoupper(Yii::_t('partners.links.hold')) ?></span>
</div>
<?php endif; ?>
