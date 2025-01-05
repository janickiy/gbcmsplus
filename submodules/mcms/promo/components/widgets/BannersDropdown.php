<?php

namespace mcms\promo\components\widgets;

use mcms\common\widget\Select2;
use mcms\common\helpers\ArrayHelper;
use mcms\common\helpers\Html;
use mcms\promo\models\Banner;
use yii\base\Widget;
use cakebake\bootstrap\select\BootstrapSelectAsset;
use Yii;
use yii\bootstrap\BootstrapPluginAsset;
use yii\caching\TagDependency;

class BannersDropdown extends Widget {

  public $model;
  public $theme = Select2::THEME_SMARTADMIN;
  public $attribute;
  public $items = [];
  public $options = [];
  public $useSelect2 = false;
  public $pluginOptions = [];
  public $pluginEvents = [];

  const CSS_SELECTOR = '.banners-selectpicker';

  /** @var array Массив, среди каких шаблонов выбрать. */
  public $templatesId = [];

  /** @var array Массив, какие баннеры показать */
  public $bannersId = [];

  public function init() {
    $this->items = Banner::getBannersDropDown();

    $defaultOptions = [
      'class' => 'form-control selectpicker banners-selectpicker',
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