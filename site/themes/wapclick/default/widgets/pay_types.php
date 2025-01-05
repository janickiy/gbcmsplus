<?php

/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */
$module = Yii::$app->getModule('pages');
?>
<ul>
  <?php foreach($data as $page): ?>
  <li><b><?= strtoupper($page->code) ?></b> <?= $page->name ?></li>
  <?php endforeach ?>
</ul>