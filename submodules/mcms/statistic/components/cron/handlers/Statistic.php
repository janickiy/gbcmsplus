<?php

namespace mcms\statistic\components\cron\handlers;

use mcms\statistic\components\cron\AbstractTableHandler;
use Yii;

/**
 * Поэтапное заполнение таблицы statistic данными
 */
class Statistic extends AbstractTableHandler
{
  /**
   * @throws \yii\base\InvalidConfigException
   * @throws \yii\db\Exception
   */
  public function run()
  {
    // Заполнение статистики данными о подписках revshare/cpa/fake/redjected
    Yii::$app->db->createCommand("INSERT INTO `statistic`
        (`count_ons_revshare`, 
        `count_ons_cpa`, 
        `count_ons_rejected`, 
        `date`, `hour`, `source_id`,
        `landing_id`, `operator_id`, `platform_id`,
        `landing_pay_type_id`, is_fake, `currency_id`, `provider_id`)
        SELECT 
          COUNT(IF(s.is_cpa = 0 AND ss.id IS NULL, s.id, null)) AS count_ons_revshare, 
          COUNT(IF(s.is_cpa = 1 OR ss.id IS NOT NULL, s.id, null)) AS count_ons_cpa,
          COUNT(IF(s.is_cpa = 1 AND ss.id IS NULL, s.id, null)) AS count_ons_rejected,
          s.`date`, s.`hour`, s.`source_id`, s.`landing_id`,
          s.`operator_id`, s.`platform_id`, s.`landing_pay_type_id`, s.is_fake,
          s.`currency_id`, s.`provider_id`
        FROM `subscriptions` s
        LEFT JOIN sold_subscriptions ss
          ON s.hit_id = ss.hit_id
        WHERE s.date >= :date
        GROUP BY s.`date`, s.`hour`, s.`source_id`, s.`landing_id`, s.`operator_id`, s.`platform_id`, s.`landing_pay_type_id`, s.is_fake
        ORDER BY NULL
        ON DUPLICATE KEY UPDATE count_ons_rejected = VALUES(count_ons_rejected), count_ons_cpa = VALUES(count_ons_cpa), count_ons_revshare = VALUES(count_ons_revshare)")
      ->bindValue(':date', $this->params->fromDate)
      ->execute();

    // Заполнение статистики об отписках revshare/cpa/fake/redjected
    Yii::$app->db->createCommand("INSERT INTO `statistic`
        (`count_offs_revshare`, `count_offs_rejected`, `date`, `hour`, `source_id`,
        `landing_id`, `operator_id`, `platform_id`,
        `landing_pay_type_id`, is_fake, `currency_id`, `provider_id`)
        SELECT  
          COUNT(IF(s.is_cpa = 0 AND ss.id IS NULL, s.id, null)) AS count_offs_revshare,
          COUNT(IF(s.is_cpa = 1 AND ss.id IS NULL, s.id, null)) AS count_offs_rejected,
          s.`date`, s.`hour`, s.`source_id`, s.`landing_id`,
          s.`operator_id`, s.`platform_id`, s.`landing_pay_type_id`, s.is_fake,
          s.`currency_id`, s.`provider_id`
        FROM `subscription_offs` s
        LEFT JOIN sold_subscriptions ss
          ON s.hit_id = ss.hit_id
        WHERE s.date >= :date
        GROUP BY s.`date`, s.`hour`, s.`source_id`, s.`landing_id`, s.`operator_id`, s.`platform_id`, s.`landing_pay_type_id`, s.is_fake
        ORDER BY NULL
        ON DUPLICATE KEY UPDATE count_offs_rejected = VALUES(count_offs_rejected), count_offs_revshare = VALUES(count_offs_revshare)")
      ->bindValue(':date', $this->params->fromDate)
      ->execute();

    // Заполнение статистики revshare ребиллах, профитах реселлера и партнера с этих ребиллов, данными о CPA ребиллах (выкупленных и невыкупленных) и соответствующие профиты реса
    Yii::$app->db->createCommand("INSERT INTO `statistic`
          (
          `count_rebills_revshare`,
          `res_revshare_profit_rub`, `res_revshare_profit_usd`, `res_revshare_profit_eur`,
          `partner_revshare_profit_rub`, `partner_revshare_profit_usd`, `partner_revshare_profit_eur`,
          
          `count_rebills_sold`, `count_rebills_rejected`,
           res_sold_profit_rub, res_sold_profit_usd, res_sold_profit_eur,
           
           res_rejected_profit_rub, res_rejected_profit_usd, res_rejected_profit_eur,
           `date`, `hour`, `source_id`,
           `landing_id`, `operator_id`, `platform_id`,
           `landing_pay_type_id`, `currency_id`, `provider_id`)
            SELECT
              COUNT(IF(sr.is_cpa = 0 AND ss.id IS NULL, sr.id, null)) AS count_rebills_revshare,
            
              SUM(IF(sr.`is_cpa` = 0 AND ss.id IS NULL, sr.`reseller_profit_rub`, 0)) AS res_revshare_profit_rub,
              SUM(IF(sr.`is_cpa` = 0 AND ss.id IS NULL, sr.`reseller_profit_usd`, 0)) AS res_revshare_profit_usd,
              SUM(IF(sr.`is_cpa` = 0 AND ss.id IS NULL, sr.`reseller_profit_eur`, 0)) AS res_revshare_profit_eur,
    
              SUM(IF(sr.`is_cpa` = 0 AND ss.id IS NULL, sr.`profit_rub`, 0)) AS partner_revshare_profit_rub,
              SUM(IF(sr.`is_cpa` = 0 AND ss.id IS NULL, sr.`profit_usd`, 0)) AS partner_revshare_profit_usd,
              SUM(IF(sr.`is_cpa` = 0 AND ss.id IS NULL, sr.`profit_eur`, 0)) AS partner_revshare_profit_eur,
          
              COUNT(IF(ss.id IS NOT NULL, sr.hit_id, NULL))                  AS count_rebills_sold,
              COUNT((IF(sr.`is_cpa` = 1 AND ss.id IS NULL, sr.hit_id, NULL)))   AS count_rebills_rejected,
          
              SUM(IF(ss.hit_id IS NOT NULL, sr.`reseller_profit_rub`, 0)) AS res_sold_profit_rub,
              SUM(IF(ss.hit_id IS NOT NULL, sr.`reseller_profit_usd`, 0)) AS res_sold_profit_usd,
              SUM(IF(ss.hit_id IS NOT NULL, sr.`reseller_profit_eur`, 0)) AS res_sold_profit_eur,
              
              SUM(IF(sr.`is_cpa` = 1 AND ss.id IS NULL, sr.`reseller_profit_rub`, 0))     AS res_rejected_profit_rub,
              SUM(IF(sr.`is_cpa` = 1 AND ss.id IS NULL, sr.`reseller_profit_usd`, 0))     AS res_rejected_profit_usd,
              SUM(IF(sr.`is_cpa` = 1 AND ss.id IS NULL, sr.`reseller_profit_eur`, 0))     AS res_rejected_profit_eur,
          
              sr.date,
              sr.hour,
              sr.source_id,
              sr.landing_id,
              sr.operator_id,
              sr.platform_id,
              sr.landing_pay_type_id,
              sr.currency_id,
              sr.provider_id
            FROM `subscription_rebills` sr
              LEFT JOIN sold_subscriptions ss
                ON sr.hit_id = ss.hit_id
            WHERE sr.date >= :date
            GROUP BY sr.date, sr.hour, sr.source_id, sr.landing_id, sr.operator_id, sr.platform_id, sr.landing_pay_type_id
            ORDER BY NULL
          ON DUPLICATE KEY UPDATE
            count_rebills_revshare = VALUES(count_rebills_revshare),
          
            res_revshare_profit_rub = VALUES(res_revshare_profit_rub),
            res_revshare_profit_usd = VALUES(res_revshare_profit_usd),
            res_revshare_profit_eur = VALUES(res_revshare_profit_eur),
            
            partner_revshare_profit_rub = VALUES(partner_revshare_profit_rub),
            partner_revshare_profit_usd = VALUES(partner_revshare_profit_usd),
            partner_revshare_profit_eur = VALUES(partner_revshare_profit_eur),
        
            count_rebills_sold      = VALUES(count_rebills_sold),
            count_rebills_rejected  = VALUES(count_rebills_rejected),
          
            res_sold_profit_rub     = VALUES(res_sold_profit_rub),
            res_sold_profit_usd     = VALUES(res_sold_profit_usd),
            res_sold_profit_eur     = VALUES(res_sold_profit_eur),
            
            res_rejected_profit_rub = VALUES(res_rejected_profit_rub),
            res_rejected_profit_usd = VALUES(res_rejected_profit_usd),
            res_rejected_profit_eur = VALUES(res_rejected_profit_eur)
          ")
      ->bindValue(':date', $this->params->fromDate)
      ->execute();

    $fromDate = $this->getFromDate();
    // Вычитаем 1 день, т.к. по-умолчанию крон считает -12часов.
    // Следовательно чтоб попали все подписки за сутки а не за 12ч, нужно сместить дату
    $fromDate = Yii::$app->formatter->asDate($fromDate . ' - 1day', 'php:Y-m-d');

    // Заполнение статистики данными об отписках в течении суток после подписки (revshare, cpa)
    Yii::$app->db->createCommand("
      INSERT INTO `statistic` (
        `count_offs24_revshare`,
        `count_offs24_rejected`,
        `count_offs24_sold`,
        `date`,
        `hour`,
        `source_id`,
        `landing_id`,
        `operator_id`,
        `platform_id`,
        `landing_pay_type_id`, is_fake, `currency_id`, `provider_id`)
      SELECT
        COUNT(IF(s.`is_cpa` = '0' AND ss.id IS NULL, s.`id`, NULL)) AS count_offs24_revshare,
        COUNT(IF(s.`is_cpa` = '1' AND ss.id IS NULL, s.`id`, NULL)) AS count_offs24_rejected,
        COUNT(IF(ss.id IS NOT NULL, s.`id`, NULL)) AS count_offs24_sold,
        s.`date`,
        s.`hour`,
        s.`source_id`,
        s.`landing_id`,
        s.`operator_id`,
        s.`platform_id`,
        s.`landing_pay_type_id`, 
        s.is_fake,
        s.`currency_id`,
        s.`provider_id`
      FROM `subscriptions` s
      INNER JOIN `subscription_offs` so
        ON s.hit_id = so.hit_id
        LEFT JOIN sold_subscriptions ss
          ON s.hit_id = ss.hit_id
      WHERE s.`date` >= :date AND so.time <= (:groupOffsHours * 60 * 60 + s.time)
      GROUP BY s.`date`, s.`hour`, s.`source_id`, s.`landing_id`, s.`operator_id`, s.`platform_id`, s.`landing_pay_type_id`, s.is_fake
      ORDER BY NULL
      ON DUPLICATE KEY UPDATE
        count_offs24_revshare = VALUES(count_offs24_revshare),
        count_offs24_rejected = VALUES(count_offs24_rejected),
        count_offs24_sold = VALUES(count_offs24_sold)
    ")
      ->bindValue(':groupOffsHours', $this->getOffsScopeHours())
      ->bindValue(':date', $fromDate)
      ->execute();

   // Ребиллы и профиты в течении суток после подписки
   // Заполнение статистики данными о ребиллах в течении суток после подписки (revshare, cpa) и соответствующих профитах партнера
   Yii::$app->db->createCommand("
     INSERT INTO `statistic`(
       `date`,
       `hour`,
       `source_id`,
       `landing_id`,
       `operator_id`,
       `platform_id`,
       `landing_pay_type_id`,
       is_fake,
       `currency_id`,
       `provider_id`,
       `count_rebills24_revshare`,
       `count_rebills24_rejected`,
       `count_rebills24_sold`,
       `partner_revshare_profit24_rub`,
       `partner_revshare_profit24_usd`,
       `partner_revshare_profit24_eur`,

       `partner_rejected_profit24_rub`,
       `partner_rejected_profit24_usd`,
       `partner_rejected_profit24_eur`,

       `partner_sold_profit24_rub`,
       `partner_sold_profit24_usd`,
       `partner_sold_profit24_eur`
     )
       SELECT
         s.`date`,
         s.`hour`,
         s.`source_id`,
         s.`landing_id`,
         s.`operator_id`,
         s.`platform_id`,
         s.`landing_pay_type_id`,
         s.is_fake,
         s.`currency_id`,
         s.`provider_id`,
         COUNT(IF(s.is_cpa = 0 AND ss.id IS NULL, s.id, null)) as count_rebills24_revshare,
         COUNT(IF(s.is_cpa = 1 AND ss.id IS NULL, s.id, null)) as count_rebills24_rejected,
         COUNT(IF(ss.id IS NOT NULL, s.id, null)) as count_rebills24_sold,
         SUM(IF(s.`is_cpa` = 0 AND ss.id IS NULL, r.`reseller_profit_rub`, 0)) as partner_revshare_profit24_rub,
         SUM(IF(s.`is_cpa` = 0 AND ss.id IS NULL, r.`reseller_profit_usd`, 0)) as partner_revshare_profit24_usd,
         SUM(IF(s.`is_cpa` = 0 AND ss.id IS NULL, r.`reseller_profit_eur`, 0)) as partner_revshare_profit24_eur,

         SUM(IF(s.`is_cpa` = 1 AND ss.id IS NULL, r.`reseller_profit_rub`, 0)) as partner_rejected_profit24_rub,
         SUM(IF(s.`is_cpa` = 1 AND ss.id IS NULL, r.`reseller_profit_usd`, 0)) as partner_rejected_profit24_usd,
         SUM(IF(s.`is_cpa` = 1 AND ss.id IS NULL, r.`reseller_profit_eur`, 0)) as partner_rejected_profit24_eur,

         SUM(IF(ss.id IS NOT NULL, r.`reseller_profit_rub`, 0)) as partner_sold_profit24_rub,
         SUM(IF(ss.id IS NOT NULL, r.`reseller_profit_usd`, 0)) as partner_sold_profit24_usd,
         SUM(IF(ss.id IS NOT NULL, r.`reseller_profit_eur`, 0)) as partner_sold_profit24_eur

       FROM `subscription_rebills` AS r
       LEFT JOIN `subscriptions` s
         ON s.hit_id = r.hit_id AND
           (({$this->getTrialOperatorsInCondition('s.operator_id', true)} AND s.date = r.date) OR ({$this->getTrialOperatorsInCondition('s.operator_id')} AND s.date = date_add(r.date, INTERVAL -1 DAY)))
       LEFT JOIN sold_subscriptions ss
         ON r.hit_id = ss.hit_id
       WHERE s.date >= :date AND s.is_fake = 0
       GROUP BY s.`date`, s.`hour`, s.`source_id`, s.`landing_id`, s.`operator_id`, s.`platform_id`, s.`landing_pay_type_id`, s.is_fake
       ORDER BY NULL
       ON DUPLICATE KEY UPDATE
         count_rebills24_revshare = VALUES(count_rebills24_revshare),
         count_rebills24_rejected = VALUES(count_rebills24_rejected),
         count_rebills24_sold = VALUES(count_rebills24_sold),
         partner_revshare_profit24_rub = VALUES(partner_revshare_profit24_rub),
         partner_revshare_profit24_usd = VALUES(partner_revshare_profit24_usd),
         partner_revshare_profit24_eur = VALUES(partner_revshare_profit24_eur),

         partner_rejected_profit24_rub = VALUES(partner_rejected_profit24_rub),
         partner_rejected_profit24_usd = VALUES(partner_rejected_profit24_usd),
         partner_rejected_profit24_eur = VALUES(partner_rejected_profit24_eur),

         partner_sold_profit24_rub = VALUES(partner_sold_profit24_rub),
         partner_sold_profit24_usd = VALUES(partner_sold_profit24_usd),
         partner_sold_profit24_eur = VALUES(partner_sold_profit24_eur)
   ")->bindValue(':date', $fromDate)
     ->execute();

    // Заполнение статистики данными о количестве отписок от выкупленных подписок
    Yii::$app->db->createCommand("INSERT INTO `statistic`
        (`count_offs_sold`, `date`, `hour`, `source_id`,
        `landing_id`, `operator_id`, `platform_id`,
        `landing_pay_type_id`, is_fake, `currency_id`, `provider_id`)
        SELECT COUNT(IF(ss.id IS NOT NULL, so.hit_id, NULL)) AS count_offs_sold,
        so.date, so.hour, so.source_id, so.landing_id,
          so.operator_id, so.platform_id, so.landing_pay_type_id, so.is_fake,
          so.currency_id, so.provider_id
        FROM `subscription_offs` so
         LEFT JOIN sold_subscriptions ss
          ON so.hit_id = ss.hit_id
        WHERE so.date >= :date
        GROUP BY so.date, so.hour, so.source_id, so.landing_id, so.operator_id, so.platform_id, so.landing_pay_type_id, so.is_fake
        ORDER BY NULL
        ON DUPLICATE KEY UPDATE
          count_offs_sold = VALUES(count_offs_sold)")
      ->bindValue(':date', $this->params->fromDate)
      ->execute();

    // Заполнение статы данными, которые не были добавлены в предыдущих запросах
    Yii::$app->db->createCommand(/** @lang MySQL */
      'UPDATE statistic st
        LEFT JOIN sources s ON s.id = st.source_id
        LEFT JOIN operators op ON op.id = st.operator_id
      SET st.user_id = s.user_id, st.stream_id = s.stream_id, st.country_id = op.country_id
      WHERE st.date >= :date AND st.user_id = 0 AND st.stream_id = 0 AND st.country_id = 0'
    )
      ->bindValue(':date', $this->params->fromDate)
      ->execute();
  }
}