<?php

use mcms\promo\components\LandingOperatorPrices;

/**
 * @var $modalWindow
 * @var \mcms\promo\models\LandingOperator $landing
 * @var $rebillValue
 * @var $currency
 * @var $buyoutValue
 * @var $cpaPrice
 * @var $selected
 * @var $operatorId
 * @var LandingOperatorPrices $prices
 */
?>

<div class="modal-dialog" data-landing-id="<?= $landing->landing_id ?>" data-operator-id="<?= $operatorId ?>" role="document">
  <div class="prev-landing"><div style="transform: scale(-1, -1)"><i class="icon-next""></i></div></div>
  <div class="next-landing"><i class="icon-next"></i></div>
  <div class="modal-content">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="icon-cancel_4"></i></button>
      <h4 class="modal-title" id="myModalLabel">
        <?= $landing->landing->id ?>. <?= $landing->landing->name ?>
      </h4>
    </div>
    <?= $this->render($modalWindow, compact(
      'modalWindow',
      'link',
      'landing',
      'operatorId',
      'rebillValue',
      'buyoutValue',
      'accessByRequestValue',
      'unblockedRequestStatusModerationValue',
      'unblockedRequestStatusUnlockedValue',
      'selected',
      'currency',
      'landingUnblockRequest',
      'prices'
      )); ?>
  </div>
</div>


