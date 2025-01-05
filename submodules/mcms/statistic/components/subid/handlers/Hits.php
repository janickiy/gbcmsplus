<?php

namespace mcms\statistic\components\subid\handlers;

use mcms\statistic\components\subid\BaseHandler;
use Yii;
use yii\db\Expression;
use yii\db\Query;
use kak\clickhouse\Query as ClickhouseQuery;

/**
 */
class Hits extends BaseHandler
{
  public function run()
  {
//    foreach ($this->cfg->getUserIds() as $userId) {
//      $this->log("$userId|");
//      Yii::$app->sdb->createCommand("
//        /** @lang MySQL */
//        INSERT INTO statistic_user_$userId (hits, tb, uniques, tb_uniques, to_buyout_hits, to_buyout_tb, to_buyout_uniques, to_buyout_tb_uniques, revshare_hits,
//                              revshare_tb, revshare_uniques, revshare_tb_uniques, otp_hits, otp_tb, otp_uniques, otp_tb_uniques,
//                              date, hour, source_id, landing_id, operator_id, platform_id, landing_pay_type_id, is_fake,
//                              subid1_id, subid2_id)
//        SELECT
//                NULLIF(SUM(hits), 0),
//                NULLIF(SUM(tb), 0),
//                NULLIF(SUM(uniques), 0),
//                NULLIF(SUM(tb_uniques), 0),
//                NULLIF(SUM(to_buyout_hits), 0),
//                NULLIF(SUM(to_buyout_tb), 0),
//                NULLIF(SUM(to_buyout_uniques), 0),
//                NULLIF(SUM(to_buyout_tb_uniques), 0),
//                NULLIF(SUM(revshare_hits), 0),
//                NULLIF(SUM(revshare_tb), 0),
//                NULLIF(SUM(revshare_uniques), 0),
//                NULLIF(SUM(revshare_tb_uniques), 0),
//                NULLIF(SUM(otp_hits), 0),
//                NULLIF(SUM(otp_tb), 0),
//                NULLIF(SUM(otp_uniques), 0),
//                NULLIF(SUM(otp_tb_uniques), 0),
//                date,
//                hour,
//                source_id,
//                landing_id,
//                operator_id,
//                platform_id,
//                landing_pay_type_id,
//                0,
//                g1.id,
//                g2.id
//        FROM (
//          SELECT COUNT(1) AS hits,
//            COUNT(IF(h.is_tb > 0, 1, NULL)) AS tb,
//            COUNT(IF(h.is_unique = 1, 1, NULL)) AS uniques,
//            COUNT(IF(h.is_unique = 1 AND h.is_tb > 0, 1, NULL)) AS tb_uniques,
//            COUNT(IF(h.traffic_type = :trafToBuyout, 1, NULL)) AS to_buyout_hits,
//            COUNT(IF(h.traffic_type = :trafToBuyout AND h.is_tb > 0, 1, NULL)) AS to_buyout_tb,
//            COUNT(IF(h.traffic_type = :trafToBuyout AND h.is_unique = 1, 1, NULL)) AS to_buyout_uniques,
//            COUNT(IF(h.traffic_type = :trafToBuyout AND h.is_unique = 1 AND h.is_tb > 0, 1, NULL)) AS to_buyout_tb_uniques,
//            COUNT(IF(h.traffic_type = :trafRevshare, 1, NULL)) AS revshare_hits,
//            COUNT(IF(h.traffic_type = :trafRevshare AND h.is_tb > 0, 1, NULL)) AS revshare_tb,
//            COUNT(IF(h.traffic_type = :trafRevshare AND h.is_unique = 1, 1, NULL)) AS revshare_uniques,
//            COUNT(IF(h.traffic_type = :trafRevshare AND h.is_unique = 1 AND h.is_tb > 0, 1, NULL)) AS revshare_tb_uniques,
//            COUNT(IF(h.traffic_type = :trafOtp, 1, NULL)) AS otp_hits,
//            COUNT(IF(h.traffic_type = :trafOtp AND h.is_tb > 0, 1, NULL)) AS otp_tb,
//            COUNT(IF(h.traffic_type = :trafOtp AND h.is_unique = 1, 1, NULL)) AS otp_uniques,
//            COUNT(IF(h.traffic_type = :trafOtp AND h.is_unique = 1 AND h.is_tb > 0, 1, NULL)) AS otp_tb_uniques,
//
//            h.date,
//            h.hour,
//            h.source_id,
//            h.landing_id,
//            h.operator_id,
//            h.platform_id,
//            h.landing_pay_type_id,
//            0,
//            hp.subid1,
//            hp.subid2
//
//          FROM {$this->getMainSchemaName()}.hits h
//                 INNER JOIN {$this->getMainSchemaName()}.hit_params hp ON hp.hit_id = h.id
//          WHERE date >= :dateFrom
//            AND date <= :dateTo
//            AND h.source_id IN (SELECT id FROM {$this->getMainSchemaName()}.sources WHERE user_id = :userId)
//            AND h.time <= :maxTime
//          GROUP BY h.date, h.hour, h.source_id, h.landing_id, h.operator_id, h.platform_id, h.landing_pay_type_id, hp.subid1,
//            hp.subid2) inside
//               LEFT JOIN {$this->getMainSchemaName()}.subid_glossary g1 ON g1.hash = MD5(inside.subid1)
//               LEFT JOIN {$this->getMainSchemaName()}.subid_glossary g2 ON g2.hash = MD5(inside.subid2)
//        GROUP BY date, hour, source_id, landing_id, operator_id, platform_id, landing_pay_type_id, g1.id,
//          g2.id
//
//          ON DUPLICATE KEY UPDATE hits = VALUES(hits),
//              tb                         = VALUES(tb),
//              uniques                    = VALUES(uniques),
//              to_buyout_hits             = VALUES(to_buyout_hits),
//              to_buyout_tb               = VALUES(to_buyout_tb),
//              to_buyout_uniques          = VALUES(to_buyout_uniques),
//              revshare_hits              = VALUES(revshare_hits),
//
//              revshare_tb                = VALUES(revshare_tb),
//              revshare_uniques           = VALUES(revshare_uniques),
//              otp_hits                   = VALUES(otp_hits),
//              otp_tb                     = VALUES(otp_tb),
//              otp_uniques                = VALUES(otp_uniques)
//        ")
//        ->bindValue(':dateFrom', $this->cfg->getDateFrom(), \PDO::PARAM_STR)
//        ->bindValue(':dateTo', $this->cfg->getDateTo(), \PDO::PARAM_STR)
//        ->bindValue(':maxTime', $this->cfg->getMaxTime(), \PDO::PARAM_INT)
//        ->bindValue(':userId', $userId, \PDO::PARAM_INT)
//        ->bindValue(':trafRevshare', Landing::TRAFFIC_TYPE_REVSHARE, \PDO::PARAM_INT)
//        ->bindValue(':trafToBuyout', Landing::TRAFFIC_TYPE_CPA, \PDO::PARAM_INT)
//        ->bindValue(':trafOtp', Landing::TRAFFIC_TYPE_ONETIME, \PDO::PARAM_INT)
//        ->execute();
//    }


    foreach ($this->cfg->getUserIds() as $userId) {
      $this->log("$userId|");

      $fromTime = $this->cfg->getTimeFrom();
      $maxTime = $this->cfg->getMaxTime();

      while ($fromTime <= $maxTime) {
        // итерируемся по времени
        $toTime = $fromTime + 3600 * 12;
        $userData = $this->fetchUserData($userId, $fromTime, $toTime);

        if (count($userData) > 0) {
          $this->insertData($userId, $userData);
        }
        $fromTime = $toTime;
      }
    }
  }

  private function insertData($userId, $userData)
  {
    $insertSql = Yii::$app->sdb->createCommand()
      ->batchInsert("statistic_user_$userId", [
        'hits',
        'tb',
        'uniques',
        'tb_uniques',
        'to_buyout_hits',
        'to_buyout_tb',
        'to_buyout_uniques',
        'to_buyout_tb_uniques',
        'revshare_hits',
        'revshare_tb',
        'revshare_uniques',
        'revshare_tb_uniques',
        'otp_hits',
        'otp_tb',
        'otp_uniques',
        'otp_tb_uniques',
        'date',
        'hour',
        'source_id',
        'landing_id',
        'operator_id',
        'platform_id',
        'landing_pay_type_id',
        'is_fake',
        'subid1_id',
        'subid2_id'
      ], $userData)
      ->getSql();

    $insertSql .= "
      ON DUPLICATE KEY UPDATE 
        hits = VALUES(hits),
        tb = VALUES(tb),
        uniques = VALUES(uniques),
        to_buyout_hits = VALUES(to_buyout_hits),
        to_buyout_tb = VALUES(to_buyout_tb),
        to_buyout_uniques = VALUES(to_buyout_uniques),
        revshare_hits = VALUES(revshare_hits),
        revshare_tb = VALUES(revshare_tb),
        revshare_uniques = VALUES(revshare_uniques),
        otp_hits = VALUES(otp_hits),
        otp_tb = VALUES(otp_tb),
        otp_uniques = VALUES(otp_uniques)
      ";

    Yii::$app->sdb->createCommand($insertSql)->execute();
  }

  private function fetchUserData($userId, $fromTime, $toTime)
  {

    /**
     * SELECT
     * coalesce(SUM(hits), 0),
     * coalesce(SUM(tb), 0),
     * coalesce(SUM(uniques), 0),
     * coalesce(SUM(tb_uniques), 0),
     * coalesce(SUM(to_buyout_hits), 0),
     * coalesce(SUM(to_buyout_tb), 0),
     * coalesce(SUM(to_buyout_uniques), 0),
     * coalesce(SUM(to_buyout_tb_uniques), 0),
     * coalesce(SUM(revshare_hits), 0),
     * coalesce(SUM(revshare_tb), 0),
     * coalesce(SUM(revshare_uniques), 0),
     * coalesce(SUM(revshare_tb_uniques), 0),
     * coalesce(SUM(otp_hits), 0),
     * coalesce(SUM(otp_tb), 0),
     * coalesce(SUM(otp_uniques), 0),
     * coalesce(SUM(otp_tb_uniques), 0),
     * date,
     * hour,
     * source_id,
     * landing_id,
     * operator_id,
     * platform_id,
     * landing_pay_type_id,
     * 0,
     * subid1,
     * subid2
     * FROM (
     * SELECT COUNT(1) AS hits,
     * COUNT(if(h.is_tb > 0, 1, NULL)) AS tb,
     * COUNT(if(h.is_unique = 1, 1, NULL)) AS uniques,
     * COUNT(if(h.is_unique = 1 AND h.is_tb > 0, 1, NULL)) AS tb_uniques,
     * COUNT(if(h.is_tb > 0, 1, NULL)) AS tb,
     * COUNT(if(h.is_unique = 1, 1, NULL)) AS uniques,
     * COUNT(if(h.is_unique = 1 AND h.is_tb > 0, 1, NULL)) AS tb_uniques,
     * COUNT(if(h.traffic_type = 2, 1, NULL)) AS to_buyout_hits,
     * COUNT(if(h.traffic_type = 2 AND h.is_tb > 0, 1, NULL)) AS to_buyout_tb,
     * COUNT(if(h.traffic_type = 2 AND h.is_unique = 1, 1, NULL)) AS to_buyout_uniques,
     * COUNT(if(h.traffic_type = 2 AND h.is_unique = 1 AND h.is_tb > 0, 1, NULL)) AS to_buyout_tb_uniques,
     * COUNT(if(h.traffic_type = 1, 1, NULL)) AS revshare_hits,
     * COUNT(if(h.traffic_type = 1 AND h.is_tb > 0, 1, NULL)) AS revshare_tb,
     * COUNT(if(h.traffic_type = 1 AND h.is_unique = 1, 1, NULL)) AS revshare_uniques,
     * COUNT(if(h.traffic_type = 1 AND h.is_unique = 1 AND h.is_tb > 0, 1, NULL)) AS revshare_tb_uniques,
     * COUNT(if(h.traffic_type = 3, 1, NULL)) AS otp_hits,
     * COUNT(if(h.traffic_type = 3 AND h.is_tb > 0, 1, NULL)) AS otp_tb,
     * COUNT(if(h.traffic_type = 3 AND h.is_unique = 1, 1, NULL)) AS otp_uniques,
     * COUNT(if(h.traffic_type = 3 AND h.is_unique = 1 AND h.is_tb > 0, 1, NULL)) AS otp_tb_uniques,
     *
     * h.date,
     * h.hour,
     * h.source_id,
     * h.landing_id,
     * h.operator_id,
     * h.platform_id,
     * h.landing_pay_type_id,
     * 0,
     * h.subid1,
     * h.subid2
     *
     * FROM hits h
     *
     * WHERE h.date >= '2018-01-01'
     * AND h.date <= '2020-10-16'
     * AND h.source_id IN (11,12,13,14,15,40,41,42)
     * AND h.timestamp <= 1602838002
     * GROUP BY h.date, h.hour, h.source_id, h.landing_id, h.operator_id, h.platform_id, h.landing_pay_type_id, h.subid1, h.subid2
     * ) as inside
     *
     * GROUP BY date, hour, source_id, landing_id, operator_id, platform_id, landing_pay_type_id, subid1, subid2
     */

    $userSources = (new Query)
      ->select('id')
      ->from('sources')
      ->where(['user_id' => $userId])
      ->column();

    $subquery = (new ClickhouseQuery)
      ->select([
        'hits' => 'count(1)',
        'tb' => 'countIf(h.is_tb > 0)',
        'uniques' => 'countIf(h.is_unique = 1)',
        'tb_uniques' => 'countIf(h.is_unique = 1 AND h.is_tb > 0)',
        'to_buyout_hits' => 'countIf(h.traffic_type = 2)',
        'to_buyout_tb' => 'countIf(h.traffic_type = 2 AND h.is_tb > 0)',
        'to_buyout_uniques' => 'countIf(h.traffic_type = 2 AND h.is_unique = 1)',
        'to_buyout_tb_uniques' => 'countIf(h.traffic_type = 2 AND h.is_unique = 1 AND h.is_tb > 0)',
        'revshare_hits' => 'countIf(h.traffic_type = 1)',
        'revshare_tb' => 'countIf(h.traffic_type = 1 AND h.is_tb > 0)',
        'revshare_uniques' => 'countIf(h.traffic_type = 1 AND h.is_unique = 1)',
        'revshare_tb_uniques' => 'countIf(h.traffic_type = 1 AND h.is_unique = 1 AND h.is_tb > 0)',
        'otp_hits' => 'countIf(h.traffic_type = 3)',
        'otp_tb' => 'countIf(h.traffic_type = 3 AND h.is_tb > 0)',
        'otp_uniques' => 'countIf(h.traffic_type = 3 AND h.is_unique = 1)',
        'otp_tb_uniques' => 'countIf(h.traffic_type = 3 AND h.is_unique = 1 AND h.is_tb > 0)',
        'date' => 'h.date',
        'hour' => 'h.hour',
        'source_id' => 'h.source_id',
        'landing_id' => 'h.landing_id',
        'operator_id' => 'h.operator_id',
        'platform_id' => 'h.platform_id',
        'landing_pay_type_id' => 'h.landing_pay_type_id',
        'subid1_id' => 'h.subid1_id',
        'subid2_id' => 'h.subid2_id'
      ])
      ->from(['h' => 'hits'])
      /**
       * h.date >= '2018-01-01'
       * AND h.date <= '2020-10-16'
       * AND h.source_id IN (11,12,13,14,15,40,41,42)
       * AND h.timestamp <= 1602838002
       */
      ->where([
        'and',
        ['>=', 'h.date', $this->cfg->getDateFrom()],
        ['<=', 'h.date', $this->cfg->getDateTo()],
        ['<=', 'h.timestamp', $toTime],
        ['>=', 'h.timestamp', $fromTime],
        ['h.source_id' => $userSources]
      ])
      ->groupBy([
        'h.date',
        'h.hour',
        'h.source_id',
        'h.landing_id',
        'h.operator_id',
        'h.platform_id',
        'h.landing_pay_type_id',
        'h.subid1_id',
        'h.subid2_id'
      ]);

    $mainQuery = (new ClickhouseQuery())
      ->select([
        'coalesce(sum(hits), 0)',
        'coalesce(sum(tb), 0)',
        'coalesce(sum(uniques), 0)',
        'coalesce(sum(tb_uniques), 0)',
        'coalesce(sum(to_buyout_hits), 0)',
        'coalesce(sum(to_buyout_tb), 0)',
        'coalesce(sum(to_buyout_uniques), 0)',
        'coalesce(sum(to_buyout_tb_uniques), 0)',
        'coalesce(sum(revshare_hits), 0)',
        'coalesce(sum(revshare_tb), 0)',
        'coalesce(sum(revshare_uniques), 0)',
        'coalesce(sum(revshare_tb_uniques), 0)',
        'coalesce(sum(otp_hits), 0)',
        'coalesce(sum(otp_tb), 0)',
        'coalesce(sum(otp_uniques), 0)',
        'coalesce(sum(otp_tb_uniques), 0)',
        'date',
        'hour',
        'source_id',
        'landing_id',
        'operator_id',
        'platform_id',
        'landing_pay_type_id',
        new Expression('0 as `is_fake`'),
        'subid1_id',
        'subid2_id'
      ])
      ->from(['inside' => $subquery])
      ->groupBy([
        'date',
        'hour',
        'source_id',
        'landing_id',
        'operator_id',
        'platform_id',
        'landing_pay_type_id',
        'subid1_id',
        'subid2_id'
      ]);

    return $mainQuery->createCommand(Yii::$app->clickhouse)->queryAll();
  }
}
