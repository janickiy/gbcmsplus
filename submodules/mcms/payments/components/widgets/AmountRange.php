<?php

namespace mcms\payments\components\widgets;

use kartik\field\FieldRange;
use mcms\common\helpers\Html;
use mcms\common\helpers\ArrayHelper;

class AmountRange extends FieldRange {

  public function init()
  {
    $this->separator = '-';
    $this->label= '';
    $this->template = '{widget}{customError}';
    parent::init();
  }

  /**
   * @inheritDoc
   */
  public function run()
  {
    if ($this->model) {
      $errorMessage = '';
      if ($this->model->hasErrors($this->attribute1) || $this->model->hasErrors($this->attribute2)) {
        $this->container['class'][] = 'has-error';
        $errorMessage = !empty($this->model->hasErrors($this->attribute1))
          ? ArrayHelper::getValue($this->model->getErrors($this->attribute1), 0)
          : ArrayHelper::getValue($this->model->getErrors($this->attribute2), 0);
      }

      $errorInnerDiv = Html::tag('div', $errorMessage, ['class' => '']);
      $errorOuterDiv = Html::tag('div', $errorInnerDiv, $this->errorContainer);
      $this->template = strtr($this->template, [
        '{customError}' => $errorOuterDiv
      ]);
    }

    parent::run();
  }

  protected function renderWidget()
  {
    Html::addCssClass($this->options, 'kv-field-range');
    if ($this->type === self::INPUT_DATE) {
      $widget = $this->getDatePicker();
    } else {
      Html::addCssClass($this->options, 'input-group');
      $widget = isset($this->form) ? $this->getFormInput() : $this->getInput(1) .
        '<span class="input-group-addon kv-field-separator">' . $this->separator . '</span>' .
        $this->getInput(2);
      $widget = Html::tag('div', $widget, $this->options);
    }
    $widget = Html::tag('div', $widget, $this->widgetContainer);
    $error = Html::tag('div', '<div class="help-block"></div>', $this->errorContainer);

    echo Html::tag('div', strtr($this->template, [
      '{label}' => Html::label($this->label, null, $this->labelOptions),
      '{widget}' => $widget,
      '{error}' => $error
    ]), $this->container);
  }

}