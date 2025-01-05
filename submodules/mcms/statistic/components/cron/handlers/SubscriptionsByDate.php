<?php

namespace mcms\statistic\components\cron\handlers;

use mcms\statistic\components\cron\AbstractTableHandler;
use Yii;

/**
 * Class SubscriptionsByDate
 * @package mcms\statistic\components\cron\handlers
 */
class SubscriptionsByDate extends AbstractTableHandler
{

  public function run()
  {
    // автоматом выбирает ошибочный индекс, ставим сами USE INDEX (`sdhg_group_by_day`)
    Yii::$app->db->createCommand("INSERT INTO `subscriptions_day_group`
      (`count_ons`, `count_offs`, `count_rebills`, `sum_real_profit_rub`, `sum_real_profit_eur`,
      `sum_real_profit_usd`, `sum_reseller_profit_rub`, `sum_reseller_profit_eur`, `sum_reseller_profit_usd`,
      `sum_profit_rub`, `sum_profit_eur`, `sum_profit_usd`, `date`, `source_id`,
      `landing_id`, `operator_id`, `platform_id`, `landing_pay_type_id`, `is_cpa`, `currency_id`, `is_fake`)
        SELECT
          SUM(count_ons) AS count_ons,
          SUM(count_offs) AS count_offs,
          SUM(count_rebills) AS count_rebills,
          SUM(sum_real_profit_rub) AS sum_real_profit_rub,
          SUM(sum_real_profit_eur) AS sum_real_profit_eur,
          SUM(sum_real_profit_usd) AS sum_real_profit_usd,
          SUM(sum_reseller_profit_rub) AS sum_reseller_profit_rub,
          SUM(sum_reseller_profit_eur) AS sum_reseller_profit_eur,
          SUM(sum_reseller_profit_usd) AS sum_reseller_profit_usd,
          SUM(sum_profit_rub) AS sum_profit_rub,
          SUM(sum_profit_eur) AS sum_profit_eur,
          SUM(sum_profit_usd) AS sum_profit_usd,
          `date`, `source_id`, `landing_id`, `operator_id`, `platform_id`,
          `landing_pay_type_id`, `is_cpa`, `currency_id`, `is_fake`
        FROM `subscriptions_day_hour_group`
        WHERE date >= :date
        GROUP BY `date`, `source_id`, `landing_id`, `operator_id`, `platform_id`, `landing_pay_type_id`, `is_cpa`, `is_fake`
        ORDER BY NULL
        ON DUPLICATE KEY UPDATE
          count_ons = VALUES(count_ons),
          count_offs = VALUES(count_offs),
          count_rebills = VALUES(count_rebills),
          sum_real_profit_rub = VALUES(sum_real_profit_rub),
          sum_real_profit_eur = VALUES(sum_real_profit_eur),
          sum_real_profit_usd = VALUES(sum_real_profit_usd),
          sum_reseller_profit_rub = VALUES(sum_reseller_profit_rub),
          sum_reseller_profit_eur = VALUES(sum_reseller_profit_eur),
          sum_reseller_profit_usd = VALUES(sum_reseller_profit_usd),
          sum_profit_rub = VALUES(sum_profit_rub),
          sum_profit_eur = VALUES(sum_profit_eur),
          sum_profit_usd = VALUES(sum_profit_usd)")
      ->bindValue(':date', $this->params->fromDate)
      ->execute();

    Yii::$app->db->createCommand("UPDATE `subscriptions_day_group` sdg
        INNER JOIN landings l ON l.id=sdg.landing_id
        SET sdg.provider_id=l.provider_id 
        WHERE date >= :date AND sdg.provider_id != l.provider_id")
      ->bindValue(':date', $this->params->fromDate)
      ->execute();

    Yii::$app->db->createCommand("UPDATE `subscriptions_day_group` sdg
        INNER JOIN sources s ON s.id=sdg.source_id
        SET sdg.user_id = s.user_id, sdg.stream_id = s.stream_id 
        WHERE date >= :date AND (sdg.user_id != s.user_id OR sdg.stream_id != s.stream_id)")
      ->bindValue(':date', $this->params->fromDate)
      ->execute();

    Yii::$app->db->createCommand("UPDATE `subscriptions_day_group` sdg
        INNER JOIN operators bo ON bo.id=sdg.operator_id
        SET sdg.country_id=bo.country_id 
        WHERE date >= :date AND sdg.country_id != bo.country_id")
      ->bindValue(':date', $this->params->fromDate)
      ->execute();
  }

}