<?php

namespace mcms\promo\components\api;

use mcms\common\module\api\ApiResult;
use mcms\common\helpers\ArrayHelper;
use mcms\promo\models\Landing;
use mcms\promo\models\LandingUnblockRequest;

class GetLandingUnblockRequest extends ApiResult
{
  public $landingId;

  public function init($params = [])
  {
    $this->landingId = ArrayHelper::getValue($params, 'landing_id');
  }

  public function getResult()
  {
    return LandingUnblockRequest::findByLanding($this->landingId);
  }
}


