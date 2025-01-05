<?php

namespace mcms\promo\components\api;

use mcms\common\module\api\ApiResult;
use mcms\common\helpers\ArrayHelper;
use mcms\promo\models\Domain;

class GetDomainTypes extends ApiResult
{
  public $type;

  public function init($params = [])
  {
    $this->type = ArrayHelper::getValue($params, 'type');
  }

  public function getResult()
  {
    if ($this->type == 'normal') {
      return Domain::TYPE_NORMAL;
    }
    if ($this->type == 'parked') {
      return Domain::TYPE_PARKED;
    }

    return null;
  }
}

