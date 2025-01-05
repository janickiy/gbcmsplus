<?php

namespace admin\widgets\onoffswitch;

use mcms\common\helpers\Html;
use yii\base\Widget;

/**
 * Красивый свитчер из смартадмин
 * @property string $id
 */
class SwitchWidget extends Widget
{
  const DEFAULT_ON_LABEL = 'ON';
  const DEFAULT_OFF_LABEL = 'OFF';

  public $label;
  public $onLabel;
  public $offLabel;

  public $options = [];

  public $model;
  public $attribute;
  public $value;

  /**
   * @inheritdoc
   */
  public function run()
  {
    $this->onLabel = $this->onLabel ?: self::DEFAULT_ON_LABEL;
    $this->offLabel = $this->offLabel ?: self::DEFAULT_OFF_LABEL;

    if (empty($this->options['id']) && empty($this->model)) {
      $this->options['id'] = Html::getUniqueId();
    }

    if (empty($this->options['id']) && !empty($this->model)) {
      $this->options['id'] = Html::getInputId($this->model, $this->attribute);
    }

    return $this->render('switch', [
      'label' => $this->label,
      'options' => $this->options,
      'model' => $this->model,
      'attribute' => $this->attribute,
      'onLabel' => $this->onLabel,
      'offLabel' => $this->offLabel,
      'value' => $this->value,
    ]);
  }
}
