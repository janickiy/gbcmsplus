<?php
namespace mcms\promo\components\events;

use mcms\common\event\Event;
use Yii;
use mcms\promo\models\TrafficbackProvider;

class TrafficbackProviderUpdated extends Event
{

  public $trafficbackProvider;

  public function __construct(TrafficbackProvider $trafficbackProvider = null)
  {
    $this->trafficbackProvider = $trafficbackProvider;
  }

  public function getModelId()
  {
    return $this->trafficbackProvider->id;
  }

  function getEventName()
  {
    return Yii::_t('promo.events.trafficback_provider_updated');
  }
}