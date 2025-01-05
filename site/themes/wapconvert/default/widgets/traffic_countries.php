<?php
/**
 * @var \mcms\pages\models\Page[] $data
 * @var \mcms\pages\models\Category $category
 * @var \mcms\pages\Module $pagesModule
 */

use yii\helpers\Html;

?>
<div class="traffic-slider js-traffic-slider anim anim-fade-down">
  <?php foreach ($data as $page): ?>
    <div class="slide">
      <p><?= $page->name ?></p>
      <div class="flag"><img class="icon" src="<?= $page->getPropByCode('image')->getImageUrl() ?>"></div>
    </div>
  <?php endforeach ?>
</div>