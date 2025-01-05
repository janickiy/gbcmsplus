<?php

namespace mcms\statistic\components\subid\handlers;

use mcms\promo\models\Landing;
use mcms\statistic\components\subid\BaseHandler;
use mcms\statistic\models\Complain;
use Yii;

/**
 */
class Onetime extends BaseHandler
{
  public function run()
  {
    foreach ($this->cfg->getUserIds() as $userId) {
      $this->log("$userId|");
      Yii::$app->sdb->createCommand("
        /** @lang MySQL */
        INSERT INTO statistic_user_$userId (otp_ons, otp_visible_ons, otp_partner_profit_rub, otp_partner_profit_usd,
                              otp_partner_profit_eur, otp_reseller_profit_rub, otp_reseller_profit_usd,
                              otp_reseller_profit_eur, otp_reseller_corrected_profit_rub,
                              otp_reseller_corrected_profit_usd, otp_reseller_corrected_profit_eur,
                              date, hour, source_id, landing_id, operator_id, platform_id, landing_pay_type_id, is_fake,
                              subid1_id, subid2_id, stream_id, provider_id, country_id)
          SELECT NULLIF(SUM(otp_ons), 0),
            NULLIF(SUM(otp_visible_ons), 0),
          
            NULLIF(SUM(otp_partner_profit_rub), 0),
            NULLIF(SUM(otp_partner_profit_usd), 0),
            NULLIF(SUM(otp_partner_profit_eur), 0),
          
            NULLIF(SUM(otp_reseller_profit_rub), 0),
            NULLIF(SUM(otp_reseller_profit_usd), 0),
            NULLIF(SUM(otp_reseller_profit_eur), 0),
          
            NULLIF(SUM(otp_reseller_corrected_profit_rub), 0),
            NULLIF(SUM(otp_reseller_corrected_profit_usd), 0),
            NULLIF(SUM(otp_reseller_corrected_profit_eur), 0),
          
            date,
            hour,
            source_id,
            landing_id,
            operator_id,
            platform_id,
            landing_pay_type_id,
            0,
            g1.id,
            g2.id,
            stream_id,
            provider_id,
            country_id
          FROM (
            SELECT COUNT(1) AS otp_ons,
              COUNT(IF(otp.is_visible_to_partner = 1, 1, NULL)) AS otp_visible_ons,
          
              SUM(otp.profit_rub) AS otp_partner_profit_rub,
              SUM(otp.profit_usd) AS otp_partner_profit_usd,
              SUM(otp.profit_eur) AS otp_partner_profit_eur,
          
              SUM(otp.reseller_profit_rub) AS otp_reseller_profit_rub,
              SUM(otp.reseller_profit_usd) AS otp_reseller_profit_usd,
              SUM(otp.reseller_profit_eur) AS otp_reseller_profit_eur,
          
              SUM(IF(otp.is_visible_to_partner = 0, reseller_profit_rub, NULL)) AS otp_reseller_corrected_profit_rub,
              SUM(IF(otp.is_visible_to_partner = 0, reseller_profit_usd, NULL)) AS otp_reseller_corrected_profit_usd,
              SUM(IF(otp.is_visible_to_partner = 0, reseller_profit_eur, NULL)) AS otp_reseller_corrected_profit_eur,
          
              otp.date,
              otp.hour,
              otp.source_id,
              otp.landing_id,
              otp.operator_id,
              otp.platform_id,
              otp.landing_pay_type_id,
              0,
              hp.subid1,
              hp.subid2,
              otp.stream_id,
              otp.provider_id,
              otp.country_id
            FROM {$this->getMainSchemaName()}.onetime_subscriptions otp
                   INNER JOIN {$this->getMainSchemaName()}.hit_params hp ON hp.hit_id = otp.hit_id
                   INNER JOIN {$this->getMainSchemaName()}.hits h ON h.id = otp.hit_id
            WHERE otp.date >= :dateFrom 
              AND otp.date <= :dateTo 
              AND otp.user_id=:userId  
              AND otp.time <= :maxTime
            GROUP BY otp.date, otp.hour, otp.source_id, otp.landing_id, otp.operator_id, otp.platform_id, otp.landing_pay_type_id,
              hp.subid1,
              hp.subid2) inside
                 LEFT JOIN {$this->getMainSchemaName()}.subid_glossary g1 ON g1.hash = MD5(inside.subid1)
                 LEFT JOIN {$this->getMainSchemaName()}.subid_glossary g2 ON g2.hash = MD5(inside.subid2)
          GROUP BY date, hour, source_id, landing_id, operator_id, platform_id, landing_pay_type_id, g1.id,
            g2.id
          
          ON DUPLICATE KEY UPDATE otp_ons     = VALUES(otp_ons),
            otp_visible_ons                   = VALUES(otp_visible_ons),
            otp_partner_profit_rub            = VALUES(otp_partner_profit_rub),
            otp_partner_profit_usd            = VALUES(otp_partner_profit_usd),
            otp_partner_profit_eur            = VALUES(otp_partner_profit_eur),
            otp_reseller_profit_rub           = VALUES(otp_reseller_profit_rub),
            otp_reseller_profit_usd           = VALUES(otp_reseller_profit_usd),
            otp_reseller_profit_eur           = VALUES(otp_reseller_profit_eur),
            otp_reseller_corrected_profit_rub = VALUES(otp_reseller_corrected_profit_rub),
            otp_reseller_corrected_profit_usd = VALUES(otp_reseller_corrected_profit_usd),
            otp_reseller_corrected_profit_eur = VALUES(otp_reseller_corrected_profit_eur);
        ")
        ->bindValue(':dateFrom', $this->cfg->getDateFrom(), \PDO::PARAM_STR)
        ->bindValue(':dateTo', $this->cfg->getDateTo(), \PDO::PARAM_STR)
        ->bindValue(':maxTime', $this->cfg->getMaxTime(), \PDO::PARAM_INT)
        ->bindValue(':userId', $userId, \PDO::PARAM_INT)
        ->execute();
    }


  }
}
