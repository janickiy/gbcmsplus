<?php

namespace mcms\promo\components\api;

use mcms\common\module\api\ApiResult;
use mcms\common\helpers\ArrayHelper;
use mcms\promo\models\Source as SourceModel;

class GetTrafficbackTypes extends ApiResult
{

  public $type;

  public function init($params = [])
  {
    $this->type = ArrayHelper::getValue($params, 'type');

  }

  public function getResult()
  {
    switch ($this->type) {
      case 'static':
        return SourceModel::TRAFFICBACK_TYPE_STATIC;
        break;
      case 'dynamic':
        return SourceModel::TRAFFICBACK_TYPE_DYNAMIC;
        break;
    }

    return SourceModel::getTrafficbackTypes();
  }

}


