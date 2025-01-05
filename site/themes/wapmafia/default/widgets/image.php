<?php
/**
 * @var \mcms\pages\models\Page[] $data
 * @var \mcms\pages\models\Category $category
 * @var \mcms\pages\Module $pagesModule
 */

use mcms\common\helpers\ArrayHelper;
use yii\helpers\Html;

$propCode = ArrayHelper::getValue($this->context->options, 'propCode');
$cssClass = ArrayHelper::getValue($this->context->options, 'cssClass', '');

if ($propCode) {
    /** @var \mcms\pages\models\PageProp $image */
    $image = $data[0]->getPropByCode(ArrayHelper::getValue($this->context->options, 'propCode'));
    echo Html::img($image->getImageUrl(), ['class' => $cssClass]);
}