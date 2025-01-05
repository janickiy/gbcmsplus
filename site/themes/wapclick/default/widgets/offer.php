<?php

/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */
$module = Yii::$app->getModule('pages');
?>

<div class="row">
  <?php $i=0; foreach($data as $page): ?>
    <?php $images = unserialize($page->images); ?>
  <div class="col-md-2 col-sm-4 scroll" data-vp-add-class="bounceIn delay-<?= $i ?> animated">
    <div class="adv-img">
      <img class="lazy" data-src="<?= $images[0] ?>" alt="">
    </div>
    <span><?= $page->name ?></span>
  </div>
  <?php $i+=200; endforeach; ?>
</div>