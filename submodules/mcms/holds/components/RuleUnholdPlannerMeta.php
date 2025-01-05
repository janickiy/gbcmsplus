<?php

namespace mcms\holds\components;

use yii\base\Object;

/**
 * Вернет строку для отладки, мы её кладем в поле rule_unhold_plan.meta
 */
class RuleUnholdPlannerMeta extends Object
{
  /**
   * @var int
   */
  public $ruleId;
  /**
   * @var string Y-m-d
   */
  public $packDateFrom;
  /**
   * @var string Y-m-d
   */
  public $packDateTo;
  /**
   * @var UnholdDateCalc
   */
  public $calcObj;

  /**
   * @return string
   */
  public function getMetaText()
  {
    return json_encode([
      'ruleId' => $this->ruleId,
      'packDateFrom' => $this->packDateFrom,
      'packDateTo' => $this->packDateTo,
      'calcObj' => $this->calcObj
    ]);
  }
}
