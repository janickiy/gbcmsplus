<?php


namespace mcms\promo\components\api;


use mcms\common\module\api\ApiResult;
use mcms\promo\components\widgets\MainCurrenciesWidget AS MainCurrenciesWidgetPromo;

/**
 * Class MainCurrenciesWidget
 * @package mcms\promo\components\api
 */
class MainCurrenciesWidget extends ApiResult
{

  public $params;

  public function init($params = [])
  {
    $this->params = $params;
  }

  public function getWidgetclass
  {
    return MainCurrenciesWidgetPromo::class;
  }

  public function getResult()
  {
    $this->prepareWidget($this->getWidgetclass, $this->params);
    return parent::getResult();
  }

  public function getSelectedCurrency()
  {
    return MainCurrenciesWidgetPromo::getSelectedCurrency();
  }


}