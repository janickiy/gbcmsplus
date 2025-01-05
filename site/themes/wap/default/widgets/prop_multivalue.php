<?php
/**
 * Вывод пользовательского свойства элемента
 */
use mcms\common\helpers\ArrayHelper;
/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */

$propCode = ArrayHelper::getValue($this->context->options, 'propCode');

if ($propCode) {
  $prop = $data[0]->getPropByCode($propCode);
  echo $prop ? $prop->multilang_value : '';
}
?>
