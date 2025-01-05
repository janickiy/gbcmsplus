<?php
namespace mcms\promo\components\events;

use mcms\common\event\Event;
use Yii;
use mcms\promo\models\LandingCategory;

class LandingCategoryUpdated extends Event
{

  public $landingCategory;

  public function __construct(LandingCategory $landingCategory = null)
  {
    $this->landingCategory = $landingCategory;
  }

  public function getModelId()
  {
    return $this->landingCategory->id;
  }

  function getEventName()
  {
    return Yii::_t('promo.events.landing_category_updated');
  }
}