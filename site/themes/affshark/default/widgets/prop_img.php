<?php
/**
 * @var $data \mcms\pages\models\Page[]
 * @var $category \mcms\pages\models\Category
 * @var \mcms\pages\Module $pagesModule
 */

use mcms\common\helpers\ArrayHelper;

$propCode = ArrayHelper::getValue($this->context->options, 'propCode');

$images = $data[0]->getPropByCode($propCode);
$image = $images->getImageUrl();

echo $image;
?>