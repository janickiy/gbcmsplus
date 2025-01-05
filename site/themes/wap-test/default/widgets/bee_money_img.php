<?php
/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */

$images = $data[0]->getPropByCode('bee_money_raster');
$image = $images->getImageUrl();
echo $image;




