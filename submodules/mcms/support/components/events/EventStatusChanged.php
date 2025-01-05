<?php

namespace mcms\support\components\events;

use mcms\common\event\Event;
use mcms\support\models\Support;
use Yii;

class EventStatusChanged extends Event
{
  public $ticket;

  /**
   * EventStatusChanged constructor.
   * @param Support $ticket
   */
  public function __construct(Support $ticket)
  {
    $this->ticket = $ticket;
  }

  public function getModelId()
  {
    return $this->ticket ? $this->ticket->id : null;
  }

  function getEventName()
  {
    return Yii::_t('support.events.status_changed');
  }


}