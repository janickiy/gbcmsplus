<?php

namespace mcms\holds\queues;

use rgk\queue\BasePayload;

/**
 * Данные для воркера @see RuleUnholdPlannerWorker
 */
class RuleUnholdPlannerPayload extends BasePayload
{
  /** @var int ID правила для расхолда*/
  public $ruleId;
}
