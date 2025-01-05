<?php

use mcms\promo\components\LandingOperatorPrices;

/** @var \mcms\promo\models\LandingOperator[] $landings */
/** @var \mcms\promo\models\LandingOperator $landingOperator */

?>
<?php foreach ($landings as $landingOperator):  ?>
  <?php $prices = new LandingOperatorPrices($landingOperator, Yii::$app->user->id); ?>
  <?php

  $landing = $landingOperator->landing;

  if ($landing->isHiddenBlocked() || $landingOperator->is_deleted) continue;

  Yii::beginProfile('landingRenderingLoop', 'landings');

  $isRequestStatusNotUnlocked = $landing->isRequestStatusNotUnlocked();
  $isRequestStatusBlocked = $landing->isRequestStatusBlocked();
  $isRequestStatusUnlocked = $landing->isRequestStatusUnlocked();
  $sort = $landing->id;
  if ($isRequestStatusUnlocked && ($unblockRequest = $landing->landingUnblockRequestCurrentUser)) {
    $sort = $unblockRequest->updated_at;
  } else if ($isRequestStatusNotUnlocked || $isRequestStatusBlocked) {
    $sort = -1;
  }

  $class = ['category-' . $landing->category_id, 'offer-' . $landing->offer_category_id];

  $landingOperatorKey = $landingOperator->landing_id . '_' . $landingOperator->operator_id;
  foreach ($landingPayTypes[$landingOperatorKey] as $payType) {
    $class[] = 'paytype-' . $payType->id;
  }
  ?>

  <li class="item <?= implode(' ', $class) ?>" data-landing-id="<?= $landing->id ?>" data-sort="<?= $sort ?>"
      data-operator-id="<?= $landingOperator->operator_id ?>" id="lpid<?= $landing->id ?>">

    <div class="addLinks__lands-title open_lp_modal"><span><?= $landing->id ?>. <?= $landing->name ?></span></div>
    <?php $statusName = 'status__active';
      if ($isRequestStatusNotUnlocked) $statusName = 'status__lock';
      if ($landing->isRequestStatusBlocked()) $statusName .= ' status__blocked';
      if ($landing->isRequestStatusModeration()) $statusName .= ' status__wait';
    ?>
    <div class="addLinks__lands-img landingSelectDisplay <?= $statusName ?>">
      <div class="label__new">
        <!-- <i class="country__new">new</i> -->
        <!-- <i class="country__hot">HOT</i> -->
      </div>
      <div class="addLinks-img-overlay">
        <div class="open_lp_modal"></div>
        <i <?php if ($isRequestStatusNotUnlocked): ?>class="open_lp_modal"<?php endif; ?> data-selected="<?= Yii::_t('links.selected') ?>" data-cancel="<?= Yii::_t('links.cancel') ?>"></i>
      </div>
      <div class="land_image_box">
        <img src="<?= $landing->image_src ?>" alt="">
      </div>

      <div class="addLinks-hidden open_lp_modal">
        <span class="addLinks__showFull"><i class="icon-plus1"></i><?= Yii::_t('links.more') ?></span>
      </div>

    </div>

    <?php $isSameForOperator = $landing->isSameForOperators() ?>
    <?php $landingOperatorsIdList = $landing->getLandingOperators(Yii::$app->user->id) ?>
    <div class="addLinks__lands-info">
      <div class="row">
        <div class="date__create"><?= Yii::$app->formatter->asDate($landing->created_at, 'php:d.m.Y') ?></div>
        <?php if(!$landingOperator->isOnetime): ?>
          <div class="col-xs-6 selectedValueSwitch set_land_selected value-<?= $rebillValue ?>" data-value="<?= $rebillValue ?>" data-profit-type="<?= $rebillValue ?>" data-landing-id="<?= $landingOperator->landing_id ?>" data-operator-id="<?= $landingOperator->operator_id ?>"
            <?php if ($isSameForOperator):?> data-operator-ids="<?= $landingOperatorsIdList ?>"<?php endif;?>>
            <span class="addLinks__price number"><?= Yii::$app->formatter->asLandingPrice($prices->getRebillPrice(), $currency); ?></span>
            <span class="addLinks__name"><?= Yii::_t('links.rebill') ?></span>
          </div>
        <?php else: ?>
          <div class="col-xs-6"></div>
        <?php endif; ?>

        <?php $cpaPrice = $prices->getCpaPrice(); ?>
        <?php if($cpaPrice != 0): ?>
          <div class="col-xs-6 selectedValueSwitch set_land_selected value-<?= $buyoutValue ?> text-right" data-value="<?= $buyoutValue ?>" data-profit-type="<?= $buyoutValue ?>" data-landing-id="<?= $landingOperator->landing_id ?>" data-operator-id="<?= $landingOperator->operator_id ?>"
            <?php if ($isSameForOperator):?> data-operator-ids="<?= $landingOperatorsIdList ?>"<?php endif;?>>
            <span class="addLinks__price number_1"><?= Yii::$app->formatter->asLandingPrice($cpaPrice, $currency); ?></span>
            <span class="addLinks__name"><?= Yii::_t('links.buyout') ?></span>
          </div>
        <?php endif; ?>
      </div>

    </div>
  </li>
<?php Yii::endProfile('landingRenderingLoop', 'landings'); ?>
<?php endforeach; ?>
