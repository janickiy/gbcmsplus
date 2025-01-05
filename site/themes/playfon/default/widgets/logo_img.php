<?php
/**
 * @var $data \mcms\pages\models\Page[]
 * @var $category \mcms\pages\models\Category
 * @var \mcms\pages\Module $pagesModule
 */

use yii\helpers\Html;

$images = $data[0]->getPropByCode('logo');
if (!$images) {
  return;
}


$image = $images->getImageUrl();

echo Html::a(Html::img($image,['class' => 'img-responsive','style'=>'display: none;']), '#', ['class' => 'navbar-brand logo'])
?>