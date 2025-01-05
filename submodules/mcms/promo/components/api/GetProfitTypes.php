<?php

namespace mcms\promo\components\api;

use mcms\common\module\api\ApiResult;
use mcms\common\helpers\ArrayHelper;
use mcms\promo\models\SourceOperatorLanding;

class GetProfitTypes extends ApiResult
{
  public $type;

  public function init($params = [])
  {
    $this->type = ArrayHelper::getValue($params, 'type');

  }

  public function getResult()
  {
    switch ($this->type) {
      case 'rebill':
        return SourceOperatorLanding::PROFIT_TYPE_REBILL;
        break;
      case 'buyout':
        return SourceOperatorLanding::PROFIT_TYPE_BUYOUT;
        break;
    }

    return SourceOperatorLanding::getProfitTypes();
  }

}


