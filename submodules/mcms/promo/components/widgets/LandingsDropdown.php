<?php
namespace mcms\promo\components\widgets;

use mcms\common\widget\Select2;
use mcms\common\helpers\ArrayHelper;
use mcms\common\helpers\Html;
use mcms\promo\models\Landing;
use mcms\promo\models\LandingCategory;
use mcms\promo\Module;
use yii\base\Widget;
use cakebake\bootstrap\select\BootstrapSelectAsset;
use Yii;
use yii\caching\TagDependency;

class LandingsDropdown extends Widget {

  public $model;
  public $attribute;
  public $theme = Select2::THEME_SMARTADMIN;
  public $items = [];
  public $options = [];
  public $useSelect2 = false;
  public $pluginOptions = [];
  public $pluginEvents = [];
  public $landingsId = [];
  public $isActive = true;

  const CSS_SELECTOR = '.landings-selectpicker';

  public function init()
  {
    $this->items = Module::getInstance()
      ->api('getLandingsByCategory', [
        'landingsId' => $this->landingsId,
        'isActive' => $this->isActive,
      ])
      ->getResult();
    $defaultOptions = [
      'class' => 'form-control selectpicker landings-selectpicker',
      'data-width' => '100%',
      'data-live-search' => 'true',
      'style' => 'width:100%'
    ];
    $this->options = ArrayHelper::merge($defaultOptions, $this->options);
  }

  public function run()
  {
    if ($this->useSelect2) {
      return Select2::widget([
        'model' => $this->model,
        'attribute' => $this->attribute,
        'data' => $this->items,
        'theme' => $this->theme,
        'options' => $this->options,
        'pluginOptions' => $this->pluginOptions,
        'pluginEvents' => $this->pluginEvents,
        'showToggleAll' => false,
      ]);
    }
    BootstrapSelectAsset::register($this->view, ['selector' => self::CSS_SELECTOR]);
    return Html::activeDropDownList($this->model, $this->attribute, $this->items, $this->options);
  }
}