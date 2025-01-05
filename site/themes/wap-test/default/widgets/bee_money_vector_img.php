<?php
/** @var $data \mcms\pages\models\Page[] */


use yii\helpers\Html;
//Yii::debug($this->context);
$images = $data[0]->getPropByCode('bee_money_vector');
$image = $images->getImageUrl();
echo $image;







