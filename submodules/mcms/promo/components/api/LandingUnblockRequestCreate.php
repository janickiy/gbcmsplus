<?php

namespace mcms\promo\components\api;

use mcms\common\helpers\ArrayHelper;
use mcms\common\module\api\ApiResult;
use mcms\promo\models\LandingUnblockRequest;

class LandingUnblockRequestCreate extends ApiResult
{
  protected $postData;
  protected $userId;


  public function init($params = [])
  {
    $this->postData = ArrayHelper::getValue($params, 'postData');
    $this->userId = ArrayHelper::getValue($params, 'userId');
  }

  public function getResult()
  {
    if (LandingUnblockRequest::isCreated(ArrayHelper::getValue($this->postData, 'landing_id')))
      return ['success' => true];

    $landingUnblockRequest = new LandingUnblockRequest([
      'status' => LandingUnblockRequest::STATUS_MODERATION,
    ]);

    $landingUnblockRequest->setScenario(LandingUnblockRequest::SCENARIO_PARTNER_CREATE);
    $landingUnblockRequest->user_id = $this->userId;
    $landingUnblockRequest->landing_id = ArrayHelper::getValue($this->postData, 'landing_id');
    $landingUnblockRequest->traffic_type = ArrayHelper::getValue($this->postData, 'traffic_type');
    $landingUnblockRequest->description = ArrayHelper::getValue($this->postData, 'description');
    $landingUnblockRequest->reject_reason = ArrayHelper::getValue($this->postData, 'reject_reason');

    return [
      'success' => $landingUnblockRequest->save(),
      'errors' => $landingUnblockRequest->getErrors(),
      'status' => LandingUnblockRequest::getStatuses(LandingUnblockRequest::STATUS_MODERATION)
    ];

  }
}