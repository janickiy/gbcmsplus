<?php
namespace mcms\promo\components\events;

use mcms\common\event\Event;
use mcms\common\helpers\ArrayHelper;
use Yii;
use mcms\promo\models\Landing;

class LandingCreated extends Event
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
    return Yii::_t('promo.events.landing_created');
  }

  public static function getUrl($id = null)
  {
    return ['/partners/links/index'];
  }

  public function getReplacements()
  {
    $operators = [];
    foreach($this->landing->operator as $operator) $operators[] = $operator->name . ' (' . $operator->country->code . ')';
    $nameWithOperators = $this->landing->name . ' - ' . implode(', ', $operators);

    return ArrayHelper::merge(parent::getReplacements(), [
      '{landing.name}' => $nameWithOperators,
    ]);
  }
}