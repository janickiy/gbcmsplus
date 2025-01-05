<?php
/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */

$imageOptions = \yii\helpers\ArrayHelper::getValue($this->context->options, 'imageOptions');
$images = $data[0]->getPropByCode('logo_light');

$image = $images->getImageUrl();
?>
 <?= \yii\helpers\Html::img($image,$imageOptions)?>


