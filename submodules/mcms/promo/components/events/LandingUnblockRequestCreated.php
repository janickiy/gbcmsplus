<?php

namespace mcms\promo\components\events;

use mcms\common\event\Event;
use mcms\promo\models\LandingUnblockRequest;
use Yii;

/**
 * Class LandingUnblockRequestCreated
 * @package mcms\promo\components\events
 */
class LandingUnblockRequestCreated extends Event
{

  public $landingUnblockRequest;

  /**
   * @param LandingUnblockRequest $landingUnblockRequest
   */
  public function __construct(LandingUnblockRequest $landingUnblockRequest = null)
  {
    $this->landingUnblockRequest = $landingUnblockRequest;
  }

  public function getModelId()
  {
    return $this->landingUnblockRequest->id;
  }

  public static function getUrl($id = null)
  {
    return ['/promo/landing-unblock-requests/index/'];
  }

  public function incrementBadgeCounter()
  {
    return $this->landingUnblockRequest ? $this->landingUnblockRequest->isStatusModeration() : null;
  }

  /**
   * @return string
   */
  function getEventName()
  {
    return Yii::_t('promo.events.landing_unblock_request_created');
  }
}