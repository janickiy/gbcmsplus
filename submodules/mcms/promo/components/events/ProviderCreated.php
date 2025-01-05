<?php
namespace mcms\promo\components\events;

use mcms\common\event\Event;
use Yii;
use mcms\promo\models\Provider;

class ProviderCreated extends Event
{
  public $provider;

  public function __construct(Provider $provider = null)
  {
    $this->provider = $provider;
  }

  public function getModelId()
  {
    return $this->provider->id;
  }

  function getEventName()
  {
    return Yii::_t('promo.events.provider_created');
  }
}