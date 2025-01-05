<?php

namespace mcms\promo\components\api;

use mcms\common\module\api\ApiResult;
use mcms\common\helpers\ArrayHelper;
use mcms\promo\models\Landing;

class GetLandingAccessTypes extends ApiResult
{
  public $type;

  public function init($params = [])
  {
    $this->type = ArrayHelper::getValue($params, 'type');
  }

  public function getResult()
  {
    if ($this->type = 'request') {
      return Landing::ACCESS_TYPE_BY_REQUEST;
    }

    return Landing::getAccessTypes();
  }

}


