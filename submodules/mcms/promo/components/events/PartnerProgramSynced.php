<?php
namespace mcms\promo\components\events;

use mcms\common\event\Event;
use Yii;

class PartnerProgramSynced extends Event
{
  public $programId;
  public $userId;

  public function __construct($programId = null, $userId = null)
  {
    $this->programId = $programId;
    $this->userId = $userId;
  }

  public function getModelId()
  {
    return $this->programId;
  }

  function getEventName()
  {
    return Yii::_t('promo.events.partner_program_synced');
  }
}