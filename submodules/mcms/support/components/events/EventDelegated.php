<?php

namespace mcms\support\components\events;

use mcms\common\event\Event;
use mcms\support\models\Support;
use mcms\user\models\User;
use Yii;

class EventDelegated extends Event
{
  public $ticket;
  public $delegatedTo;

  /**
   * EventDelegated constructor.
   * @param $ticket
   * @param $delegatedTo
   */
  public function __construct(Support $ticket = null, User $delegatedTo = null)
  {
    $this->ticket = $ticket;
    $this->delegatedTo = $delegatedTo;
  }

  function getEventName()
  {
    return Yii::_t('support.events.delegated');
  }
}