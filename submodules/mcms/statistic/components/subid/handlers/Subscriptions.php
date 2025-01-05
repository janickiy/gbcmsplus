<?php

namespace mcms\statistic\components\subid\handlers;


use mcms\statistic\components\subid\BaseHandler;
use Yii;

/**
 * Заполнение данных о подписках
 */
class Subscriptions extends BaseHandler
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
        INSERT INTO statistic_user_$userId (revshare_ons, to_buyout_ons, rejected_ons, buyout_ons, buyout_visible_ons, 
        date, hour, source_id, landing_id, operator_id, platform_id, landing_pay_type_id, is_fake, subid1_id, subid2_id)
        SELECT 
          NULLIF(SUM(revshare_ons), 0) AS revshare_ons,
          NULLIF(SUM(to_buyout_ons), 0) AS to_buyout_ons,
          NULLIF(SUM(rejected_ons), 0) AS rejected_ons,
          NULLIF(SUM(buyout_ons), 0) AS buyout_ons,
          NULLIF(SUM(buyout_visible_ons), 0) AS buyout_visible_ons,
        
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
            COUNT(IF(h.traffic_type = 1, s.id, NULL)) AS revshare_ons,
            COUNT(IF(h.traffic_type = 2, s.id, NULL)) AS to_buyout_ons,
            COUNT(IF(h.traffic_type = 2 AND ss.id IS NULL, s.id, NULL)) AS rejected_ons,
            COUNT(IF(h.traffic_type = 2 AND ss.id IS NOT NULL, s.id, NULL)) AS buyout_ons,
            COUNT(IF(h.traffic_type = 2 AND ss.id IS NOT NULL AND ss.is_visible_to_partner = 1, s.id, NULL)) AS buyout_visible_ons,
      
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
          FROM {$this->getMainSchemaName()}.subscriptions s
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
            revshare_ons              = VALUES(revshare_ons),
            to_buyout_ons             = VALUES(to_buyout_ons),
            rejected_ons              = VALUES(rejected_ons),
            buyout_ons                = VALUES(buyout_ons),
            buyout_visible_ons        = VALUES(buyout_visible_ons)
        ")
        ->bindValue(':dateFrom', $this->cfg->getDateFrom(), \PDO::PARAM_STR)
        ->bindValue(':dateTo', $this->cfg->getDateTo(), \PDO::PARAM_STR)
        ->bindValue(':maxTime', $this->cfg->getMaxTime(), \PDO::PARAM_INT)
        ->bindValue(':userId', $userId, \PDO::PARAM_INT)
        ->execute();
    }
  }


}