<?php

/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */
$module = Yii::$app->getModule('pages');
?>

<div class="row">
  <?php $i=0; foreach($data as $page): ?>
    <?php $images = unserialize($page->images); ?>
  <div class="col-md-3 col-sm-6 scroll" data-vp-add-class="fadeIn delay-<?= $i ?> animated">
    <img class="lazy" data-src="<?= $images[0] ?>" alt="">
    <span><?= $page->text ?></span>
  </div>
  <?php $i+=200; endforeach; ?>
</div>