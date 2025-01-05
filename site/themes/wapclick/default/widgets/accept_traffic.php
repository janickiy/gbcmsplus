<?php

/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */
$module = Yii::$app->getModule('pages');
?>

<ul>
  <?php foreach($data as $page): ?>
    <?php $images = unserialize($page->images); ?>
  <li><img src="<?= $images[0] ?>" alt=""><?= $page->name ?></li>
  <?php endforeach ?>
</ul>