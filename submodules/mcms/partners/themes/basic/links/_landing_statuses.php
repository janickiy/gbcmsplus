<?php
/* @var mcms\promo\models\Source $link */
?>

<?php if ($link && ($link->isStatusModeration() || $link->getLandingsOnModeration() || $link->getBlockedOperatorsList() || $link->getDisabledLandings() || $link->getLandingsLocked())): ?>
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
            <?= implode(', ', $landings) ?></div>
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
          <div><?= Yii::_t('links.traffic') ?> <b><?= implode(', ', $operators) ?></b> <?= Yii::_t('links.traffic_blocked') ?></div>
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
<?php endif;?>