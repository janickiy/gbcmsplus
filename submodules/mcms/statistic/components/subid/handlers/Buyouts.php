<?php

namespace mcms\statistic\components\subid\handlers;


use mcms\statistic\components\subid\BaseHandler;
use Yii;

/**
 * Заполнение данных по выкупам
 */
class Buyouts extends BaseHandler
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
        INSERT INTO statistic_user_$userId (buyout_price_rub, buyout_price_usd, buyout_price_eur, buyout_visible_price_rub, buyout_visible_price_usd, buyout_visible_price_eur, buyout_partner_profit_rub, buyout_partner_profit_usd, buyout_partner_profit_eur,
        date, hour, source_id, landing_id, operator_id, platform_id, landing_pay_type_id, is_fake, subid1_id, subid2_id)
        SELECT 
          NULLIF(SUM(buyout_price_rub), 0) AS buyout_price_rub,
          NULLIF(SUM(buyout_price_usd), 0) AS buyout_price_usd,
          NULLIF(SUM(buyout_price_eur), 0) AS buyout_price_eur,
          NULLIF(SUM(buyout_visible_price_rub), 0) AS buyout_visible_price_rub,
          NULLIF(SUM(buyout_visible_price_usd), 0) AS buyout_visible_price_usd,
          NULLIF(SUM(buyout_visible_price_eur), 0) AS buyout_visible_price_eur,
          
          NULLIF(SUM(buyout_partner_profit_rub), 0) AS buyout_partner_profit_rub,
          NULLIF(SUM(buyout_partner_profit_usd), 0) AS buyout_partner_profit_usd,
          NULLIF(SUM(buyout_partner_profit_eur), 0) AS buyout_partner_profit_eur,
        
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
            SUM(IF(h.traffic_type = 2 AND ss.id IS NOT NULL, ss.reseller_price_rub, 0)) AS buyout_price_rub,
            SUM(IF(h.traffic_type = 2 AND ss.id IS NOT NULL, ss.reseller_price_usd, 0)) AS buyout_price_usd,
            SUM(IF(h.traffic_type = 2 AND ss.id IS NOT NULL, ss.reseller_price_eur, 0)) AS buyout_price_eur,
            SUM(IF(h.traffic_type = 2 AND ss.id IS NOT NULL, ss.price_rub, 0)) AS buyout_visible_price_rub,
            SUM(IF(h.traffic_type = 2 AND ss.id IS NOT NULL, ss.price_usd, 0)) AS buyout_visible_price_usd,
            SUM(IF(h.traffic_type = 2 AND ss.id IS NOT NULL, ss.price_eur, 0)) AS buyout_visible_price_eur,
            
            SUM(IF(h.traffic_type = 2 AND ss.is_visible_to_partner = 1 AND ss.id IS NOT NULL, ss.profit_rub, 0)) AS buyout_partner_profit_rub,
            SUM(IF(h.traffic_type = 2 AND ss.is_visible_to_partner = 1 AND ss.id IS NOT NULL, ss.profit_usd, 0)) AS buyout_partner_profit_usd,
            SUM(IF(h.traffic_type = 2 AND ss.is_visible_to_partner = 1 AND ss.id IS NOT NULL, ss.profit_eur, 0)) AS buyout_partner_profit_eur,
      
            ss.`date`,
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
          WHERE ss.date >= :dateFrom 
            AND ss.date <= :dateTo 
            AND h.source_id IN (SELECT id FROM {$this->getMainSchemaName()}.sources WHERE user_id = :userId)
            AND ss.time <= :maxTime
          GROUP BY
            ss.`date`,
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
            buyout_price_rub             = VALUES(buyout_price_rub),
            buyout_price_usd             = VALUES(buyout_price_usd),
            buyout_price_eur             = VALUES(buyout_price_eur),
            buyout_visible_price_rub     = VALUES(buyout_visible_price_rub),
            buyout_visible_price_usd     = VALUES(buyout_visible_price_usd),
            buyout_visible_price_eur     = VALUES(buyout_visible_price_eur),
            buyout_partner_profit_rub     = VALUES(buyout_partner_profit_rub),
            buyout_partner_profit_usd     = VALUES(buyout_partner_profit_usd),
            buyout_partner_profit_eur     = VALUES(buyout_partner_profit_eur)
        ")
        ->bindValue(':dateFrom', $this->cfg->getDateFrom(), \PDO::PARAM_STR)
        ->bindValue(':dateTo', $this->cfg->getDateTo(), \PDO::PARAM_STR)
        ->bindValue(':maxTime', $this->cfg->getMaxTime(), \PDO::PARAM_INT)
        ->bindValue(':userId', $userId, \PDO::PARAM_INT)
        ->execute();
    }
  }


}