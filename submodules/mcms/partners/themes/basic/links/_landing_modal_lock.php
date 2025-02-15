<?php

use mcms\promo\components\LandingOperatorPrices;

/**
 * @var \mcms\promo\models\LandingOperator $landing
 * @var $rebillValue
 * @var $currency
 * @var $buyoutValue
 * @var $cpaPrice
 * @var $selected
 * @var LandingOperatorPrices $prices
 */
?>

<div class="modal-body">
  <div class="row" >
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

        <div class="col-xs-<?= $xsSize?>">
          <div>
            <i class="icon-lock"></i>
            <div><?= Yii::_t('partners.links.locked') ?></div>
          </div>
        </div>

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
      <a id="modal_request_open" class="btn btn-success" data-landing-id="<?= $landing->landing_id ?>" data-operator-id="<?= $operatorId ?>"><?= Yii::_t('partners.links.send_request') ?></a>
    </div>
  </div>
</div>