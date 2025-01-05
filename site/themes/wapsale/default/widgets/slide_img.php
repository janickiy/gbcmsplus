<?php
use mcms\common\helpers\ArrayHelper;

/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */

$propCode = ArrayHelper::getValue($this->context->options, 'propCode');
$images = $data[0]->getPropByCode($propCode);

echo  $images->getImageUrl();
