<?php

namespace mcms\holds\components;

use mcms\holds\models\HoldProgram;
use mcms\holds\models\HoldProgramRule;
use mcms\payments\models\UserPaymentSetting;
use Yii;
use yii\base\Object;
use yii\db\Query;

/**
 * Получаем подходящее правило расхолда по юзеру-стране
 */
class RulePicker extends Object
{
  /**
   * @var int
   */
  public $userId;
  /**
   * @var int
   */
  public $countryId;

  /**
   * @return HoldProgramRule
   */
  public function getRule()
  {
    $rule = Yii::$app->db->createCommand('
      SELECT rule.*
        FROM
          `hold_program_rules` `rule`
          INNER JOIN (
              SELECT
                (SELECT hold_program_id FROM user_payment_settings WHERE user_id = :userId) as user_program_id,
                program.id,
                program.is_default
              FROM `hold_programs` `program`
              HAVING user_program_id = program.id OR program.is_default=1
              ORDER BY is_default ASC
              LIMIT 1
           ) program ON program.id = rule.hold_program_id
        WHERE (`rule`.`country_id` IS NULL) OR (`rule`.`country_id` = :countryId)
        ORDER BY `rule`.`country_id` DESC
    ', [
      ':userId' => $this->userId,
      ':countryId' => $this->countryId
    ])->queryOne();

    if (!$rule) {
      return null;
    }

    $model = new HoldProgramRule();

    $model->setAttributes($rule, false);

    $model->isNewRecord = false; // на всякий случай, хотя вроде и не нужно особо

    return $model;
  }
}
