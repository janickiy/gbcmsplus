<?php

namespace mcms\statistic\components\subid\handlers;


use mcms\statistic\components\subid\BaseHandler;
use Yii;

/**
 * Заполнение данных о скорректированных ребилах 24
 */
class CorrectedRebills24 extends BaseHandler
{
  /**
   * @throws \yii\base\InvalidConfigException
   * @throws \yii\db\Exception
   */
  public function run()
  {
    foreach ($this->cfg->getUserIds() as $userId) {
      $this->log("$userId|");
      Yii::$app->sdb->createCommand("
        /** @lang MySQL */
        INSERT INTO statistic_user_$userId (revshare_corrected_rebills24,
        date, hour, source_id, landing_id, operator_id, platform_id, landing_pay_type_id, is_fake, subid1_id, subid2_id)
        SELECT 
          NULLIF(SUM(revshare_corrected_rebills24), 0) AS revshare_corrected_rebills24,
        
          `date`,
          `hour`,
          `source_id`,
          `landing_id`,
          `operator_id`,
          `platform_id`,
          `landing_pay_type_id`,
          0 AS is_fake,
          sg_1.`id` AS subid1_id,
          sg_2.`id` AS subid2_id
        FROM (
          SELECT
            COUNT(s.id) AS revshare_corrected_rebills24,
      
            src.`date`,
            s.`hour`,
            s.`source_id`,
            s.`landing_id`,
            s.`operator_id`,
            s.`platform_id`,
            s.`landing_pay_type_id`,
            hp.`subid1`,
            hp.`subid2`
          FROM {$this->getMainSchemaName()}.`subscription_rebills_corrected` src
            LEFT JOIN {$this->getMainSchemaName()}.`subscriptions` s
              ON s.hit_id = src.hit_id AND
           (({$this->getTrialOperatorsInCondition('s.operator_id', true)} AND s.date = src.date) OR ({$this->getTrialOperatorsInCondition('s.operator_id')} AND s.date = date_add(src.date, INTERVAL -1 DAY)))
            LEFT JOIN {$this->getMainSchemaName()}.hits h
              ON h.id = src.hit_id
            LEFT JOIN {$this->getMainSchemaName()}.hit_params hp
              ON h.id = hp.hit_id
          WHERE s.is_fake = 0 
            AND src.date >= :dateFrom
            AND src.date <= :dateTo
            AND h.source_id IN (SELECT id FROM {$this->getMainSchemaName()}.sources WHERE user_id = :userId)
            AND src.time <= :maxTime
          GROUP BY
            src.`date`,
            s.`hour`,
            s.`source_id`,
            s.`landing_id`,
            s.`operator_id`,
            s.`platform_id`,
            s.`landing_pay_type_id`,
            hp.`subid1`,
            hp.`subid2`
        ) inside
               LEFT JOIN {$this->getMainSchemaName()}.subid_glossary sg_1 ON sg_1.hash = MD5(inside.subid1)
               LEFT JOIN {$this->getMainSchemaName()}.subid_glossary sg_2 ON sg_2.hash = MD5(inside.subid2)
        GROUP BY date, hour, source_id, landing_id, operator_id, platform_id, landing_pay_type_id, sg_1.id,
          sg_2.id
          
          ON DUPLICATE KEY UPDATE 
            revshare_corrected_rebills24  = VALUES(revshare_corrected_rebills24)
        ")
        ->bindValue(':dateFrom', $this->cfg->getDateFrom(), \PDO::PARAM_STR)
        ->bindValue(':dateTo', $this->cfg->getDateTo(), \PDO::PARAM_STR)
        ->bindValue(':maxTime', $this->cfg->getMaxTime(), \PDO::PARAM_INT)
        ->bindValue(':userId', $userId, \PDO::PARAM_INT)
        ->execute();
    }
  }


}