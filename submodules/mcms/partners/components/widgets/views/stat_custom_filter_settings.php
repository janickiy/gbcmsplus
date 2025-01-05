<?php
/* @var string $id */
/* @var string $label */
/* @var string $from */
/* @var string $to */
/* @var boolean $shouldUpperCaseLabel */
?>

<li class=""><a class="user_custom-filter" href="#<?= $id ?>"><?= $label ?> <span class="icomoon icon-checked check-mark"></span></a>
  <div class="filter-list-settings">
    <div class="form-group">
      <label><?= Yii::_t('statistic.settings') ?> <?= $shouldUpperCaseLabel ? $label : mb_strtolower($label) ?></label>
      <input name="<?= $from ?>" data-index="1" type="text" class="disable_change_trigger form-control" placeholder="<?= Yii::_t('statistic.from') ?>">
    </div>
    <div class="form-group">
      <input name="<?= $to ?>" data-index="2" type="text" class="disable_change_trigger form-control" placeholder="<?= Yii::_t('statistic.to') ?>">
    </div>
    <div class="form-group text-center">
      <a data-add="<?= Yii::_t('statistic.add') ?>" data-delete="<?= Yii::_t('statistic.delete') ?>"><span class="btn btn-success btn-sm"><?= Yii::_t('statistic.add') ?></span></a>
    </div>
  </div>
</li>