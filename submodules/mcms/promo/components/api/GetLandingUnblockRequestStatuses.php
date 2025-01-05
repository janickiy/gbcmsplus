<?php

namespace mcms\promo\components\api;

use mcms\common\module\api\ApiResult;
use mcms\common\helpers\ArrayHelper;
use mcms\promo\models\Landing;
use mcms\promo\models\LandingUnblockRequest;

class GetLandingUnblockRequestStatuses extends ApiResult
{
  public $status;

  public function init($params = [])
  {
    $this->status = ArrayHelper::getValue($params, 'status');

  }

  public function getResult()
  {
    switch ($this->status) {
      case 'moderation':
        return LandingUnblockRequest::STATUS_MODERATION;
        break;
      case 'unlocked':
        return LandingUnblockRequest::STATUS_UNLOCKED;
        break;
    }

    return LandingUnblockRequest::getStatuses();
  }

}


