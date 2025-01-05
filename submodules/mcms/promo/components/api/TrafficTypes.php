<?php


namespace mcms\promo\components\api;


use mcms\common\module\api\ApiResult;
use mcms\promo\models\search\TrafficTypeSearch;

class TrafficTypes extends ApiResult
{

  public function init($params = [])
  {
    $this->prepareDataProvider(new TrafficTypeSearch(), $params);
  }
}