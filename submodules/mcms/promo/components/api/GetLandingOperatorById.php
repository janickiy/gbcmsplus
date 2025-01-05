<?php

namespace mcms\promo\components\api;

use mcms\common\module\api\ApiResult;
use mcms\common\helpers\ArrayHelper;
use mcms\promo\models\LandingOperator;

class GetLandingOperatorById extends ApiResult
{
  protected $landingId;
  protected $operatorId;

  public function init($params = [])
  {
    $this->landingId = ArrayHelper::getValue($params, 'landingId');
    $this->operatorId = ArrayHelper::getValue($params, 'operatorId');

    if (!$this->landingId) $this->addError('landingId is not set');
    if (!$this->operatorId) $this->addError('operatorId is not set');
  }

  public function getResult()
  {
    return LandingOperator::findOne([
      'landing_id' => $this->landingId,
      'operator_id' => $this->operatorId,
    ]);
  }

}