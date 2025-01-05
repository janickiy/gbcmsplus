<?php

use yii\helpers\Html;

/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */

?>


<div class="countries col-9">
  <h3><?= $category->name ?></h3>
  <div id="countries-slider">
    <?php foreach($data as $k=>$page): ?>

      <?php if(!($k%2)):?>
        <div class="slide">
      <?php endif;?>

      <?php $images = unserialize($page->images); ?>
      <div class="country">
        <?= Html::img($images[0])?>
        <p><?= $page->name ?></p>
      </div>

      <?php if($k%2):?>
        </div>
      <?php endif;?>

    <?php endforeach ?>
    <?php if(!($k%2)):?>
      </div>
    <?php endif;?>
  </div>
</div>