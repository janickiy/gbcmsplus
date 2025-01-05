<?php
namespace mcms\promo\components\events;

use mcms\common\event\Event;
use Yii;

class LandingConvertTest extends Event
{

  public $startTime;
  public $endTime;
  public $stdout = '';
  public $countTests;
  public $countFinishedTests;

  function getEventName()
  {
    return 'Landing convert test console app';
  }

  function getReplacements()
  {
    return [
      'startTime' => $this->startTime,
      'endTime' => $this->endTime,
      'countTests' => $this->countTests,
      'countFinishedTests' => $this->countFinishedTests,
      'stdout' => $this->stdout,
    ];
  }

  function getReplacementsHelp()
  {
    return [
      'startTime' => 'Script start timestamp',
      'endTime' => 'Script finish timestamp',
      'countTests' => 'Total active tests',
      'countFinishedTests' => 'Finished and not calculated tests',
      'stdout' => 'Console stdout text',
    ];
  }
}