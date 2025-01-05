<?php

namespace mcms\support\components\events;

use mcms\common\event\Event;
use mcms\support\models\Support;

abstract class BaseEventClosed extends Event
{
  /**
   * @var Support
   */
  public $ticket;

  /**
   * @param Support $ticket
   */
  public function __construct(Support $ticket = null)
  {
    $this->ticket = $ticket;
  }

  public function getModelId()
  {
    return $this->ticket->id;
  }

  public static function getUrl($id = null)
  {
    return ['/partners/support/index/', 'id' => $id];
  }
}