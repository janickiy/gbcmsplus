<?php

use yii\helpers\Html;

/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */

?>

<h2><?= $category->name ?></h2>
<div class="tabs-container row">
  <ul class="headers col-2">
    <?php foreach($data as $page): ?>
      <li><a href="#"><?= $page->name ?></a></li>
    <?php endforeach ?>
  </ul>
  <div class="tabs col-10">
    <?php foreach($data as $page): ?>
      <?php $images = unserialize($page->images); ?>
      <div class="tab">
        <?= Html::img($images[0])?>
      </div>
    <?php endforeach ?>
  </div>
</div>
