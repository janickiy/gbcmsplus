<?php
/* @var string $id */
/* @var string $label */
/* @var string $from */
/* @var string $to */
/* @var boolean $shouldUpperCaseLabel */
?>

<div class="col-xs-20 hidden" id="<?= $id ?>">
  <div class="filter filter_custom">
    <div class="filter-header">
      <span><span><?= $label ?> <i data-from="<?= mb_strtolower(Yii::_t('statistic.from')) ?>" data-to="<?= mb_strtolower(Yii::_t('statistic.to')) ?>"></i></span></span>
      <div class="delete_filter"><i class="icon-cancel_4"></i></div>
      <div class="caret_wrap">
        <i class="caret"></i>
      </div>
    </div>
    <div class="filter-body filter-body_left">
      <div class="filter-list-settings">
        <div class="form-group">
          <label><?= Yii::_t('statistic.settings') ?> <?= $shouldUpperCaseLabel ? $label : mb_strtolower($label) ?></label>
          <input data-id="<?= $from ?>" data-index="1" type="text" class="disable_change_trigger custom-filter-input form-control" placeholder="<?= Yii::_t('statistic.from') ?>">
        </div>
        <div class="form-group">
          <input data-id="<?= $to ?>" data-index="2" type="text" class="disable_change_trigger custom-filter-input form-control" placeholder="<?= Yii::_t('statistic.to') ?>">
        </div>
      </div>
    </div>
  </div>
</div>
