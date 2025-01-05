<?php

namespace mcms\statistic\components\cron\handlers;

use mcms\statistic\components\cron\AbstractTableHandler;
use Yii;

/**
 * Class SubscriptionsByHours
 * @package mcms\statistic\components\cron\handlers
 */
class SubscriptionsByHours extends AbstractTableHandler
{

  public function run()
  {
    // Обновим существующее кол-во подписок для тех строк, у которых кол-во подписок изменилось
    // (например если партнер продал подписку)
    Yii::$app->db->createCommand("UPDATE subscriptions_day_hour_group AS sdhg
          LEFT JOIN `subscriptions` AS s ON
           sdhg.date = s.date AND
           sdhg.source_id = s.source_id AND
           sdhg.landing_id = s.landing_id AND
           sdhg.operator_id = s.operator_id AND
           sdhg.platform_id = s.platform_id AND
           sdhg.hour = s.hour AND
           sdhg.landing_pay_type_id = s.landing_pay_type_id AND
           sdhg.is_cpa = s.is_cpa AND
           sdhg.is_fake = s.is_fake
        SET sdhg.count_ons = 0
        WHERE sdhg.date >= :date AND s.id IS NULL")
      ->bindValue(':date', $this->params->fromDate)
      ->execute();

    Yii::$app->db->createCommand("INSERT INTO `subscriptions_day_hour_group`
        (`count_ons`, `date`, `hour`, `source_id`,
        `landing_id`, `operator_id`, `platform_id`,
        `landing_pay_type_id`, `is_cpa`, `currency_id`, `provider_id`, `is_fake`)
        SELECT COUNT(id) AS count_ons,
          `date`, `hour`, `source_id`, `landing_id`,
          `operator_id`, `platform_id`, `landing_pay_type_id`,
          `is_cpa`, `currency_id`, `provider_id`, `is_fake`
        FROM `subscriptions`
        WHERE {$this->params->dateQuery}
        GROUP BY `date`, `source_id`, `landing_id`, `operator_id`, `platform_id`, `hour`, `landing_pay_type_id`, `is_cpa`, `is_fake`
        ORDER BY NULL
        ON DUPLICATE KEY UPDATE count_ons = VALUES(count_ons)")
      ->execute();

    Yii::$app->db->createCommand("UPDATE subscriptions_day_hour_group AS sdhg
          LEFT JOIN `subscription_offs` AS s  ON
           sdhg.date = s.date AND
           sdhg.source_id = s.source_id AND
           sdhg.landing_id = s.landing_id AND
           sdhg.operator_id = s.operator_id AND
           sdhg.platform_id = s.platform_id AND
           sdhg.hour = s.hour AND
           sdhg.landing_pay_type_id = s.landing_pay_type_id AND
           sdhg.is_cpa = s.is_cpa AND
           sdhg.is_fake = s.is_fake
        SET sdhg.count_offs = 0
        WHERE sdhg.date >= :date AND s.id IS NULL")
      ->bindValue(':date', $this->params->fromDate)
      ->execute();

    Yii::$app->db->createCommand("INSERT INTO `subscriptions_day_hour_group`
        (`count_offs`, `date`, `hour`, `source_id`, `landing_id`,
        `operator_id`, `platform_id`, `landing_pay_type_id`,
        `is_cpa`, `currency_id`, `provider_id`, `is_fake`)
        SELECT COUNT(id) AS count_offs,
          `date`, `hour`, `source_id`, `landing_id`, `operator_id`,
          `platform_id`, `landing_pay_type_id`, `is_cpa`, `currency_id`, `provider_id`, `is_fake`
        FROM `subscription_offs`
        WHERE {$this->params->dateQuery}
        GROUP BY
          `date`, `source_id`, `landing_id`, `operator_id`,
          `platform_id`, `hour`, `landing_pay_type_id`, `is_cpa`, `is_fake`
        ORDER BY NULL
        ON DUPLICATE KEY UPDATE count_offs = VALUES(count_offs)")
      ->execute();


    // Обновим существующие суммы по ребиллам для тех строк для которых не сработает INSERT ON DUPLICATE KEY UPDATE
    Yii::$app->db->createCommand("UPDATE subscriptions_day_hour_group AS sdhg
        LEFT JOIN `subscription_rebills` s ON
         sdhg.date = s.date AND
         sdhg.source_id = s.source_id AND
         sdhg.landing_id = s.landing_id AND
         sdhg.operator_id = s.operator_id AND
         sdhg.platform_id = s.platform_id AND
         sdhg.hour = s.hour AND
         sdhg.landing_pay_type_id = s.landing_pay_type_id AND
         sdhg.is_cpa = s.is_cpa
      SET
        sdhg.count_rebills = 0,
        sdhg.sum_profit_rub = 0,
        sdhg.sum_profit_eur = 0,
        sdhg.sum_profit_usd = 0,
        sdhg.sum_real_profit_rub = 0,
        sdhg.sum_real_profit_eur = 0,
        sdhg.sum_real_profit_usd = 0,
        sdhg.sum_reseller_profit_rub = 0,
        sdhg.sum_reseller_profit_eur = 0,
        sdhg.sum_reseller_profit_usd = 0
      WHERE sdhg.date >= :date AND s.id IS NULL")
      ->bindValue(':date', $this->params->fromDate)
      ->execute();


    Yii::$app->db->createCommand("INSERT INTO `subscriptions_day_hour_group`
        (`count_rebills`,
        `sum_real_profit_rub`, `sum_real_profit_eur`, `sum_real_profit_usd`,
        `sum_reseller_profit_rub`, `sum_reseller_profit_eur`, `sum_reseller_profit_usd`,
        `sum_profit_rub`, `sum_profit_eur`, `sum_profit_usd`,
        `date`, `hour`, `source_id`, `landing_id`, `operator_id`,
        `platform_id`, `landing_pay_type_id`, `is_cpa`, `currency_id`, `provider_id`)
        SELECT
          COUNT(r.id) AS count_rebills,
          SUM(r.`real_profit_rub`) AS sum_real_profit_rub,
          SUM(r.`real_profit_eur`) AS sum_real_profit_eur,
          SUM(r.`real_profit_usd`) AS sum_real_profit_usd,
          SUM(r.`reseller_profit_rub`) AS sum_reseller_profit_rub,
          SUM(r.`reseller_profit_eur`) AS sum_reseller_profit_eur,
          SUM(r.`reseller_profit_usd`) AS sum_reseller_profit_usd,
          SUM(r.`profit_rub`) AS sum_profit_rub,
          SUM(r.`profit_eur`) AS sum_profit_eur,
          SUM(r.`profit_usd`) AS sum_profit_usd,
          r.`date`, r.`hour`, r.`source_id`, r.`landing_id`, r.`operator_id`, r.`platform_id`,
          r.`landing_pay_type_id`, r.`is_cpa`, r.`currency_id`, r.`provider_id`
        FROM `subscription_rebills` r
        WHERE {$this->params->getDateQuery('r', false)}
        GROUP BY
          r.`date`, r.`source_id`, r.`landing_id`, r.`operator_id`,
          r.`platform_id`, r.`hour`, r.`landing_pay_type_id`, r.`is_cpa`
        ORDER BY NULL
        ON DUPLICATE KEY UPDATE count_rebills = VALUES(count_rebills),
        sum_real_profit_rub = VALUES(sum_real_profit_rub), sum_real_profit_eur = VALUES(sum_real_profit_eur), sum_real_profit_usd = VALUES(sum_real_profit_usd),
        sum_reseller_profit_rub = VALUES(sum_reseller_profit_rub), sum_reseller_profit_eur = VALUES(sum_reseller_profit_eur), sum_reseller_profit_usd = VALUES(sum_reseller_profit_usd),
        sum_profit_rub = VALUES(sum_profit_rub), sum_profit_eur = VALUES(sum_profit_eur), sum_profit_usd = VALUES(sum_profit_usd)")
      ->execute();

    Yii::$app->db->createCommand("UPDATE `subscriptions_day_hour_group` sdhg
        INNER JOIN sources s ON s.id=sdhg.source_id
        SET sdhg.user_id = s.user_id, sdhg.stream_id = s.stream_id 
        WHERE {$this->params->dateQuery} AND (sdhg.user_id != s.user_id OR sdhg.stream_id != s.stream_id)")
      ->execute();

    Yii::$app->db->createCommand("UPDATE `subscriptions_day_hour_group` sdhg
        INNER JOIN operators bo ON bo.id=sdhg.operator_id
        SET sdhg.country_id=bo.country_id 
        WHERE {$this->params->dateQuery} AND sdhg.country_id != bo.country_id")
      ->execute();
  }
}
