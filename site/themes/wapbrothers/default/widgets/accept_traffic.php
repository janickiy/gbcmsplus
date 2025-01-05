<?php

/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */
$module = Yii::$app->getModule('pages');
?>
<div class="logo-countryes clearfix">
  <?php foreach($data as $page): ?>
    <?php $images = unserialize($page->images); ?>
    <img src="<?= $images[0] ?>" width="67" height="47" title="<?= $page->name ?>" alt="<?= $page->name ?>" class="img-responsive">
  <?php endforeach ?>
</div>