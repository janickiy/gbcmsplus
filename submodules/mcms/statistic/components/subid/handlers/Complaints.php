<?php

namespace mcms\statistic\components\subid\handlers;

use mcms\promo\models\Landing;
use mcms\statistic\components\subid\BaseHandler;
use mcms\statistic\models\Complain;
use Yii;

/**
 */
class Complaints extends BaseHandler
{
  public function run()
  {
    foreach ($this->cfg->getUserIds() as $userId) {
      $this->log("$userId|");
      Yii::$app->sdb->createCommand("
        /** @lang MySQL */
        INSERT INTO statistic_user_$userId (revshare_text_complaints, revshare_call_complaints, revshare_call_mno_complaints,
                              otp_text_complaints, otp_call_complaints, otp_call_mno_complaints,
                              to_buyout_text_complaints, to_buyout_call_complaints, to_buyout_call_mno_complaints,
                              date, hour, source_id, landing_id, operator_id, platform_id, landing_pay_type_id, is_fake,
                              subid1_id, subid2_id, stream_id, provider_id, country_id)
          SELECT NULLIF(SUM(revshare_text_complaints), 0),
            NULLIF(SUM(revshare_call_complaints), 0),
            NULLIF(SUM(revshare_call_mno_complaints), 0),
            NULLIF(SUM(otp_text_complaints), 0),
            NULLIF(SUM(otp_call_complaints), 0),
            NULLIF(SUM(otp_call_mno_complaints), 0),
            NULLIF(SUM(to_buyout_text_complaints), 0),
            NULLIF(SUM(to_buyout_call_complaints), 0),
            NULLIF(SUM(to_buyout_call_mno_complaints), 0),
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
            SELECT COUNT(IF(h.traffic_type = :trafRevshare AND c.type = :typeText, 1, NULL)) AS revshare_text_complaints,
              COUNT(IF(h.traffic_type = :trafRevshare AND c.type = :typeCall, 1, NULL)) AS revshare_call_complaints,
              COUNT(IF(h.traffic_type = :trafRevshare AND c.type = :typeCallMno, 1, NULL)) AS revshare_call_mno_complaints,
              COUNT(IF(h.traffic_type = :trafOtp AND c.type = :typeText, 1, NULL)) AS otp_text_complaints,
              COUNT(IF(h.traffic_type = :trafOtp AND c.type = :typeCall, 1, NULL)) AS otp_call_complaints,
              COUNT(IF(h.traffic_type = :trafOtp AND c.type = :typeCallMno, 1, NULL)) AS otp_call_mno_complaints,
              COUNT(IF(h.traffic_type = :trafToBuyout AND c.type = :typeText, 1, NULL)) AS to_buyout_text_complaints,
              COUNT(IF(h.traffic_type = :trafToBuyout AND c.type = :typeCall, 1, NULL)) AS to_buyout_call_complaints,
              COUNT(IF(h.traffic_type = :trafToBuyout AND c.type = :typeCallMno, 1, NULL)) AS to_buyout_call_mno_complaints,
          
              c.date,
              c.hour,
              c.source_id,
              c.landing_id,
              c.operator_id,
              c.platform_id,
              c.landing_pay_type_id,
              0,
              hp.subid1,
              hp.subid2,
              c.stream_id,
              c.provider_id,
              c.country_id
            FROM {$this->getMainSchemaName()}.complains c
                   INNER JOIN {$this->getMainSchemaName()}.hit_params hp ON hp.hit_id = c.hit_id
                   INNER JOIN {$this->getMainSchemaName()}.hits h ON h.id = c.hit_id
          WHERE c.date >= :dateFrom 
            AND c.date <= :dateTo 
            AND c.user_id=:userId
            AND c.time <= :maxTime
            GROUP BY c.date, c.hour, c.source_id, c.landing_id, c.operator_id, c.platform_id, c.landing_pay_type_id, hp.subid1,
              hp.subid2) inside
                 LEFT JOIN {$this->getMainSchemaName()}.subid_glossary g1 ON g1.hash = MD5(inside.subid1)
                 LEFT JOIN {$this->getMainSchemaName()}.subid_glossary g2 ON g2.hash = MD5(inside.subid2)
          GROUP BY date, hour, source_id, landing_id, operator_id, platform_id, landing_pay_type_id, g1.id,
            g2.id
          
          ON DUPLICATE KEY UPDATE revshare_text_complaints = VALUES(revshare_text_complaints),
            revshare_call_complaints                       = VALUES(revshare_call_complaints),
            revshare_call_mno_complaints                   = VALUES(revshare_call_mno_complaints),
            otp_text_complaints                            = VALUES(otp_text_complaints),
            otp_call_complaints                            = VALUES(otp_call_complaints),
            otp_call_mno_complaints                        = VALUES(otp_call_mno_complaints),
            to_buyout_text_complaints                      = VALUES(to_buyout_text_complaints),
            to_buyout_call_complaints                      = VALUES(to_buyout_call_complaints),
            to_buyout_call_mno_complaints                  = VALUES(to_buyout_call_mno_complaints);
        ")
        ->bindValue(':dateFrom', $this->cfg->getDateFrom(), \PDO::PARAM_STR)
        ->bindValue(':dateTo', $this->cfg->getDateTo(), \PDO::PARAM_STR)
        ->bindValue(':maxTime', $this->cfg->getMaxTime(), \PDO::PARAM_INT)
        ->bindValue(':userId', $userId, \PDO::PARAM_INT)
        ->bindValue(':trafRevshare', Landing::TRAFFIC_TYPE_REVSHARE, \PDO::PARAM_INT)
        ->bindValue(':trafToBuyout', Landing::TRAFFIC_TYPE_CPA, \PDO::PARAM_INT)
        ->bindValue(':trafOtp', Landing::TRAFFIC_TYPE_ONETIME, \PDO::PARAM_INT)
        ->bindValue(':typeText', Complain::TYPE_TEXT, \PDO::PARAM_INT)
        ->bindValue(':typeCall', Complain::TYPE_CALL, \PDO::PARAM_INT)
        ->bindValue(':typeCallMno', Complain::TYPE_CALL_MNO, \PDO::PARAM_INT)
        ->execute();
    }


  }
}
