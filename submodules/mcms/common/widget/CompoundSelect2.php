<?php

namespace mcms\common\widget;

use mcms\common\widget\Select2;
use mcms\statistic\Module;
use yii\helpers\ArrayHelper;

/**
 * Виджет Select2
 * Использует для поиска остальные CompoundSelect2 виджеты
 * Class CompoundSelect2
 * @package mcms\common\widget
 */
class CompoundSelect2 extends Select2
{
  /**
   * Класс для поиска остальных CompoundSelect2
   * @var string
   */
  public $compoundSelectClassName = 'compound-select-widget';
  public $format = '#:id: - :name';

  /**
   * @var string
   */
  public $placeholder = '';

  /**
   * Атрибут, который будет передан хендлеру, который должен учавствовать в фильтрации
   * @var
   */
  public $attributeName;
  public $title;
  /**
   * Урл обработчика поиска
   * @var string
   */
  public $handlerUrl;

  private $statFilter;

  /**
   * @var mixed
   */
  public $initValue;

  public function __construct($config = [])
  {
    $this->statFilter = Module::getInstance();
    $this->attributeName = ArrayHelper::getValue($config, 'attributeName', $this->attributeName);
    $this->handlerUrl = ArrayHelper::getValue($config, 'handlerUrl');
    $this->title = ArrayHelper::getValue($config, 'placeholder');
    $this->compoundSelectClassName = ArrayHelper::getValue($config, 'compoundSelectClassName', $this->compoundSelectClassName);
    $this->format = ArrayHelper::getValue($config, 'format', '#:id - :name');

    $this->initValue = ArrayHelper::getValue($config, 'initValue');

    $config = [
      'data' => $this->initValue,
//      'readonly' => true,
      'options' => [
        'title' => $this->title,
        'prompt' => null,
        'multiple' => true,
        'placeholder' => $this->title,
        'class' => $this->compoundSelectClassName,
        'data-attribute-name' => $this->attributeName,
      ],
      'pluginOptions' => [
        'allowClear' => true,
        'ajax' => [
          'url' => $this->handlerUrl,
          'dataType' => 'json',
          'data' => new \yii\web\JsExpression($this->ajaxData())
        ]
      ],
      'model' => $config['model'],
      'attribute' => $config['attribute'],
    ];
    parent::__construct($config);
  }

  private function initialData()
  {

  }

  /**
   * Должен собрать данные остальных CompoundSelect2 и отправить вместе с текущим запросом
   * @return string
   */
  private function ajaxData()
  {
    $template = '
      function(params) {
        var ajaxData = {
          attributeName: ":attributeName",
          compound: {},
          format: ":format"
        };
        $(\':compoundSelectClassName\').each(function() {
          var attributeName = $(this).data(\'attributeName\')
            , attributeValue = $(this).val()
            ;

          ajaxData.compound[attributeName] = attributeValue;
        });
        return {
          q: params.term,
          data: ajaxData
        };
      }
    ';

    return strtr($template, [
      ':compoundSelectClassName' => $this->compoundSelectClassName,
      ':attributeName' => $this->attributeName,
      ':format' => $this->format,
    ]);
  }
}