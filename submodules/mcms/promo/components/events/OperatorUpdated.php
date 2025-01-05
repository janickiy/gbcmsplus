<?php
namespace mcms\promo\components\events;

use mcms\common\event\Event;
use Yii;
use mcms\promo\models\Operator;

class OperatorUpdated extends Event
{

  public $operator;

  public function __construct(Operator $operator = null)
  {
    $this->operator = $operator;
  }

  public function getModelId()
  {
    return $this->operator->id;
  }

  function getEventName()
  {
    return Yii::_t('promo.events.operator_updated');
  }
}