<?php
namespace mcms\promo\components\events;

use mcms\common\event\Event;
use mcms\promo\models\LandingUnblockRequest;
use mcms\promo\Module;
use Yii;

/**
 * Class LandingUnlocked
 * @package mcms\promo\components\events
 */
class LandingUnlocked extends Event
{

  public $landingUnblockRequest;

  /**
   * @param LandingUnblockRequest $landingUnblockRequest
   */
  public function __construct(LandingUnblockRequest $landingUnblockRequest = null)
  {
    $this->landingUnblockRequest = $landingUnblockRequest;
  }

  /**
   * @return \mcms\user\models\User
   */
  public function getOwner()
  {
    return $this->landingUnblockRequest->user;
  }

  public function getModelId()
  {
    return $this->landingUnblockRequest->id;
  }

  public function trigger()
  {
    parent::trigger();
    Yii::$app->getModule('notifications')->api('setViewedByIdEvent', [
      'event' => LandingUnblockRequest::class,
      'modelId' => $this->getModelId()
    ])->getResult();

    Module::getInstance()->api('badgeCounters')->invalidateCache();
  }
  
  /**
   * @return string
   */
  function getEventName()
  {
    return Yii::_t('promo.events.landing_unlocked');
  }
  
  public static function getUrl($id = null)
  {
    return ['/partners/promo/index/'];
  }
}