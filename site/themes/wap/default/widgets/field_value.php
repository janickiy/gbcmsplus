<?php
/**
 * Вывод свойства элемента.
 * Например name
 */
use mcms\common\helpers\ArrayHelper;
/** @var $this \mcms\common\web\View */
/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */

$propCode = ArrayHelper::getValue($this->context->options, 'fieldCode');

if ($propCode) {
  echo ArrayHelper::getValue($data,[key($data),$propCode]);
}