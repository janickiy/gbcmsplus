<?php

namespace mcms\statistic\components\subid\handlers;


use mcms\statistic\components\subid\BaseHandler;
use Yii;

/**
 * Заполнение данных бо отписках
 */
class Unsubscriptions extends BaseHandler
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
        INSERT INTO statistic_user_$userId (revshare_offs, rejected_offs, buyout_offs, 
        date, hour, source_id, landing_id, operator_id, platform_id, landing_pay_type_id, is_fake, subid1_id, subid2_id)
        SELECT 
          NULLIF(SUM(revshare_offs), 0) AS revshare_offs,
          NULLIF(SUM(rejected_offs), 0) AS rejected_offs,
          NULLIF(SUM(buyout_offs), 0) AS buyout_offs,
        
          `date`,
          `hour`,
          `source_id`,
          `landing_id`,
          `operator_id`,
          `platform_id`,
          `landing_pay_type_id`,
          `is_fake`,
          sg_1.`id` AS subid1_id,
          sg_2.`id` AS subid2_id
        FROM (
          SELECT
            COUNT(IF(h.traffic_type = 1, s.id, NULL)) AS revshare_offs,
            COUNT(IF(h.traffic_type = 2 AND ss.id IS NULL, s.id, NULL)) AS rejected_offs,
            COUNT(IF(h.traffic_type = 2 AND ss.id IS NOT NULL, s.id, NULL)) AS buyout_offs,
      
            s.`date`,
            s.`hour`,
            s.`source_id`,
            s.`landing_id`,
            s.`operator_id`,
            s.`platform_id`,
            s.`landing_pay_type_id`,
            s.`is_fake`,
            hp.`subid1`,
            hp.`subid2`
          FROM {$this->getMainSchemaName()}.subscription_offs s
            LEFT JOIN {$this->getMainSchemaName()}.sold_subscriptions ss
              ON s.hit_id = ss.hit_id
            LEFT JOIN {$this->getMainSchemaName()}.hits h
              ON h.id = s.hit_id
            LEFT JOIN {$this->getMainSchemaName()}.hit_params hp
              ON h.id = hp.hit_id
          WHERE s.date >= :dateFrom 
            AND s.date <= :dateTo 
            AND h.source_id IN (SELECT id FROM {$this->getMainSchemaName()}.sources WHERE user_id = :userId)
            AND s.time <= :maxTime
          GROUP BY
            s.`date`,
            s.`hour`,
            s.`source_id`,
            s.`landing_id`,
            s.`operator_id`,
            s.`platform_id`,
            s.`landing_pay_type_id`,
            s.`is_fake`,
            hp.`subid1`,
            hp.`subid2`
        ) inside
               LEFT JOIN {$this->getMainSchemaName()}.subid_glossary sg_1 ON sg_1.hash = MD5(inside.subid1)
               LEFT JOIN {$this->getMainSchemaName()}.subid_glossary sg_2 ON sg_2.hash = MD5(inside.subid2)
        GROUP BY date, hour, source_id, landing_id, operator_id, platform_id, landing_pay_type_id, sg_1.id,
          sg_2.id
          
          ON DUPLICATE KEY UPDATE 
            revshare_offs              = VALUES(revshare_offs),
            rejected_offs              = VALUES(rejected_offs),
            buyout_offs                = VALUES(buyout_offs)
        ")
        ->bindValue(':dateFrom', $this->cfg->getDateFrom(), \PDO::PARAM_STR)
        ->bindValue(':dateTo', $this->cfg->getDateTo(), \PDO::PARAM_STR)
        ->bindValue(':maxTime', $this->cfg->getMaxTime(), \PDO::PARAM_INT)
        ->bindValue(':userId', $userId, \PDO::PARAM_INT)
        ->execute();
    }
  }


}