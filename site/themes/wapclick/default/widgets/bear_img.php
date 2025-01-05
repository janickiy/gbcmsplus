<?php
/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */

$images = $data[0]->getPropByCode('bear_img');

$image = $images->getImageUrl();

echo \yii\helpers\Html::img($image);




