<?php

namespace mcms\promo\components\api;

use mcms\common\module\api\ApiResult;
use mcms\promo\models\search\DomainSearch;
use mcms\promo\models\Domain;

class DomainList extends ApiResult
{

  public function init($params = [])
  {
    $this->setResultTypeMap();
    $this->setMapParams(['id', 'url']);
    $this->prepareDataProvider(new DomainSearch(), $params);
  }

  public function getIsSystemKeySystem()
  {
    return Domain::IS_SYSTEM_KEY_SYSTEM;
  }

  public function getIsSystemKeyParked()
  {
    return Domain::IS_SYSTEM_KEY_PARKED;
  }

}
