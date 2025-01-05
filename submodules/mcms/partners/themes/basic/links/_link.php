<?php
use mcms\common\helpers\Link;

/* @var mcms\promo\models\Source $link */

$disabledPosbacksClass = $countPostbacks > 0 ? '' : ' disabled';
?>
<div class="collapse-content text-center" style="padding: 0;">
  <div class="link_list-copy">
    <span class="link_list-copy-label"><?= Yii::_t('partners.links.our_link') ?>:</span>

    <div class="link_list-copy-link selected__text clipboard">
      <span id="link"><?= $link->getLink() ?></span>
      <span
        class="btn btn-white btn-white_copy copy-button"
        data-clipboard-target="#link" title="<?= Yii::_t('partners.main.copy') ?>">
        <i class="icon-blank"></i>
      </span>
    </div>
    <div class="link_list-copy-buttons">
      <?=
      $link->isSmartLink()
        ? Link::get('/partners/smart-links/update/', ['id' => $link->id], ['data-pjax' => 0, 'class' => 'btn btn-white'],
        '<i class="icon-options"></i> ' . Yii::_t('partners.links.tune'))
        : Link::get('add', ['id' => $link->id, '#' => $link->stream->isEnabled() ? 'step_3' : 'step_1'], ['data-pjax' => 0, 'class' => 'btn btn-white'],
        '<i class="icon-options"></i> ' . Yii::_t('partners.links.tune'));
      ?>
      <?= Link::get('export-postbacks', ['id' => $link->id], ['data-pjax' => 0, 'class' => 'btn btn-white' . $disabledPosbacksClass, 'target' => '_blank'],
        '<i class="icon-export"></i> ' . Yii::_t('partners.links.export_postbacks')); ?>
    </div>
  </div>
  <?php if ($link->isStatusModeration() || $link->getLandingsOnModeration() || $link->getBlockedOperatorsList() || $link->getDisabledLandings() || $link->getLandingsLocked()): ?>
  <div class="link_copy-bottom">
    <?php if ($link->isStatusModeration()): ?>
      <div class="link_copy-bottom-row">
        <div class="link_copy-bottom-col">
          <i class="icon-lock"></i>
          <div><?= Yii::_t('links.landings_on_moderation') ?></div>
          <span class="small"><?= Yii::_t('links.traffic_before_approval') ?></span>
        </div>
      </div>
    <?php endif; ?>
    <?php if (($landings = $link->getLandingsOnModeration()) && !$link->isStatusModeration()): ?>
    <div class="link_copy-bottom-row">
      <div class="link_copy-bottom-col">
        <i class="icon-lock"></i>
        <div><?= Yii::_t('links.landing_on_moderation', ['n' => count($landings)]) ?>:
          <?= implode(', ', $landings) ?>
        </div>
        <span class="small"><?= Yii::_t('links.traffic_before_approval') ?></span>
      </div>
    </div>
    <?php endif; ?>
    <?php if ($landings = $link->getLandingsLocked()): ?>
      <div class="link_copy-bottom-row">
        <div class="link_copy-bottom-col">
          <i class="icon-dismiss"></i>
          <div><?= Yii::_t('links.landing_locked', ['n' => count($landings)]) ?>:
            <?= implode(', ', $landings) ?>
          </div>
          <span class="small"><?= Yii::_t('links.traffic_land_blocked') ?></span>
        </div>
      </div>
    <?php endif; ?>
    <?php if ($operators = $link->getBlockedOperatorsList()): ?>
    <div class="link_copy-bottom-row">
      <div class="link_copy-bottom-col">
        <i class="icon-dismiss"></i>
        <div><?= Yii::_t('links.traffic') ?> <b><?= implode(', ', $operators) ?></b> <?= Yii::_t('links.traffic_blocked') .
          ($link->operator_blocked_reason ? ': ' . $link->operator_blocked_reason : '') ?></div>
        <span class="small"><?= Yii::_t('links.blocked_operator_traffic') ?></span>
      </div>
    </div>
    <?php endif; ?>
    <?php if ($landings = $link->getDisabledLandings()): ?>
      <div class="link_copy-bottom-row">
        <div class="link_copy-bottom-col">
          <i class="icon-dismiss"></i>
          <div><?= Yii::_t('links.landing_disabled', ['n' => count($landings)]) ?>:
            <?= implode(', ', $landings) ?>
          </div>
          <span class="small"><?= Yii::_t('links.traffic_land_blocked') ?></span>
        </div>
      </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>