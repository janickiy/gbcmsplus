<?php

namespace mcms\promo\components\widgets;

use cakebake\bootstrap\select\BootstrapSelectAsset;
use mcms\common\helpers\ArrayHelper;
use mcms\common\helpers\Html;
use mcms\common\widget\Select2;
use mcms\promo\models\Country;
use yii\base\Widget;
use yii\bootstrap\BootstrapPluginAsset;

class CountriesDropdown extends Widget {

  public $onlyActiveCountries;
  public $model;
  public $theme = Select2::THEME_SMARTADMIN;
  public $attribute;
  public $items = [];
  public $options = [];
  public $useSelect2 = false;
  public $pluginOptions = [];
  public $pluginEvents = [];

  const CSS_SELECTOR = '.countries-selectpicker';

  /** @var array Массив, среди каких стран выбрать. */
  public $countriesId = [];

  /** @var array Массив, среди какие операторы показать */
  public $operatorsId = [];

  public function init() {
    $this->items = Country::getDropdownItems($this->onlyActiveCountries ? Country::STATUS_ACTIVE : null);

    $defaultOptions = [
      'class' => 'form-control selectpicker countries-selectpicker',
      'data-width' => '100%',
      'data-live-search' => 'true',
      'style' => 'width:100%'
    ];

    BootstrapPluginAsset::register($this->view);

    $this->options = ArrayHelper::merge($defaultOptions, $this->options);
  }

  public function run()
  {
    if ($this->useSelect2) {
      return Select2::widget([
        'model' => $this->model,
        'attribute' => $this->attribute,
        'theme' => $this->theme,
        'data' => $this->items,
        'options' => $this->options,
        'pluginOptions' => $this->pluginOptions,
        'pluginEvents' => $this->pluginEvents,
      ]);
    }
    BootstrapSelectAsset::register($this->view, [
      'selector' => self::CSS_SELECTOR
    ]);
    return Html::activeDropDownList($this->model, $this->attribute, $this->items, $this->options);
  }

}