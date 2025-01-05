<?php

/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */
$module = Yii::$app->getModule('pages');
?>

<?php $i = 0; foreach($data as $page): ?>
  <?php $images = unserialize($page->images); ?>
<div class="col-sm-6 col-xs-12 scroll" data-vp-add-class="<?= $i % 2 == 0 ? 'fadeInLeft' : 'fadeInRight' ?> animated" data-delay="0">
  <div class="bg_brown">
    <img class="lazy" data-src="<?= $images[0] ?>" alt="">
    <h3><?= $page->name ?></h3>
    <span><?= $page->text ?></span>
  </div>
</div>
<?php $i++; endforeach ?>
