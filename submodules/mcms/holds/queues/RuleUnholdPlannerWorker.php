<?php
namespace mcms\holds\queues;

use mcms\holds\components\RuleUnholdPlanner;
use mcms\holds\models\HoldProgramRule;
use rgk\queue\PayloadInterface;
use rgk\queue\WorkerInterface;
use Yii;

/**
 * По айди правила пересчитываем график расхолда
 */
class RuleUnholdPlannerWorker implements WorkerInterface
{
  const CHANNEL_NAME = 'rule_unhold_planner';

  /**
   * @param PayloadInterface|RuleUnholdPlannerPayload $payload
   * @return bool
   */
  public function work(PayloadInterface $payload)
  {
    if (!$payload->ruleId) {
      Yii::warning('Invalid RuleUnholdPlannerWorker payload! ruleId: ' . $payload->ruleId . PHP_EOL);

      return true;
    }

    $rule = HoldProgramRule::findOne((int)$payload->ruleId);
    if (!$rule) {
      Yii::error('Rule#' . $payload->ruleId . ' not found. Worker return false' . PHP_EOL);
      return false;
    }

    return (new RuleUnholdPlanner(['rule' => $rule]))->run();
  }

  /**
   * @inheritdoc
   */
  public function getChannelName()
  {
    return self::CHANNEL_NAME;
  }
}
