<?php
/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */

$images = $data[0]->getPropByCode('footer_logo_image');

$image = $images->getImageUrl();
?>

<div class="logo-footer">
  <?= \yii\helpers\Html::img($image, ['id' => 'logo-footer']) ?>
</div>

