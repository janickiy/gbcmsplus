<?php
/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */

$images = $data[0]->getPropByCode('logo');

$image = $images->getImageUrl();
?>

<a href="/" class="logo">
  <?= \yii\helpers\Html::img($image, ['alt' => $images->categoryProp->name])?>
</a>

