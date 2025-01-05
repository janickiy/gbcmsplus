<?php

use mcms\common\helpers\Link;

/* @var mcms\common\web\View $this */
?>
<div class="empty_data empty_data-link">
  <div class="empty_data-icon">
    <i class="icon-file"></i>
    <?= Link::get('add', [], [], '<i class="icon-plus"></i>') ?>
  </div>
  <div class="empty_data-info">
    <span><?= Yii::_t('main.no_data_available') ?></span>
    <?= Link::get('add', [], [], Yii::_t('links.add_link')) ?>
  </div>
</div>
