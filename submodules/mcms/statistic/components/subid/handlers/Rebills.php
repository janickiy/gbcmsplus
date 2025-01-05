<?php

namespace mcms\statistic\components\subid\handlers;


use mcms\statistic\components\subid\BaseHandler;
use Yii;
use yii\db\Expression;
use yii\db\Query;
use kak\clickhouse\Query as ClickhouseQuery;

/**
 * Заполнение данных о ребилах
 */
class Rebills extends BaseHandler
{
  /**
   * @throws \yii\base\InvalidConfigException
   * @throws \yii\db\Exception
   */
  public function run()
  {
//    foreach ($this->cfg->getUserIds() as $userId) {
//      $this->log("$userId|");
//      Yii::$app->sdb->createCommand("
//        /** @lang MySQL */
//        INSERT INTO statistic_user_$userId (revshare_rebills, rejected_rebills, buyout_rebills,
//        revshare_res_profit_rub, revshare_res_profit_usd, revshare_res_profit_eur,
//        rejected_res_profit_rub, rejected_res_profit_usd, rejected_res_profit_eur,
//        buyout_res_profit_rub, buyout_res_profit_usd, buyout_res_profit_eur,
//        revshare_partner_profit_rub, revshare_partner_profit_usd, revshare_partner_profit_eur,
//        date, hour, source_id, landing_id, operator_id, platform_id, landing_pay_type_id, is_fake, subid1_id, subid2_id)
//        SELECT
//          NULLIF(SUM(revshare_rebills), 0) AS revshare_rebills,
//          NULLIF(SUM(rejected_rebills), 0) AS rejected_rebills,
//          NULLIF(SUM(buyout_rebills), 0) AS buyout_rebills,
//
//          NULLIF(SUM(revshare_res_profit_rub), 0) AS revshare_res_profit_rub,
//          NULLIF(SUM(revshare_res_profit_usd), 0) AS revshare_res_profit_usd,
//          NULLIF(SUM(revshare_res_profit_eur), 0) AS revshare_res_profit_eur,
//
//          NULLIF(SUM(rejected_res_profit_rub), 0) AS rejected_res_profit_rub,
//          NULLIF(SUM(rejected_res_profit_usd), 0) AS rejected_res_profit_usd,
//          NULLIF(SUM(rejected_res_profit_eur), 0) AS rejected_res_profit_eur,
//
//          NULLIF(SUM(buyout_res_profit_rub), 0) AS buyout_res_profit_rub,
//          NULLIF(SUM(buyout_res_profit_usd), 0) AS buyout_res_profit_usd,
//          NULLIF(SUM(buyout_res_profit_eur), 0) AS buyout_res_profit_eur,
//
//          NULLIF(SUM(revshare_partner_profit_rub), 0) AS revshare_partner_profit_rub,
//          NULLIF(SUM(revshare_partner_profit_usd), 0) AS revshare_partner_profit_usd,
//          NULLIF(SUM(revshare_partner_profit_eur), 0) AS revshare_partner_profit_eur,
//
//          `date`,
//          `hour`,
//          `source_id`,
//          `landing_id`,
//          `operator_id`,
//          `platform_id`,
//          `landing_pay_type_id`,
//          0 AS is_fake,
//          sg_1.`id` AS subid1_id,
//          sg_2.`id` AS subid2_id
//        FROM (
//          SELECT
//            COUNT(IF(h.traffic_type = 1, sr.id, NULL)) AS revshare_rebills,
//            COUNT(IF(h.traffic_type = 2 AND ss.id IS NULL, sr.id, NULL)) AS rejected_rebills,
//            COUNT(IF(h.traffic_type = 2 AND ss.id IS NOT NULL, sr.id, NULL)) AS buyout_rebills,
//
//            SUM(IF(h.traffic_type = 1, sr.reseller_profit_rub, 0)) AS revshare_res_profit_rub,
//            SUM(IF(h.traffic_type = 1, sr.reseller_profit_usd, 0)) AS revshare_res_profit_usd,
//            SUM(IF(h.traffic_type = 1, sr.reseller_profit_eur, 0)) AS revshare_res_profit_eur,
//
//            SUM(IF(h.traffic_type = 2 AND ss.id IS NULL, sr.reseller_profit_rub, 0)) AS rejected_res_profit_rub,
//            SUM(IF(h.traffic_type = 2 AND ss.id IS NULL, sr.reseller_profit_usd, 0)) AS rejected_res_profit_usd,
//            SUM(IF(h.traffic_type = 2 AND ss.id IS NULL, sr.reseller_profit_eur, 0)) AS rejected_res_profit_eur,
//
//            SUM(IF(h.traffic_type = 2 AND ss.id IS NOT NULL, sr.reseller_profit_rub, 0)) AS buyout_res_profit_rub,
//            SUM(IF(h.traffic_type = 2 AND ss.id IS NOT NULL, sr.reseller_profit_usd, 0)) AS buyout_res_profit_usd,
//            SUM(IF(h.traffic_type = 2 AND ss.id IS NOT NULL, sr.reseller_profit_eur, 0)) AS buyout_res_profit_eur,
//
//            SUM(IF(h.traffic_type = 1, sr.profit_rub, 0)) AS revshare_partner_profit_rub,
//            SUM(IF(h.traffic_type = 1, sr.profit_usd, 0)) AS revshare_partner_profit_usd,
//            SUM(IF(h.traffic_type = 1, sr.profit_eur, 0)) AS revshare_partner_profit_eur,
//
//            sr.`date`,
//            sr.`hour`,
//            sr.`source_id`,
//            sr.`landing_id`,
//            sr.`operator_id`,
//            sr.`platform_id`,
//            sr.`landing_pay_type_id`,
//            hp.`subid1`,
//            hp.`subid2`
//          FROM {$this->getMainSchemaName()}.`subscription_rebills` sr
//            LEFT JOIN {$this->getMainSchemaName()}.`sold_subscriptions` ss
//              ON sr.hit_id = ss.hit_id
//            LEFT JOIN {$this->getMainSchemaName()}.hits h
//              ON h.id = sr.hit_id
//            LEFT JOIN {$this->getMainSchemaName()}.hit_params hp
//              ON h.id = hp.hit_id
//          WHERE sr.date >= :dateFrom
//            AND sr.date <= :dateTo
//            AND h.source_id IN (SELECT id FROM {$this->getMainSchemaName()}.sources WHERE user_id = :userId)
//            AND sr.time <= :maxTime
//          GROUP BY
//            sr.`date`,
//            sr.`hour`,
//            sr.`source_id`,
//            sr.`landing_id`,
//            sr.`operator_id`,
//            sr.`platform_id`,
//            sr.`landing_pay_type_id`,
//            hp.`subid1`,
//            hp.`subid2`
//        ) inside
//               LEFT JOIN {$this->getMainSchemaName()}.subid_glossary sg_1 ON sg_1.hash = MD5(inside.subid1)
//               LEFT JOIN {$this->getMainSchemaName()}.subid_glossary sg_2 ON sg_2.hash = MD5(inside.subid2)
//        GROUP BY date, hour, source_id, landing_id, operator_id, platform_id, landing_pay_type_id, sg_1.id,
//          sg_2.id
//
//          ON DUPLICATE KEY UPDATE
//            revshare_rebills                = VALUES(revshare_rebills),
//            rejected_rebills                = VALUES(rejected_rebills),
//            buyout_rebills                  = VALUES(buyout_rebills),
//            revshare_res_profit_rub                = VALUES(revshare_res_profit_rub),
//            revshare_res_profit_usd         = VALUES(revshare_res_profit_usd),
//            revshare_res_profit_eur         = VALUES(revshare_res_profit_eur),
//
//            rejected_res_profit_rub         = VALUES(rejected_res_profit_rub),
//            rejected_res_profit_usd         = VALUES(rejected_res_profit_usd),
//            rejected_res_profit_eur         = VALUES(rejected_res_profit_eur),
//
//            buyout_res_profit_rub           = VALUES(buyout_res_profit_rub),
//            buyout_res_profit_usd           = VALUES(buyout_res_profit_usd),
//            buyout_res_profit_eur           = VALUES(buyout_res_profit_eur),
//
//            revshare_partner_profit_rub     = VALUES(revshare_partner_profit_rub),
//            revshare_partner_profit_usd     = VALUES(revshare_partner_profit_usd),
//            revshare_partner_profit_eur     = VALUES(revshare_partner_profit_eur)
//        ")
//        ->bindValue(':dateFrom', $this->cfg->getDateFrom(), \PDO::PARAM_STR)
//        ->bindValue(':dateTo', $this->cfg->getDateTo(), \PDO::PARAM_STR)
//        ->bindValue(':maxTime', $this->cfg->getMaxTime(), \PDO::PARAM_INT)
//        ->bindValue(':userId', $userId, \PDO::PARAM_INT)
//        ->execute();
//    }

    foreach ($this->cfg->getUserIds() as $userId) {
      $this->log("$userId|");

      $fromTime = $this->cfg->getTimeFrom();
      $dateDiff = ceil((time() - $fromTime) / 43200);
      for ($i = 0; $i < $dateDiff; $i++) {
        $from = $fromTime + (43200 * $i);
        $to = $from + 43199;
        $userData = $this->getUserData($userId, date('Y-m-d', $from), $fromTime, $to);
        if (count($userData) === 0) break;

        $insertSql = Yii::$app->sdb->createCommand()
          ->batchInsert("statistic_user_$userId", [
            'revshare_rebills',
            'rejected_rebills',
            'buyout_rebills',
            'revshare_res_profit_rub',
            'revshare_res_profit_usd',
            'revshare_res_profit_eur',
            'rejected_res_profit_rub',
            'rejected_res_profit_usd',
            'rejected_res_profit_eur',
            'buyout_res_profit_rub',
            'buyout_res_profit_usd',
            'buyout_res_profit_eur',
            'revshare_partner_profit_rub',
            'revshare_partner_profit_usd',
            'revshare_partner_profit_eur',
            'date',
            'hour',
            'source_id',
            'landing_id',
            'operator_id',
            'platform_id',
            'landing_pay_type_id',
            'is_fake',
            'subid1_id',
            'subid2_id',
          ], $userData);

        $insertSql .= "
        ON DUPLICATE KEY UPDATE 
          revshare_rebills = VALUES(revshare_rebills),
          rejected_rebills = VALUES(rejected_rebills),
          buyout_rebills = VALUES(buyout_rebills),
          revshare_res_profit_rub = VALUES(revshare_res_profit_rub),
          revshare_res_profit_usd = VALUES(revshare_res_profit_usd),
          revshare_res_profit_eur = VALUES(revshare_res_profit_eur),
          rejected_res_profit_rub = VALUES(rejected_res_profit_rub),
          rejected_res_profit_usd = VALUES(rejected_res_profit_usd),
          rejected_res_profit_eur = VALUES(rejected_res_profit_eur),
          buyout_res_profit_rub = VALUES(buyout_res_profit_rub),
          buyout_res_profit_usd = VALUES(buyout_res_profit_usd),
          buyout_res_profit_eur = VALUES(buyout_res_profit_eur),
          revshare_partner_profit_rub = VALUES(revshare_partner_profit_rub),
          revshare_partner_profit_usd = VALUES(revshare_partner_profit_usd),
          revshare_partner_profit_eur = VALUES(revshare_partner_profit_eur)
      ";

        Yii::$app->sdb->createCommand($insertSql)->execute();
      }
    }
  }

  private function getUserData($userId, $forDate, $from, $to)
  {
    $userSources = (new Query)
      ->select('id')
      ->from('sources')
      ->where(['user_id' => $userId])
      ->column();

    $subquery = (new ClickhouseQuery())
      ->select([
        'revshare_rebills' => 'countIf(sr.hit_id, h.traffic_type = 1)',
        'rejected_rebills' => 'countIf(sr.hit_id, h.traffic_type = 2 AND ss.hit_id = 0)',
        'buyout_rebills' => 'countIf(sr.hit_id, h.traffic_type = 2 AND ss.hit_id != 0)',
        'revshare_res_profit_rub' => 'sumIf(sr.reseller_profit_rub, h.traffic_type = 1)',
        'revshare_res_profit_usd' => 'sumIf(sr.reseller_profit_usd, h.traffic_type = 1)',
        'revshare_res_profit_eur' => 'sumIf(sr.reseller_profit_eur, h.traffic_type = 1)',
        'rejected_res_profit_rub' => 'sumIf(sr.reseller_profit_rub, h.traffic_type = 2 AND ss.hit_id = 0)',
        'rejected_res_profit_usd' => 'sumIf(sr.reseller_profit_usd, h.traffic_type = 2 AND ss.hit_id = 0)',
        'rejected_res_profit_eur' => 'sumIf(sr.reseller_profit_eur, h.traffic_type = 2 AND ss.hit_id = 0)',
        'buyout_res_profit_rub' => 'sumIf(sr.reseller_profit_rub, h.traffic_type = 2 AND ss.hit_id != 0)',
        'buyout_res_profit_usd' => 'sumIf(sr.reseller_profit_usd, h.traffic_type = 2 AND ss.hit_id != 0)',
        'buyout_res_profit_eur' => 'sumIf(sr.reseller_profit_eur, h.traffic_type = 2 AND ss.hit_id != 0)',
        'revshare_partner_profit_rub' => 'sumIf(sr.profit_rub, h.traffic_type == 1)',
        'revshare_partner_profit_usd' => 'sumIf(sr.profit_usd, h.traffic_type == 1)',
        'revshare_partner_profit_eur' => 'sumIf(sr.profit_eur, h.traffic_type == 1)',
        'date' => 'sr.date',
        'hour' => 'sr.hour',
        'source_id' => 'sr.source_id',
        'landing_id' => 'sr.landing_id',
        'operator_id' => 'sr.operator_id',
        'platform_id' => 'sr.platform_id',
        'landing_pay_type_id' => 'sr.landing_pay_type_id',
        'subid1_id' => 'h.subid1_id',
        'subid2_id' => 'h.subid2_id',
      ])
      ->from(['sr' => 'subscription_rebills'])
      /**
       * LEFT JOIN `subscriptions` s ON s.hit_id = sr.hit_id
       * LEFT JOIN `sold_subscriptions` ss ON sr.hit_id = ss.hit_id
       * LEFT JOIN hits h ON h.hit_id = sr.hit_id
       */
      ->leftJoin(['ss' => 'sold_subscriptions'], 'sr.hit_id = ss.hit_id')
      ->leftJoin(['h' => 'hits'], 'h.hit_id = sr.hit_id')
      // sr.date >= '2018-01-01'
      ->where([
        'and',
//        ['<=', 'sr.date', $this->cfg->getDateTo()],
        ['=', 'sr.date', $forDate],
        ['<=', 'sr.timestamp', $this->cfg->getMaxTime()],
        ['>=', 'sr.timestamp', $from],
        ['<=', 'sr.timestamp', $to],
        ['h.source_id' => $userSources],
      ])
      ->groupBy([
        'sr.date',
        'sr.hour',
        'sr.source_id',
        'sr.landing_id',
        'sr.operator_id',
        'sr.platform_id',
        'sr.landing_pay_type_id',
        'h.subid1_id',
        'h.subid2_id'
      ]);

    $mainQuery = (new ClickhouseQuery)
      ->select([
        'revshare_rebills' => 'NULLIF(sum(revshare_rebills), 0)',
        'rejected_rebills' => 'NULLIF(sum(rejected_rebills), 0)',
        'buyout_rebills' => 'NULLIF(sum(buyout_rebills), 0)',
        'revshare_res_profit_rub' => 'NULLIF(sum(revshare_res_profit_rub), 0)',
        'revshare_res_profit_usd' => 'NULLIF(sum(revshare_res_profit_usd), 0)',
        'revshare_res_profit_eur' => 'NULLIF(sum(revshare_res_profit_eur), 0)',
        'rejected_res_profit_rub' => 'NULLIF(sum(rejected_res_profit_rub), 0)',
        'rejected_res_profit_usd' => 'NULLIF(sum(rejected_res_profit_usd), 0)',
        'rejected_res_profit_eur' => 'NULLIF(sum(rejected_res_profit_eur), 0)',
        'buyout_res_profit_rub' => 'NULLIF(sum(buyout_res_profit_rub), 0)',
        'buyout_res_profit_usd' => 'NULLIF(sum(buyout_res_profit_usd), 0)',
        'buyout_res_profit_eur' => 'NULLIF(sum(buyout_res_profit_eur), 0)',
        'revshare_partner_profit_rub' => 'NULLIF(sum(revshare_partner_profit_rub), 0)',
        'revshare_partner_profit_usd' => 'NULLIF(sum(revshare_partner_profit_usd), 0)',
        'revshare_partner_profit_eur' => 'NULLIF(sum(revshare_partner_profit_eur), 0)',
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
      ])
    ;

    return $mainQuery->createCommand(Yii::$app->clickhouse)->queryAll();
  }

}