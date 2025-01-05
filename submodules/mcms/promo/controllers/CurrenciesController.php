<?php

namespace mcms\promo\controllers;

use mcms\common\controller\AdminBaseController;
use mcms\common\web\AjaxResponse;
use mcms\promo\components\widgets\MainCurrenciesWidget;

/**
 * TODO: перенести в модуль currency
 */
class CurrenciesController extends AdminBaseController
{
  /**
   * Устанавливает куку для виджета MainCurrenciesWidget
   * @param $currencyCode
   * @return mixed
   */
  public function actionUserMainCurrencyChanged($currencyCode)
  {
    MainCurrenciesWidget::setSelectedCurrency($currencyCode);
    return AjaxResponse::success();
  }
}
