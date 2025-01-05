<?php

namespace mcms\promo\components\widgets;

use mcms\common\helpers\ArrayHelper;
use mcms\common\helpers\Html;
use mcms\promo\components\api\MainCurrencies;
use mcms\promo\components\widgets\assets\MainCurrenciesAsset;
use mcms\promo\Module;
use yii\helpers\Url;
use yii\web\Cookie;
use yii\base\Widget;
use yii\bootstrap\BootstrapAsset;
use Yii;

/**
 *
 * Можно навешивать js события на контейнер виджета:
 * $('#idContainer').on('mainCurrencyChanged', function(e, newValue){
 *   alert('NEW CURRENCY ' + newValue);
 * });
 *
 * Class MainCurrenciesWidget
 * @package mcms\promo\components\widgets
 */
class MainCurrenciesWidget extends Widget {

  public $name;
  public $model;
  public $attribute;
  public $items = [];
  public $options = [];
  public $pluginOptions = [];
  public $url;
  public $containerId;

  const TYPE_DROPDOWN = 'dropdown';
  const TYPE_BUTTONS = 'buttons';
  const DEFAULT_TYPE = self::TYPE_DROPDOWN;

  const CSS_CLASS = 'main_currencies-widget';

  const COOKIE_NAME = 'selectedStatisticCurrency';
  const COOKIE_URL_PARAM = 'url-cookie-set';

  private $type;

  private static $userCurrency;
  private static $selectedCurrency;
  private static $urlToChangeCookie;

  public function init() {

    $this->type = ArrayHelper::getValue($this->options, 'type', self::DEFAULT_TYPE);
    $this->containerId = ArrayHelper::getValue($this->options, 'containerId', $this->getId());
    $this->url = ['/' . Module::getInstance()->id . '/currencies/user-main-currency-changed'];

    self::$urlToChangeCookie = Url::to($this->url);

    $this->items = (new MainCurrencies())->setMapParams(['code', 'name'])->setResultTypeMap()->getResult();

    if ($this->type == self::TYPE_DROPDOWN) {
      $defaultOptions = [
        'class' => 'form-control selectpicker ' . self::CSS_CLASS,
        'data' => [
          'width' => '100%',
          self::COOKIE_URL_PARAM => self::$urlToChangeCookie
        ],
        'data-width' => '100%',
        'style' => 'width:100%',
        'id' => $this->containerId
      ];
      $this->options = ArrayHelper::merge($defaultOptions, $this->options);
    }

  }

  public function run()
  {
    if (!Html::hasUrlAccess($this->url)) {
      return null;
    }

    $widget = '';

    if ($this->type == self::TYPE_DROPDOWN) {
      BootstrapAsset::register($this->view, [
        'selector' => '.' . self::CSS_CLASS
      ]);
      $widget .= $this->model
        ? Html::activeDropDownList($this->model, $this->attribute, $this->items, $this->options)
        : Html::dropDownList($this->name, self::getSelectedCurrency(), $this->items, $this->options);
    }

    if ($this->type == self::TYPE_BUTTONS) {
      $widget .= Html::beginTag('div', [
        'class' => 'btn-group ' . self::CSS_CLASS,
        'id' => $this->containerId,
        'data' => array_merge(ArrayHelper::getValue($this->options, 'data', []), [
          self::COOKIE_URL_PARAM => self::$urlToChangeCookie,
        ]),
      ]);
      $selected = self::getSelectedCurrency();
      foreach ($this->items as $currencyCode => $currencyName) {
        $widget .= Html::button($currencyName, [
          'data-currency-code' => $currencyCode,
          'class' => 'btn btn-default btn-xs' . ($selected == $currencyCode ? ' active' : '')
        ]);
      }
      $widget .= Html::endTag('div');
    }
    $view = $this->getView();
    MainCurrenciesAsset::register($view);
    $view->registerJs("jQuery('#$this->containerId').mainCurrenciesWidget();");
    return $widget;
  }

  public static function getSelectedCurrency()
  {
    if (isset(Yii::$app->request->cookies) && self::$selectedCurrency = Yii::$app->request->cookies[self::COOKIE_NAME]) return (string) self::$selectedCurrency;

    self::$selectedCurrency = Module::MAIN_CURRENCY_RUB;

    self::setSelectedCurrency(self::$selectedCurrency);

    return self::$selectedCurrency;
  }

  public static function setSelectedCurrency($currencyCode)
  {
    isset(Yii::$app->response->cookies) && Yii::$app->response->cookies->add(new Cookie([
      'name' => self::COOKIE_NAME,
      'value' => $currencyCode,
      'expire' => strtotime('+1 year')
    ]));
  }

  public static function getUserCurrency()
  {
    if (self::$userCurrency) return self::$userCurrency;

    return self::$userCurrency = Yii::$app->getModule('payments')
      ->api('getUserCurrency', ['userId' => Yii::$app->user->id])
      ->getResult()
    ;
  }

}