<?php
namespace mcms\promo\components\events;

use mcms\common\event\Event;
use Yii;
use mcms\promo\models\Country;

class CountryUpdated extends Event
{

  public $country;

  public function __construct(Country $country = null)
  {
    $this->country = $country;
  }

  public function getModelId()
  {
    return $this->country->id;
  }

  function getEventName()
  {
    return Yii::_t('promo.events.country_updated');
  }
}