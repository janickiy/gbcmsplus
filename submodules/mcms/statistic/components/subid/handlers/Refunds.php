<?php

namespace mcms\statistic\components\subid\handlers;


use mcms\statistic\components\subid\BaseHandler;
use Yii;

/**
 * Заполнение данных о возвратах
 */
class Refunds extends BaseHandler
{
  /**
   * @throws \yii\base\InvalidConfigException
   * @throws \yii\db\Exception
   */
  public function run()
  {
    foreach ($this->cfg->getUserIds() as $userId) {
      $this->log("[$userId]");
      Yii::$app->sdb->createCommand("
        /** @lang MySQL */
        INSERT INTO statistic_user_$userId (revshare_rgk_refunds, to_buyout_rgk_refunds, otp_rgk_refunds, 
        revshare_mno_refunds, to_buyout_mno_refunds, otp_mno_refunds,
        revshare_rgk_refund_sum_rub, revshare_rgk_refund_sum_usd, revshare_rgk_refund_sum_eur,
        to_buyout_rgk_refund_sum_rub, to_buyout_rgk_refund_sum_usd, to_buyout_rgk_refund_sum_eur,
        otp_rgk_refund_sum_rub, otp_rgk_refund_sum_usd, otp_rgk_refund_sum_eur,
        revshare_mno_refund_sum_rub, revshare_mno_refund_sum_usd, revshare_mno_refund_sum_eur,
        to_buyout_mno_refund_sum_rub, to_buyout_mno_refund_sum_usd, to_buyout_mno_refund_sum_eur,
        otp_mno_refund_sum_rub, otp_mno_refund_sum_usd, otp_mno_refund_sum_eur,
        date, hour, source_id, landing_id, operator_id, platform_id, landing_pay_type_id, is_fake, subid1_id, subid2_id)
        SELECT 
          NULLIF(SUM(revshare_rgk_refunds), 0) AS revshare_rgk_refunds,
          NULLIF(SUM(to_buyout_rgk_refunds), 0) AS to_buyout_rgk_refunds,
          NULLIF(SUM(otp_rgk_refunds), 0) AS otp_rgk_refunds,
          NULLIF(SUM(revshare_mno_refunds), 0) AS revshare_mno_refunds,
          NULLIF(SUM(to_buyout_mno_refunds), 0) AS to_buyout_mno_refunds,
          NULLIF(SUM(otp_mno_refunds), 0) AS otp_mno_refunds,
          
          NULLIF(SUM(revshare_rgk_refund_sum_rub), 0) AS revshare_rgk_refund_sum_rub,
          NULLIF(SUM(revshare_rgk_refund_sum_usd), 0) AS revshare_rgk_refund_sum_usd,
          NULLIF(SUM(revshare_rgk_refund_sum_eur), 0) AS revshare_rgk_refund_sum_eur,
          
          NULLIF(SUM(to_buyout_rgk_refund_sum_rub), 0) AS to_buyout_rgk_refund_sum_rub,
          NULLIF(SUM(to_buyout_rgk_refund_sum_usd), 0) AS to_buyout_rgk_refund_sum_usd,
          NULLIF(SUM(to_buyout_rgk_refund_sum_eur), 0) AS to_buyout_rgk_refund_sum_eur,
          
          NULLIF(SUM(otp_rgk_refund_sum_rub), 0) AS otp_rgk_refund_sum_rub,
          NULLIF(SUM(otp_rgk_refund_sum_usd), 0) AS otp_rgk_refund_sum_usd,
          NULLIF(SUM(otp_rgk_refund_sum_eur), 0) AS otp_rgk_refund_sum_eur,
          
          NULLIF(SUM(revshare_mno_refund_sum_rub), 0) AS revshare_mno_refund_sum_rub,
          NULLIF(SUM(revshare_mno_refund_sum_usd), 0) AS revshare_mno_refund_sum_usd,
          NULLIF(SUM(revshare_mno_refund_sum_eur), 0) AS revshare_mno_refund_sum_eur,
          
          NULLIF(SUM(to_buyout_mno_refund_sum_rub), 0) AS to_buyout_mno_refund_sum_rub,
          NULLIF(SUM(to_buyout_mno_refund_sum_usd), 0) AS to_buyout_mno_refund_sum_usd,
          NULLIF(SUM(to_buyout_mno_refund_sum_eur), 0) AS to_buyout_mno_refund_sum_eur,
          
          NULLIF(SUM(otp_mno_refund_sum_rub), 0) AS otp_mno_refund_sum_rub,
          NULLIF(SUM(otp_mno_refund_sum_usd), 0) AS otp_mno_refund_sum_usd,
          NULLIF(SUM(otp_mno_refund_sum_eur), 0) AS otp_mno_refund_sum_eur,
        
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
            COUNT(IF(h.traffic_type = 1 AND r.type = 1, r.id, NULL))       AS revshare_rgk_refunds,
            COUNT(IF(h.traffic_type = 2 AND type = 1, r.id, null))         AS to_buyout_rgk_refunds,
            COUNT(IF(h.traffic_type = 3 AND type = 1, r.id, null))         AS otp_rgk_refunds,
            COUNT(IF(h.traffic_type = 1 AND type = 2, r.id, null))         AS revshare_mno_refunds,
            COUNT(IF(h.traffic_type = 2 AND type = 2, r.id, null))         AS to_buyout_mno_refunds,
            COUNT(IF(h.traffic_type = 3 AND type = 2, r.id, null))         AS otp_mno_refunds,
          
            SUM(IF(h.traffic_type = 1 AND type = 1, r.reseller_rub, null)) AS revshare_rgk_refund_sum_rub,
            SUM(IF(h.traffic_type = 1 AND type = 1, r.reseller_usd, null)) AS revshare_rgk_refund_sum_usd,
            SUM(IF(h.traffic_type = 1 AND type = 1, r.reseller_eur, null)) AS revshare_rgk_refund_sum_eur,
          
            SUM(IF(h.traffic_type = 2 AND type = 1, r.reseller_rub, null)) AS to_buyout_rgk_refund_sum_rub,
            SUM(IF(h.traffic_type = 2 AND type = 1, r.reseller_usd, null)) AS to_buyout_rgk_refund_sum_usd,
            SUM(IF(h.traffic_type = 2 AND type = 1, r.reseller_eur, null)) AS to_buyout_rgk_refund_sum_eur,
          
            SUM(IF(h.traffic_type = 3 AND type = 1, r.reseller_rub, null)) AS otp_rgk_refund_sum_rub,
            SUM(IF(h.traffic_type = 3 AND type = 1, r.reseller_usd, null)) AS otp_rgk_refund_sum_usd,
            SUM(IF(h.traffic_type = 3 AND type = 1, r.reseller_eur, null)) AS otp_rgk_refund_sum_eur,
          
            SUM(IF(h.traffic_type = 1 AND type = 2, r.reseller_rub, null)) AS revshare_mno_refund_sum_rub,
            SUM(IF(h.traffic_type = 1 AND type = 2, r.reseller_usd, null)) AS revshare_mno_refund_sum_usd,
            SUM(IF(h.traffic_type = 1 AND type = 2, r.reseller_eur, null)) AS revshare_mno_refund_sum_eur,
          
            SUM(IF(h.traffic_type = 2 AND type = 2, r.reseller_rub, null)) AS to_buyout_mno_refund_sum_rub,
            SUM(IF(h.traffic_type = 2 AND type = 2, r.reseller_usd, null)) AS to_buyout_mno_refund_sum_usd,
            SUM(IF(h.traffic_type = 2 AND type = 2, r.reseller_eur, null)) AS to_buyout_mno_refund_sum_eur,
          
            SUM(IF(h.traffic_type = 3 AND type = 2, r.reseller_rub, null)) AS otp_mno_refund_sum_rub,
            SUM(IF(h.traffic_type = 3 AND type = 2, r.reseller_usd, null)) AS otp_mno_refund_sum_usd,
            SUM(IF(h.traffic_type = 3 AND type = 2, r.reseller_eur, null)) AS otp_mno_refund_sum_eur,
          
            r.date,
            r.hour,
            r.source_id,
            r.landing_id,
            r.operator_id,
            r.platform_id,
            r.landing_pay_type_id,
            0                                                              AS is_fake,
            hp.`subid1`,
            hp.`subid2`
          FROM {$this->getMainSchemaName()}.`refunds` r
            LEFT JOIN {$this->getMainSchemaName()}.hits h
              ON h.id = r.hit_id
            LEFT JOIN {$this->getMainSchemaName()}.hit_params hp
              ON h.id = hp.hit_id
            WHERE r.date >= :dateFrom 
              AND r.date <= :dateTo 
              AND h.source_id IN (SELECT id FROM {$this->getMainSchemaName()}.sources WHERE user_id = :userId)
              AND r.time <= :maxTime
          GROUP BY
            r.`date`,
            r.`hour`,
            r.`source_id`,
            r.`landing_id`,
            r.`operator_id`,
            r.`platform_id`,
            r.`landing_pay_type_id`,
            hp.`subid1`,
            hp.`subid2`
        ) inside
               LEFT JOIN {$this->getMainSchemaName()}.subid_glossary sg_1 ON sg_1.hash = MD5(inside.subid1)
               LEFT JOIN {$this->getMainSchemaName()}.subid_glossary sg_2 ON sg_2.hash = MD5(inside.subid2)
        GROUP BY date, hour, source_id, landing_id, operator_id, platform_id, landing_pay_type_id, sg_1.id,
          sg_2.id
          
          ON DUPLICATE KEY UPDATE 
            revshare_rgk_refunds           = VALUES(revshare_rgk_refunds),
            to_buyout_rgk_refunds          = VALUES(to_buyout_rgk_refunds),
            otp_rgk_refunds                = VALUES(otp_rgk_refunds),
            revshare_mno_refunds           = VALUES(revshare_mno_refunds),
            to_buyout_mno_refunds          = VALUES(to_buyout_mno_refunds),
            otp_mno_refunds                = VALUES(otp_mno_refunds),
            
            revshare_rgk_refund_sum_rub    = VALUES(revshare_rgk_refund_sum_rub),
            revshare_rgk_refund_sum_usd    = VALUES(revshare_rgk_refund_sum_usd),
            revshare_rgk_refund_sum_eur    = VALUES(revshare_rgk_refund_sum_eur),

            to_buyout_rgk_refund_sum_rub   = VALUES(to_buyout_rgk_refund_sum_rub),
            to_buyout_rgk_refund_sum_usd   = VALUES(to_buyout_rgk_refund_sum_usd),
            to_buyout_rgk_refund_sum_eur   = VALUES(to_buyout_rgk_refund_sum_eur),
            
            otp_rgk_refund_sum_rub         = VALUES(otp_rgk_refund_sum_rub),
            otp_rgk_refund_sum_usd         = VALUES(otp_rgk_refund_sum_usd),
            otp_rgk_refund_sum_eur         = VALUES(otp_rgk_refund_sum_eur),
            
            revshare_mno_refund_sum_rub    = VALUES(revshare_mno_refund_sum_rub),
            revshare_mno_refund_sum_usd    = VALUES(revshare_mno_refund_sum_usd),
            revshare_mno_refund_sum_eur    = VALUES(revshare_mno_refund_sum_eur),
            
            to_buyout_mno_refund_sum_rub   = VALUES(to_buyout_mno_refund_sum_rub),
            to_buyout_mno_refund_sum_usd   = VALUES(to_buyout_mno_refund_sum_usd),
            to_buyout_mno_refund_sum_eur   = VALUES(to_buyout_mno_refund_sum_eur),
            
            otp_mno_refund_sum_rub         = VALUES(otp_mno_refund_sum_rub),
            otp_mno_refund_sum_usd         = VALUES(otp_mno_refund_sum_usd),
            otp_mno_refund_sum_eur         = VALUES(otp_mno_refund_sum_eur)
        ")
        ->bindValue(':dateFrom', $this->cfg->getDateFrom(), \PDO::PARAM_STR)
        ->bindValue(':dateTo', $this->cfg->getDateTo(), \PDO::PARAM_STR)
        ->bindValue(':maxTime', $this->cfg->getMaxTime(), \PDO::PARAM_INT)
        ->bindValue(':userId', $userId, \PDO::PARAM_INT)
        ->execute();
    }
  }


}