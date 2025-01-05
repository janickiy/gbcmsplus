<?php
use yii\helpers\Html;
use mcms\common\helpers\ArrayHelper;

/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */
$module = Yii::$app->getModule('pages');
?>

<div class="gallery row">
  <?php foreach($data as $page): ?>
    <?php $images = unserialize($page->images); ?>
    <div class="gallery-item col-xs-4">
    <div class="overlay-container">
      <img src="<?= $images[0] ?>" alt="">
      <a href="<?= ArrayHelper::getValue($page->getPropByCode('blogger_review_url'), 'multilang_value', '#') ?>" class="overlay small">
        <i class="fa fa-link"></i>
      </a>
    </div>
  </div>
  <?php endforeach ?>
</div>