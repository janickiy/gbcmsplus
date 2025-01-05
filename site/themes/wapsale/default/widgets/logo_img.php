<?php
/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */

$images = $data[0]->getPropByCode('logo');

$image = $images->getImageUrl();
?>

<div class="logo">
  <a href="/">
  <?= \yii\helpers\Html::img($image, ['id' => 'logo']) ?>
  </a>
</div>

