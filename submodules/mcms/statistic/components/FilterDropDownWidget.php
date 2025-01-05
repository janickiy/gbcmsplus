<?php

namespace mcms\statistic\components;

use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;

class FilterDropDownWidget extends Widget
{

  public $model;
  public $attribute;
  public $view;

  public $items;
  public $options;

  public function init() {
    $this->options = ArrayHelper::merge($this->options, [
      'class' => 'selectpicker',
      'data-width' => '100%',
      'multiple' => true,
      'title' => $this->model->getAttributeLabel($this->attribute),
      'data-selected-text-format' => 'count>0',
      'data-count-selected-text' => $this->model->getAttributeLabel($this->attribute). ' ({0}/{1})',
      'data-live-search' => ArrayHelper::getValue($this->options, 'data-live-search') ? ArrayHelper::getValue($this->options, 'data-live-search') : 'true',
      'style' => 'width:100%'
    ]);
  }

  public function run()
  {
    return Html::activeDropDownList($this->model, $this->attribute, $this->items, $this->options);
  }

}
