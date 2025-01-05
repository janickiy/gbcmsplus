<?php

namespace mcms\statistic\components\events;

use mcms\common\event\Event;

/**
 * Событие пересчета статы
 */
class RecalcStatEvent extends Event
{
  public $transId;
  public $time;

  /**
   * @param null $transId
   * @param null $time
   */
  public function __construct($transId = null, $time = null)
  {
    $this->transId = $transId;
    $this->time = $time;
  }

  /**
   * @return string
   */
  public function getEventName()
  {
    return 'Statistic recalculate';
  }

}
