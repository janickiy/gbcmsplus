<?php

namespace mcms\holds\components;

use yii\base\Object;

/**
 * DTO для rule_unhold_plan
 */
class RuleUnholdPlan extends Object
{
  public $rule_id;
  public $date_from;
  public $date_to;
  public $unhold_date;
}
