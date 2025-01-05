<?php

namespace mcms\holds\components;

use mcms\common\RunnableInterface;
use Yii;
use yii\base\Object;

/**
 * Расчитываем какую пачку холдов для связки партнер-страна последний раз расхолдили
 */
class PartnerCountryUnhold extends Object implements RunnableInterface
{

  /**
   * @return string
   */
  public static function tableName()
  {
    return 'partner_country_unhold';
  }

  public function run()
  {
    /**
     * Главный трабл этого скрипта в том, что нету прямых связей:
     * - какие действуют страны у правила (из-за country_id=null для глобальных)
     * - какая программа действует на партнера
     *
     * Чтобы решить это, предварительно создаем две temporary table (они действуют только в рамках текущей сессии).
     * У одной связка user_id, program_id
     * У другой rule_id, country_id
     */
    $this->createTmpTableUserProgram();
    $this->createTmpTableRuleCountry();

    $this->calcUnholds();
  }

  protected function createTmpTableUserProgram()
  {
    Yii::$app->db->createCommand('
      CREATE TEMPORARY TABLE user_hold_program (
        user_id     mediumint(5) unsigned not null,
        program_id     mediumint(5) unsigned not null,
        primary key (user_id, program_id)
      );
    ')->execute();

    Yii::$app->db->createCommand('
      INSERT INTO user_hold_program (user_id, program_id)
        SELECT sett.user_id, program.id FROM `user_payment_settings` `sett`
        INNER JOIN auth_assignment auth ON auth.user_id = sett.user_id AND auth.item_name = \'partner\'
        LEFT JOIN `hold_programs` `program`
          ON (sett.hold_program_id = program.id OR program.is_default = 1 AND sett.hold_program_id IS NULL);
    ')->execute();
  }

  protected function createTmpTableRuleCountry()
  {
    Yii::$app->db->createCommand('
      CREATE TEMPORARY TABLE hold_rule_country (
        program_id     mediumint(5) unsigned not null,
        country_id     mediumint(5) unsigned not null,
        rule_id     int unsigned not null,
        primary key (program_id, country_id)
      );
    ')->execute();

    Yii::$app->db->createCommand('
      INSERT INTO hold_rule_country (rule_id, country_id, program_id)
        SELECT (SELECT rule.id
                  FROM hold_program_rules rule
                  WHERE program.id = rule.hold_program_id AND (c.id = rule.country_id OR rule.country_id IS NULL)
                  ORDER BY country_id DESC
                  LIMIT 1) as rule_id,
          c.id as country_id,
          `program`.id
        FROM `hold_programs` `program`
          LEFT JOIN countries c ON 1 = 1
        HAVING rule_id IS NOT NULL
    ')->execute();
  }

  /**
   * расчет last_unhold_date для партнера-страны
   * @throws \yii\db\Exception
   */
  protected function calcUnholds()
  {
    // В начале запроса достаются user_id-country_id из балансов и инвойсов. И далее к этой связке приджойниваются
    // нужные таблицы чтобы получить в итоге максимальный date_to который расхолдился для user_id-country_id.

    Yii::$app->db->createCommand('
    INSERT INTO partner_country_unhold (user_id, country_id, last_unhold_date, meta, last_checked_at, last_updated_at)
    
    SELECT a.user_id, a.country_id, IFNULL(plan.date_to, CURDATE()), IF(plan.rule_id IS NOT NULL, plan.meta, \'current_date_applied\'), UNIX_TIMESTAMP(), UNIX_TIMESTAMP() FROM (
      
      SELECT DISTINCT user_id, country_id FROM user_balances_grouped_by_day WHERE country_id <> 0
      UNION
      SELECT DISTINCT user_id, country_id FROM user_balance_invoices WHERE country_id <> 0) a
      
        LEFT JOIN user_hold_program uhp ON a.user_id = uhp.user_id
        LEFT JOIN hold_programs hp ON hp.id = uhp.program_id
        LEFT JOIN hold_rule_country hrc ON hrc.country_id = a.country_id AND hp.id = hrc.program_id
        LEFT JOIN (
          SELECT rule_id, date_to, meta FROM rule_unhold_plan pl WHERE (pl.rule_id, pl.date_to) IN (
              SELECT rule_id, MAX(date_to) as max_date_to FROM rule_unhold_plan WHERE unhold_date <= CURDATE() GROUP BY rule_id
             )
          ) plan ON plan.rule_id = hrc.rule_id
    ON DUPLICATE KEY UPDATE
      last_unhold_date = IF(last_unhold_date < VALUES(last_unhold_date), VALUES(last_unhold_date), last_unhold_date),
      last_checked_at = VALUES(last_checked_at),
      last_updated_at = IF(last_unhold_date < VALUES(last_unhold_date), VALUES(last_updated_at), last_updated_at)
    ')->execute();
  }
}
