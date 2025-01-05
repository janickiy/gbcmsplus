<?php

namespace mcms\common\multilang\widgets\input;

use mcms\common\helpers\ArrayHelper;
use mcms\common\helpers\Html;
use mcms\common\multilang\LangAttribute;
use yii\bootstrap\ButtonDropdown;
use yii\bootstrap\InputWidget as YiiInput;
use yii\widgets\ActiveForm;

class InputWidget extends YiiInput
{

  /** @var  ActiveForm */
  public $form;
  public $options = ['class' => 'form-control'];
  public $prepend;

  public function init()
  {
    $this->setId(uniqid());

    parent::init();
  }


  public function run()
  {
    echo Html::beginTag('div', ['class' => 'input-group multilang-input', 'id' => $this->id]);

    if ($this->prepend) {
      echo $this->prepend;
    }


    foreach($this->getLanguages() as $lang) {
      $id = $this->options['id'] . '-' . $lang;

      echo Html::beginTag('div', [
          'data-lang' => $lang,
          'class' => $lang == $this->getLanguages()[0] ? 'field-' . $id : 'hidden field-' . $id
        ]);

      echo Html::input(
        'text',
        Html::getInputName($this->model, $this->attribute) . '[' . $lang . ']',
        $this->getValueByLang($lang),
        ArrayHelper::merge($this->options, [
          'class' => 'form-control',
          'id' => $id,
          'style' => 'float: none;',
        ])
      );

      echo Html::tag('div', null, ['class' => 'help-block']);
      echo Html::endTag('div');

      $this->addFormValidateAttr($id);

    }

    $this->renderDropDown();

    echo Html::endTag('div');

    $this->registerAsset();
  }

  private function getLanguages()
  {
    return \Yii::$app->params['languages'];
  }

  private function renderDropDown()
  {

    $langsNames = $this->getLanguages();

    $langs = []; foreach($langsNames as $langName) {
      $langs[] = ['label' => strtoupper($langName), 'url' => 'javascript:void(0)', 'linkOptions' => ['data-lang' => $langName]];
    }

    $options = [
      'options' => ['class' => 'btn btn-default dropdown-toggle'],
      'id' => uniqid(),
      'label' => strtoupper($langsNames[0]),
      'dropdown' => [
        'items' => $langs,
        'id' => uniqid(),
        'options' => [
          'class' => 'dropdown-menu-right'
        ]
      ],
    ];

    $dropdownOptions = ArrayHelper::getValue($this->options, 'dropdownOptions', []);
    $dropdownOptions = ArrayHelper::merge($dropdownOptions, $options);

    echo Html::tag(
      'div',
      ButtonDropdown::widget($dropdownOptions),
      ['class' => 'input-group-btn', 'style' => 'vertical-align: top; z-index: 3;']
    );
  }

  private function registerAsset()
  {
    $view = $this->getView();
    InputWidgetAsset::register($view);
  }

  private function addFormValidateAttr($id)
  {
    $this->form->attributes[] = [
      "id" => $id,
      "name" => "name",
      "container" => ".field-" . $id,
      "input" => "#" . $id,
      "enableAjaxValidation" => $this->form->enableAjaxValidation
    ];
  }

  private function getValueByLang($lang)
  {
    $attrName = Html::getAttributeName($this->attribute);
    $attribute = $this->model->{$attrName};
    if ($attribute instanceof LangAttribute) {
      return $attribute->getLangValue($lang);
    }
    return $attribute;
  }
}