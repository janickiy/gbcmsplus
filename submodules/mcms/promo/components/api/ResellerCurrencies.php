<?php

namespace mcms\promo\components\api;

use mcms\common\module\api\ApiResult;
use yii\data\ArrayDataProvider;
use Yii;

class ResellerCurrencies extends ApiResult
{
  public function init($params = [])
  {
    $module = Yii::$app->getModule('promo');

    $this->setDataProvider(new ArrayDataProvider([
      'allModels' => $module->getResellerCurrencies()
    ]));
  }
}