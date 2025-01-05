<?php

use yii\helpers\Html;

/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */

?>

<h2><?= $category->name ?></h2>
<div class="row stages">
  <div class="line"><span></span></div>
  <?php foreach($data as $page): ?>
    <?php $images = unserialize($page->images); ?>
    <div class="col-3">
      <?= Html::img($images[0])?>
      <p><?= $page->text ?></p>
    </div>
  <?php endforeach ?>
</div>
<div class="row iphones">
  <?php foreach($data as $page): ?>
  <div class="col-3">
    <?= Html::img($page->getPropByCode('iphone_image')->getImageUrl())?>
  </div>
  <?php endforeach ?>
</div>
