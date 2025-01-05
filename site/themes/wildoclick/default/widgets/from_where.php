<?php

use yii\helpers\Html;

/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */

?>
<div class="sources col-3">
  <h3><?= $category->name ?></h3>
  <div class="row">
    <?php foreach($data as $page): ?>
      <div class="col-6">
          <?php $images = unserialize($page->images); ?>
          <?= Html::img($images[0])?>
          <p><?= $page->name ?></p>
      </div>
    <?php endforeach ?>
  </div>
</div>
