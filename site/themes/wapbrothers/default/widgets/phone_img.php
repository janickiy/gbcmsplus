<?php
/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */

$images = $data[0]->getPropByCode('phone_image');

$image = $images->getImageUrl();
?>

<div class="phone">
  <div class="grafic">
    <?= \yii\helpers\Html::img($image, ['width' => '240', 'height' => '430px'])?>
  </div>
</div>
