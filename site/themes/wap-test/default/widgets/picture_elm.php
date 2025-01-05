<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/** @var $data \mcms\pages\models\Page[] */

$vectorName = ArrayHelper::getValue($this->context->options, 'vector');
$rasterName = ArrayHelper::getValue($this->context->options, 'raster');
$vector = $data[0]->getPropByCode($vectorName)->getImageUrl();
$raster = $data[0]->getPropByCode($rasterName)->getImageUrl();
//Yii::debug($this->context);
?>
<picture>
  <?=Html::tag('source', null, ["srcset" => $vector, 'type' => 'image/webp']); ?>
  
  <?=Html::tag("source", null, ["srcset" => $raster, 'type' => 'image/png']); ?>
  
  <?=Html::img($raster,ArrayHelper::getValue($this->context->options, 'imageOptions')); ?>
</picture>