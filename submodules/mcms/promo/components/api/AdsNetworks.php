<?php


namespace mcms\promo\components\api;


use mcms\common\module\api\ApiResult;
use mcms\promo\models\search\AdsNetworkSearch;

class AdsNetworks extends ApiResult
{

  public function init($params = [])
  {
    $this->prepareDataProvider(new AdsNetworkSearch(), $params);
  }
}