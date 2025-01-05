<?php
/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */

$images = $data[0]->getPropByCode('logo');

$image = $images->getImageUrl();
?>

<?= \yii\helpers\Html::img($image, ['width' => '210', 'class' => 'logo']) ?>

