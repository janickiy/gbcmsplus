<?php

/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */
$module = Yii::$app->getModule('pages');
?>

<div class="wiki_rotate">
  <?php foreach($data as $page): ?>
  <div class="wiki_rotate-item">
    <span><?= $page->text ?></span>
  </div>
  <?php endforeach ?>
  <canvas id="cycle"></canvas>
</div>