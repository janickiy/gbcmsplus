<?php
namespace mcms\statistic\components\events;

use mcms\common\event\Event;
use Yii;

class PostbackEvent extends Event
{
  public $startTime;
  public $endTime;
  public $timeFrom;
  public $maxAttempts;
  public $success = false;
  public $stdout = '';
  public $type;
  public $count;

  public function getEventName()
  {
    return 'Postback script';
  }

  public function getReplacements()
  {
    return [
      'startTime' => $this->startTime,
      'endTime' => $this->endTime,
      'timeFrom' => $this->timeFrom,
      'maxAttempts' => $this->maxAttempts,
      'success' => $this->success,
      'stdout' => $this->stdout,
      'type' => $this->type,
      'count' => $this->count,
    ];
  }

  public function getReplacementsHelp()
  {
    return [
      'startTime' => 'Script start timestamp',
      'endTime' => 'Script finish timestamp',
      'timeFrom' => 'Get info from time',
      'maxAttempts' => 'Max send attempts',
      'success' => 'Script result',
      'stdout' => 'Console stdout text',
      'type' => 'Type of postback',
      'count' => 'Count of notifications',
    ];
  }
}