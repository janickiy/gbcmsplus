<?php
/**
 * @var \mcms\promo\models\LandingOperator $landing
 * @var \mcms\promo\models\Source $link
 */

use yii\helpers\Html;
use mcms\promo\models\LandingOperator;
use yii\helpers\Url;

?>

<div class="line__text">
  <span><?= nl2br($landing->landing->description) ?></span>
</div>
<?php if ($offerCategory = $landing->landing->offerCategory): ?>
  <div class="line__text-header"><?= Yii::_t('partners.links.offer_category'); ?></div>
  <div class="line__text">
    <?= $offerCategory->name ?>
  </div>
<?php endif ?>
<?php if ($payTypesText = $landing->getPayTypesNameText()): ?>
  <div class="line__text-header"><?= Yii::_t('partners.links.tariffing'); ?></div>
  <div class="line__text">
    <?= $payTypesText; ?><br />
    <?= Yii::_t('promo.landings.operator-attribute-subscriber_cost_price') . ': ' . $landing->cost_price .
    (is_numeric($landing->cost_price) ? Yii::_t('promo.main.main_currency-' . $landing->defaultCurrency->code) : '') ?>
    <?php if ($landing->landing->rebill_period): ?>
      <?= Yii::_t('promo.landings.rebill_period', ['n' => $landing->landing->rebill_period]) ?><br>
    <?php endif ?>
  </div>
<?php endif; ?>
<?php if ($platformsText = $landing->landing->getPlatformsNameText()): ?>
  <div class="line__text-header"><?= Yii::_t('partners.main.platforms'); ?></div>
  <div class="line__text"><?= $platformsText; ?></div>
<?php endif; ?>
<?php if ($landing->landing): ?>
  <div class="line__text-header"><?= Yii::_t('partners.main.operators'); ?></div>
  <div class="line__text">
    <?php if ($landing->landing->isSameForOperators()): ?>
      <?php foreach ($landing->landing->operator as $operator): /* @var $operator \mcms\promo\models\Operator */ ?>
        <?php if (!LandingOperator::findOne(['operator_id' => $operator->id, 'landing_id' => $landing->landing_id, 'is_deleted' => 0])) continue;?>
        <?php if ($operator->isTrafficBlocked()) continue;?>
        <div class="checkbox checkbox-primary checkbox-inline">
          <?= Html::checkbox('LandingOperator', $link
            ? $link->isSelectedOperatorForLanding($operator->id, $landing->landing_id)
            || $link->isNoOperatorSelected($landing->landing->operator, $landing->landing_id)
            : true, [
            'id' => 'L' . $landing->landing_id . 'O' . $operator->id,
            'data-landing-id' => $landing->landing_id,
            'data-operator-id' => $operator->id,
            'class' => 'styled']) . Html::label($operator->name, 'L' . $landing->landing_id . 'O' . $operator->id) ?>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <?= $landing->landing->getOperatorsTextOrNames() ?>
    <?php endif; ?>
  </div>
<?php endif; ?>
<?php if ($trafficText = $landing->landing->getForbiddenTrafficTypesNames()): ?>
  <div class="line__text-header"><?= Yii::_t('partners.links.forbidden') ?></div>
  <div class="line__text"><?= $trafficText; ?></div>
<?php endif; ?>
<?php if ($landing->landing->service_url && $landing->operator->show_service_url) : ?>
  <div class="line__text-header"><?= Yii::_t('partners.links.service') ?></div>
  <div class="line__text">
    <?= $landing->landing->getServiceDomain()
      ? Html::a($landing->landing->getServiceDomain(), $landing->landing->service_url, ['target' => '_blank'])
      : $landing->landing->service_url
    ?>
  </div>
<?php endif; ?>
