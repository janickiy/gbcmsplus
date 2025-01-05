<?php

use mcms\promo\components\LandingOperatorPrices;

/**
 * @var \mcms\promo\models\LandingOperator $landing
 * @var $rebillValue
 * @var $currency
 * @var $buyoutValue
 * @var $selected
 * @var LandingOperatorPrices $prices
 */
?>

<div class="modal-body">
  <div class="row">
  <div class="col-xs-6 lands__modal-l">
    <img src="<?= $landing->landing->image_src ?>" alt="">
    <?php if ($landing->landing->getActualPromoMaterials()): ?>
      <a href="<?=$landing->landing->getActualPromoMaterials()?>" class="promo-download">
        <i class="icon-zipicon2"></i>
        <span><?= Yii::_t('partners.links.download-promo-materials') ?></span>
      </a>
    <?php endif ?>
  </div>
    <div class="col-xs-6 lands__modal-r">
      <div class="row">
        <?php $xsSize = $landing->isOnetime || !$prices->getCpaPrice() ? (!$landing->days_hold ? 6 : 4) : (!$landing->days_hold ? 4 : 3); ?>
        <?= $this->render('_landing_modal_middle', [
          'currency' => $currency,
          'landing' => $landing,
          'xsSize' => $xsSize,
          'prices' => $prices,
        ]); ?>
      </div>
      <?= $this->render('_landing_modal_text_middle', ['landing' => $landing, 'link' => $link]); ?>
    </div>
  </div>
</div>
<div class="modal-footer">
  <div class="row">
    <div class="col-xs-6 text-left">
      <span class="go_back"><i class="icon-double_arrow"></i><?= Yii::_t('main.prev') ?></span>
    </div>
    <div class="col-xs-6">
      <?php if(!$landing->isOnetime): ?>
        <?php if ($selected == $rebillValue): ?>
          <a class="btn btn-selected disabled"><i class="icon-checked"></i> <?= Yii::_t('partners.links.rebill_label') ?></a>
        <?php else: ?>
          <a class="btn set_land_selected btn-success" data-profit-type="<?= $rebillValue ?>" data-landing-id="<?= $landing->landing_id ?>" data-operator-id="<?= $landing->operator_id ?>"><?= Yii::_t('partners.links.rebill_label') ?></a>
        <?php endif; ?>
      <?php endif; ?>

      <?php if($prices->getCpaPrice() !== 0): ?>
        <?php if ($selected == $buyoutValue): ?>
          <a class="btn btn-selected disabled"><i class="icon-checked"></i> <?= Yii::_t('partners.links.buyout_label') ?></a>
        <?php else: ?>
          <a class="btn set_land_selected btn-success" data-profit-type="<?= $buyoutValue ?>" data-landing-id="<?= $landing->landing_id ?>" data-operator-id="<?= $landing->operator_id ?>"><?= Yii::_t('partners.links.buyout_label') ?></a>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</div>