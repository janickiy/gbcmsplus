<?php
/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */

$images = $data[0]->getPropByCode('phone_image');

$image = $images->getImageUrl();
?>

<?= \yii\helpers\Html::img($image, ['class' => 'phone-box__main-img'])?>