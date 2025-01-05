<?php
use yii\helpers\Html;

/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */
$module = Yii::$app->getModule('pages');
?>

<div class="partners-cont uk-flex uk-flex-right uk-flex-middle">
  <div class="partners">
    <?php foreach ($data as $page): ?>
      <?php $images = unserialize($page->images); ?>
      <i class="partners__item sp-icons sp-icons__<?= $page->code ?>" style="background-image: url(<?= $images[0] ?>); background-position: 0 0;"></i>
    <?php endforeach ?>
  </div>
</div>
