<?php
/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */

$images = $data[0]->getPropByCode('logo');

$image = $images->getImageUrl();
?>

<a href="/" class="navbar-brand">

    <?= \yii\helpers\Html::img($image) ?>

</a>

