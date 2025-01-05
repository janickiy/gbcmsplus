<?php

namespace mcms\statistic\components\subid\handlers;


use mcms\statistic\components\subid\BaseHandler;
use Yii;
use yii\db\Expression;
use yii\db\Query;
use kak\clickhouse\Query as ClickhouseQuery;

/**
 * Заполнение данных о ребилах 24
 */
class Rebills24 extends BaseHandler
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
//        INSERT INTO statistic_user_$userId (revshare_rebills24, rejected_rebills24, buyout_rebills24,
//        revshare_partner_profit24_rub, revshare_partner_profit24_usd, revshare_partner_profit24_eur,
//        buyout_partner_profit24_rub, buyout_partner_profit24_usd, buyout_partner_profit24_eur,
//        rejected_partner_profit24_rub, rejected_partner_profit24_usd, rejected_partner_profit24_eur,
//        date, hour, source_id, landing_id, operator_id, platform_id, landing_pay_type_id, is_fake, subid1_id, subid2_id)
//        SELECT
//          NULLIF(SUM(revshare_rebills24), 0) AS revshare_rebills24,
//          NULLIF(SUM(rejected_rebills24), 0) AS rejected_rebills24,
//          NULLIF(SUM(buyout_rebills24), 0) AS buyout_rebills24,
//
//          NULLIF(SUM(revshare_partner_profit24_rub), 0) AS revshare_partner_profit24_rub,
//          NULLIF(SUM(revshare_partner_profit24_usd), 0) AS revshare_partner_profit24_usd,
//          NULLIF(SUM(revshare_partner_profit24_eur), 0) AS revshare_partner_profit24_eur,
//
//          NULLIF(SUM(buyout_partner_profit24_rub), 0) AS buyout_partner_profit24_rub,
//          NULLIF(SUM(buyout_partner_profit24_usd), 0) AS buyout_partner_profit24_usd,
//          NULLIF(SUM(buyout_partner_profit24_eur), 0) AS buyout_partner_profit24_eur,
//
//          NULLIF(SUM(rejected_partner_profit24_rub), 0) AS rejected_partner_profit24_rub,
//          NULLIF(SUM(rejected_partner_profit24_usd), 0) AS rejected_partner_profit24_usd,
//          NULLIF(SUM(rejected_partner_profit24_eur), 0) AS rejected_partner_profit24_eur,
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
//            COUNT(IF(h.traffic_type = 1, s.id, NULL)) AS revshare_rebills24,
//            COUNT(IF(h.traffic_type = 2 AND ss.id IS NULL, s.id, NULL)) AS rejected_rebills24,
//            COUNT(IF(h.traffic_type = 2 AND ss.id IS NOT NULL, s.id, NULL)) AS buyout_rebills24,
//
//            SUM(IF(h.traffic_type = 1, sr.profit_rub, 0)) AS revshare_partner_profit24_rub,
//            SUM(IF(h.traffic_type = 1, sr.profit_usd, 0)) AS revshare_partner_profit24_usd,
//            SUM(IF(h.traffic_type = 1, sr.profit_eur, 0)) AS revshare_partner_profit24_eur,
//
//            SUM(IF(h.traffic_type = 2 AND ss.id IS NOT NULL, sr.profit_rub, 0)) AS buyout_partner_profit24_rub,
//            SUM(IF(h.traffic_type = 2 AND ss.id IS NOT NULL, sr.profit_usd, 0)) AS buyout_partner_profit24_usd,
//            SUM(IF(h.traffic_type = 2 AND ss.id IS NOT NULL, sr.profit_eur, 0)) AS buyout_partner_profit24_eur,
//
//            SUM(IF(h.traffic_type = 2 AND ss.id IS NULL, sr.profit_rub, 0)) AS rejected_partner_profit24_rub,
//            SUM(IF(h.traffic_type = 2 AND ss.id IS NULL, sr.profit_usd, 0)) AS rejected_partner_profit24_usd,
//            SUM(IF(h.traffic_type = 2 AND ss.id IS NULL, sr.profit_eur, 0)) AS rejected_partner_profit24_eur,
//
//            s.`date`,
//            s.`hour`,
//            s.`source_id`,
//            s.`landing_id`,
//            s.`operator_id`,
//            s.`platform_id`,
//            s.`landing_pay_type_id`,
//            hp.`subid1`,
//            hp.`subid2`
//          FROM {$this->getMainSchemaName()}.`subscription_rebills` AS sr
//            LEFT JOIN {$this->getMainSchemaName()}.`subscriptions` s
//              ON s.hit_id = sr.hit_id AND
//           (({$this->getTrialOperatorsInCondition('s.operator_id', true)} AND s.date = sr.date) OR ({$this->getTrialOperatorsInCondition('s.operator_id')} AND s.date = date_add(sr.date, INTERVAL -1 DAY)))
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
//            s.`date`,
//            s.`hour`,
//            s.`source_id`,
//            s.`landing_id`,
//            s.`operator_id`,
//            s.`platform_id`,
//            s.`landing_pay_type_id`,
//            hp.`subid1`,
//            hp.`subid2`
//        ) inside
//               LEFT JOIN {$this->getMainSchemaName()}.subid_glossary sg_1 ON sg_1.hash = MD5(inside.subid1)
//               LEFT JOIN {$this->getMainSchemaName()}.subid_glossary sg_2 ON sg_2.hash = MD5(inside.subid2)
//        GROUP BY date, hour, source_id, landing_id, operator_id, platform_id, landing_pay_type_id, sg_1.id,
//          sg_2.id
//
//          ON DUPLICATE KEY UPDATE
//            revshare_rebills24                = VALUES(revshare_rebills24),
//            rejected_rebills24                = VALUES(rejected_rebills24),
//            buyout_rebills24                  = VALUES(buyout_rebills24),
//
//            revshare_partner_profit24_rub     = VALUES(revshare_partner_profit24_rub),
//            revshare_partner_profit24_usd     = VALUES(revshare_partner_profit24_usd),
//            revshare_partner_profit24_eur     = VALUES(revshare_partner_profit24_eur),
//
//            buyout_partner_profit24_rub       = VALUES(buyout_partner_profit24_rub),
//            buyout_partner_profit24_usd       = VALUES(buyout_partner_profit24_usd),
//            buyout_partner_profit24_eur       = VALUES(buyout_partner_profit24_eur),
//
//            rejected_partner_profit24_rub     = VALUES(rejected_partner_profit24_rub),
//            rejected_partner_profit24_usd     = VALUES(rejected_partner_profit24_usd),
//            rejected_partner_profit24_eur     = VALUES(rejected_partner_profit24_eur)
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
        $to = $from + 43200;

        $data= $this->getUserData($userId, $fromTime, $to);
        if (count($data) === 0) break;

        $insertQuery = Yii::$app->sdb->createCommand()
          ->batchInsert("statistic_user_$userId", [
            'revshare_rebills24',
            'rejected_rebills24',
            'buyout_rebills24',
            'revshare_partner_profit24_rub',
            'revshare_partner_profit24_usd',
            'revshare_partner_profit24_eur',
            'buyout_partner_profit24_rub',
            'buyout_partner_profit24_usd',
            'buyout_partner_profit24_eur',
            'rejected_partner_profit24_rub',
            'rejected_partner_profit24_usd',
            'rejected_partner_profit24_eur',
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
          ], $data)
        ;

        $insertQuery .= "
        ON DUPLICATE KEY UPDATE 
          revshare_rebills24                = VALUES(revshare_rebills24),
          rejected_rebills24                = VALUES(rejected_rebills24),
          buyout_rebills24                  = VALUES(buyout_rebills24),
          
          revshare_partner_profit24_rub     = VALUES(revshare_partner_profit24_rub),
          revshare_partner_profit24_usd     = VALUES(revshare_partner_profit24_usd),
          revshare_partner_profit24_eur     = VALUES(revshare_partner_profit24_eur),
          
          buyout_partner_profit24_rub       = VALUES(buyout_partner_profit24_rub),
          buyout_partner_profit24_usd       = VALUES(buyout_partner_profit24_usd),
          buyout_partner_profit24_eur       = VALUES(buyout_partner_profit24_eur),
          
          rejected_partner_profit24_rub     = VALUES(rejected_partner_profit24_rub),
          rejected_partner_profit24_usd     = VALUES(rejected_partner_profit24_usd),
          rejected_partner_profit24_eur     = VALUES(rejected_partner_profit24_eur)
      ";

        Yii::$app->sdb->createCommand($insertQuery)->execute();
      }
    }
  }

  private function getUserData($userId, $fromTime, $toTime)
  {
    $userSources = (new Query)
      ->select('id')
      ->from('sources')
      ->where(['user_id' => $userId])
      ->column();

    $subquery = (new ClickhouseQuery)
      ->select([
        'revshare_rebills24' => 'countIf(s.hit_id, h.traffic_type = 1)',
        'rejected_rebills24' => 'countIf(s.hit_id, h.traffic_type = 2 AND ss.hit_id IS NULL)',
        'buyout_rebills24' => 'countIf(s.hit_id, h.traffic_type = 2 AND ss.hit_id IS NOT NULL)',
        'revshare_partner_profit24_rub' => 'sumIf(sr.profit_rub, h.traffic_type = 1)',
        'revshare_partner_profit24_usd' => 'sumIf(sr.profit_usd, h.traffic_type = 1)',
        'revshare_partner_profit24_eur' => 'sumIf(sr.profit_eur, h.traffic_type = 1)',
        'buyout_partner_profit24_rub' => 'sumIf(sr.profit_rub, h.traffic_type = 2 AND ss.hit_id IS NOT NULL)',
        'buyout_partner_profit24_usd' => 'sumIf(sr.profit_usd, h.traffic_type = 2 AND ss.hit_id IS NOT NULL)',
        'buyout_partner_profit24_eur' => 'sumIf(sr.profit_eur, h.traffic_type = 2 AND ss.hit_id IS NOT NULL)',
        'rejected_partner_profit24_rub' => 'sumIf(sr.profit_rub, h.traffic_type = 2 AND ss.hit_id IS NULL)',
        'rejected_partner_profit24_usd' => 'sumIf(sr.profit_usd, h.traffic_type = 2 AND ss.hit_id IS NULL)',
        'rejected_partner_profit24_eur' => 'sumIf(sr.profit_eur, h.traffic_type = 2 AND ss.hit_id IS NULL)',
        'date' => 's.date',
        'hour' => 's.hour',
        'source_id' => 's.source_id',
        'landing_id' => 's.landing_id',
        'operator_id' => 's.operator_id',
        'platform_id' => 's.platform_id',
        'landing_pay_type_id' => 's.landing_pay_type_id',
        new Expression('0 as `is_fake`'),
        'subid1_id' => 'h.subid1_id',
        'subid2_id' => 'h.subid2_id',
      ])
      ->from(['sr' => 'subscription_rebills'])
      /**
       * LEFT JOIN `subscriptions` s ON s.hit_id = sr.hit_id
       * LEFT JOIN `sold_subscriptions` ss ON sr.hit_id = ss.hit_id
       * LEFT JOIN hits h ON h.hit_id = sr.hit_id
       */
      ->leftJoin(['s' => 'subscriptions'], 's.hit_id = sr.hit_id')
      ->leftJoin(['ss' => 'sold_subscriptions'], 'sr.hit_id = ss.hit_id')
      ->leftJoin(['h' => 'hits'], 'h.hit_id = sr.hit_id')
      ->where([
        'and',
//        ['>=', 'sr.date', $this->cfg->getDateFrom()],
//        ['<=', 'sr.date', $this->cfg->getDateTo()],
        ['<=', 'sr.timestamp', $this->cfg->getMaxTime()],
        ['=', 'sr.date', date('Y-m-d', $fromTime)],
        ['>=', 'sr.timestamp', $fromTime],
        ['<=', 'sr.timestamp', $toTime],
        ['h.source_id' => $userSources],
      ])
      ->groupBy([
        's.date',
        's.hour',
        's.source_id',
        's.landing_id',
        's.operator_id',
        's.platform_id',
        's.landing_pay_type_id',
        'h.subid1_id',
        'h.subid2_id',
      ]);

      $mainQuery = (new ClickhouseQuery())
        ->select([
          'revshare_rebills24' => 'SUM(revshare_rebills24)',
          'rejected_rebills24' => 'SUM(rejected_rebills24)',
          'buyout_rebills24' => 'SUM(buyout_rebills24)',
          'revshare_partner_profit24_rub' => 'SUM(revshare_partner_profit24_rub)',
          'revshare_partner_profit24_usd' => 'SUM(revshare_partner_profit24_usd)',
          'revshare_partner_profit24_eur' => 'SUM(revshare_partner_profit24_eur)',
          'buyout_partner_profit24_rub' => 'SUM(buyout_partner_profit24_rub)',
          'buyout_partner_profit24_usd' => 'SUM(buyout_partner_profit24_usd)',
          'buyout_partner_profit24_eur' => 'SUM(buyout_partner_profit24_eur)',
          'rejected_partner_profit24_rub' => 'SUM(rejected_partner_profit24_rub)',
          'rejected_partner_profit24_usd' => 'SUM(rejected_partner_profit24_usd)',
          'rejected_partner_profit24_eur' => 'SUM(rejected_partner_profit24_eur)',
          'date',
          'hour',
          'source_id',
          'landing_id',
          'operator_id',
          'platform_id',
          'landing_pay_type_id',
          'is_fake' => 0,
          'subid1_id',
          'subid2_id',
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
          'subid2_id',
        ])
      ;

      return $mainQuery->createCommand(Yii::$app->clickhouse)->queryAll();
  }


}