<?php
/**
 * Вывод свойства элемента.
 * Например name
 */
use mcms\common\helpers\ArrayHelper;
/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */

$propCode = ArrayHelper::getValue($this->context->options, 'fieldCode');

if ($propCode) {
  echo $data[0]->{$propCode};
}