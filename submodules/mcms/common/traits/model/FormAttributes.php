<?php

namespace mcms\common\traits\model;

use mcms\common\DynamicActiveRecord;
use kartik\builder\Form;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Этот trait беред активные атрибуты модели, и строит форму
 * Активные атрибуты возвращаются из scenario
 * Свойство formAttributes в модели должно содержать список полей и типы элементы формы
 * пример
 * $this->formAttributes = [
    'email' => ['type' => Form::INPUT_TEXT],
    'username' => ['type' => Form::INPUT_TEXT],
    'status' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => $statuses],
    'password' => ['type' => Form::INPUT_PASSWORD],
  ];
 * Class FormAttributes
 * @package common\components\traits
 */
trait FormAttributes
{
  private function handleFormAttributes(array $activeAttributes, array $formAttributes) {
    if (count($formAttributes)) foreach ($formAttributes as $key => $formAttribute) {
      if (!in_array($key, $activeAttributes)) {
        unset($formAttributes[$key]);
        continue;
      }
      if (ArrayHelper::getValue($formAttribute, 'type') != Form::INPUT_DROPDOWN_LIST) continue;

      $items = ArrayHelper::getValue($formAttribute, 'items', []);
      $items = array_reverse($items, true);
      $items[''] = 'app.common.choose';
      $items = array_reverse($items, true);

      $formAttributes[$key]['items'] = array_map(function($item) {
        return Yii::_t($item);
      }, $items);
    }

    return $formAttributes;
  }

  public function getFormAttributes()
  {
    if (!property_exists($this, 'formAttributes')) return [];
    if (method_exists($this, 'initFormAttributes')) {
      $this->initFormAttributes();
    }
    $formAttributes = $this->handleFormAttributes(
      $this->activeAttributes(),
      $this->formAttributes
    );

    if ($this instanceof DynamicActiveRecord) {
      $additionalFieldsModel = $this->getAdditionalFieldsModel();
      $uses = class_uses($additionalFieldsModel);

      if (isset($uses['mcms\common\traits\model\FormAttributes'])) {
        $formAttributes = array_merge($formAttributes, $additionalFieldsModel->getFormAttributes());
      }
    }

    return $formAttributes;
  }
}