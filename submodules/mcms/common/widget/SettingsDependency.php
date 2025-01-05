<?php
namespace mcms\common\widget;

use yii\widgets\InputWidget;
use yii\helpers\Html;

/**
 * Class SettingsDependency
 * @package mcms\common\widget
 */
class SettingsDependency extends InputWidget
{
  public $options = ['class' => 'form-control'];
  public $type;
  public $attribute;
  public $dependencyAttribute;
  public $dependencyValue;
  
  public function init()
  {
    parent::init();
  }
  
  /**
   *При изменении поля, которое задает зависитось, скрываем или показываем зависимое поле
   */
  protected function registerClientScript()
  {
    $view = $this->getView();
    $js = <<<JS
$(document).ready(function() {
  var dependencyElement = $('[id*="$this->dependencyAttribute"]');
  var dependencyElementType = dependencyElement.prop('type');
  var value = dependencyElementType === 'checkbox' ? dependencyElement.prop('checked') : dependencyElement.val();
  var dependentElement = $('[id*="$this->attribute"]');
  var dependentContainer = dependentElement.parents('.dependent-container');
  if (value != $this->dependencyValue) {
    dependentContainer.addClass('hide');
  }
  dependencyElement.on('change', function() {
    var value = dependencyElementType === 'checkbox' ? $(this).prop('checked') : $(this).val();
    if (value == $this->dependencyValue) {
      dependentContainer.removeClass('hide');
    } else {
      dependentContainer.addClass('hide');
    }
  });
});
JS;
    $view->registerJs($js);
  }
  
  /**
   * Executes the widget.
   * @return string the result of widget execution to be outputted.
   */
  public function run()
  {
    $this->registerClientScript();
    if ($this->hasModel()) {
      echo Html::activeInput($this->type, $this->model, $this->attribute, $this->options);
    } else {
      echo Html::input($this->type, $this->name, $this->value, $this->options);
    }
  }
}