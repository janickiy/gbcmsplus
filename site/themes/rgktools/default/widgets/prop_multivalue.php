<?php
/**
 * @var \mcms\pages\models\Page[] $data
 * @var \mcms\pages\models\Category $category
 * @var \mcms\pages\Module $pagesModule
 */

use mcms\common\helpers\ArrayHelper;

$propCode = ArrayHelper::getValue($this->context->options, 'propCode');

if ($propCode) {
  $prop = $data[0]->getPropByCode($propCode);
  echo $prop ? $prop->multilang_value : '';
}