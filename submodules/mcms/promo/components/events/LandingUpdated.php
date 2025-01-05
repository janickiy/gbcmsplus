<?php
namespace mcms\promo\components\events;

use mcms\common\event\Event;
use Yii;
use mcms\promo\models\Landing;

class LandingUpdated extends Event
{
  public $landing;

  public function __construct(Landing $landing = null)
  {
    $this->landing = $landing;
  }

  public function getModelId()
  {
    return $this->landing->id;
  }

  function getEventName()
  {
    return Yii::_t('promo.events.landing_updated');
  }
}