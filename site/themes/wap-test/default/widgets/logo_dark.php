<?php
/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */

$images = $data[0]->getPropByCode('logo_dark');

$image = $images->getImageUrl();
?>
 <?= \yii\helpers\Html::img($image,['class' => 'header__logo','alt' => "Логотип Wapclick"])?>


