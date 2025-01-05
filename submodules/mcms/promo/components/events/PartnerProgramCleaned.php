<?php
namespace mcms\promo\components\events;

use mcms\common\event\Event;
use Yii;
use mcms\promo\models\PartnerProgram;

/**
 * Событие вызывается, когда ПП очищается от неактивных лендингов
 */
class PartnerProgramCleaned extends Event
{

  public $partnerProgram;
  public $landingIds;

  /**
   * PartnerProgramCleaned constructor.
   * @param PartnerProgram $partnerProgram
   * @param int[] $landingIds
   */
  public function __construct(PartnerProgram $partnerProgram, $landingIds)
  {
    $this->partnerProgram = $partnerProgram;
    $this->landingIds = $landingIds;
  }

  public function getModelId()
  {
    return $this->partnerProgram->id;
  }

  function getEventName()
  {
    return 'The partner program is cleared of inactive lendings';
  }
}