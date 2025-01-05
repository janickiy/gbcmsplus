<?php

namespace mcms\holds\commands;

use mcms\common\traits\LogTrait;
use mcms\holds\components\RuleUnholdPlanner;
use mcms\holds\models\HoldProgramRule;
use Yii;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * Пересчитываем график расхолда для одного или нескольких правил. Штатно для этого работает очередь, но всякое бывает
 */
class RuleUnholdPlannerController extends Controller
{

  use LogTrait;

  /**
   * @var int Айди правила. Либо через запятую можно указать несколько
   */
  public $ruleId;

  /**
   * @param $actionID
   * @return array
   */
  public function options($actionID)
  {
    return ['ruleId'];
  }

  public function actionIndex()
  {
    if (!$this->ruleId) {
      $this->stdout("Не указан параметр --ruleId\n", Console::FG_RED);
      return;
    }

    $ruleIds = explode(',', $this->ruleId);

    foreach ($ruleIds as $ruleId) {
      $this->log('Rule#' . $ruleId . ' begin...' . PHP_EOL);

      $rule = HoldProgramRule::findOne((int)$ruleId);
      if (!$rule) {
        Yii::error('Rule#' . $ruleId . ' not found', __METHOD__);
        $this->stdout('Rule#' . $ruleId . ' not found' . PHP_EOL, Console::FG_RED);
        continue;
      }

      if ((new RuleUnholdPlanner(['rule' => $rule]))->run()) {
        $this->log('Rule#' . $ruleId . ' done SUCCESSFULLY' . PHP_EOL);
        continue;
      }
      $this->stdout('Planner returned false, something went wrong' . PHP_EOL, Console::FG_RED);
      Yii::error('Planner  for Rule#' . $ruleId . ' returned false, something went wrong' . PHP_EOL);
    }
  }
}
