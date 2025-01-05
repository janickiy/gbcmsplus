<?php

namespace mcms\statistic\components\cron\handlers;

use mcms\statistic\components\cron\AbstractTableHandler;
use Yii;

/**
 * Class StatisticByUserAndDate
 * @package mcms\statistic\components\cron\handlers
 */
class StatisticByUserAndDate extends AbstractTableHandler
{
  public function run()
  {
    Yii::$app->db->createCommand("INSERT INTO `statistic_day_user_group`
        (`date`, `user_id`, `count_ons`, `count_offs`, `count_rebills`,
        `sum_rebill_profit_rub`, `sum_rebill_profit_eur`, `sum_rebill_profit_usd`)
        SELECT
          `date`, `user_id`,
          SUM(count_ons) AS count_ons,
          SUM(count_offs) AS count_offs,
          SUM(count_rebills) AS count_rebills,
          SUM(sum_profit_rub) AS sum_rebill_profit_rub,
          SUM(sum_profit_eur) AS sum_rebill_profit_eur,
          SUM(sum_profit_usd) AS sum_rebill_profit_usd
        FROM `subscriptions_day_group`
        WHERE date >= :date AND `is_cpa` = 0
        GROUP BY `date`, `user_id`
        ORDER BY NULL
        ON DUPLICATE KEY UPDATE
          count_ons = VALUES(count_ons),
          count_offs = VALUES(count_offs),
          count_rebills = VALUES(count_rebills),
          sum_rebill_profit_rub = VALUES(sum_rebill_profit_rub),
          sum_rebill_profit_eur = VALUES(sum_rebill_profit_eur),
          sum_rebill_profit_usd = VALUES(sum_rebill_profit_usd)")
      ->bindValue(':date', $this->params->fromDate)
      ->execute();

    Yii::$app->db->createCommand("INSERT INTO `statistic_day_user_group`
        (`date`, `user_id`, `count_onetimes`, `sum_onetime_profit_rub`,
        `sum_onetime_profit_eur`, `sum_onetime_profit_usd`)
        SELECT
          `date`, `user_id`,
          COUNT(hit_id) AS count_onetimes,
          SUM(profit_rub) AS sum_onetime_profit_rub,
          SUM(profit_eur) AS sum_onetime_profit_eur,
          SUM(profit_usd) AS sum_onetime_profit_usd
        FROM `onetime_subscriptions`
        WHERE date >= :date AND `is_visible_to_partner` = 1
        GROUP BY `date`, `user_id`
        ORDER BY NULL
        ON DUPLICATE KEY UPDATE
          count_onetimes = VALUES(count_onetimes),
          sum_onetime_profit_rub = VALUES(sum_onetime_profit_rub),
          sum_onetime_profit_eur = VALUES(sum_onetime_profit_eur),
          sum_onetime_profit_usd = VALUES(sum_onetime_profit_usd)")
      ->bindValue(':date', $this->params->fromDate)
      ->execute();

    Yii::$app->db->createCommand("INSERT INTO `statistic_day_user_group`
        (`date`, `user_id`, `count_solds`, `sum_sold_profit_rub`, `sum_sold_profit_eur`, `sum_sold_profit_usd`)
        SELECT
          `date`, `user_id`,
          COUNT(hit_id) AS count_solds,
          SUM(profit_rub) AS sum_sold_profit_rub,
          SUM(profit_eur) AS sum_sold_profit_eur,
          SUM(profit_usd) AS sum_sold_profit_usd
        FROM `sold_subscriptions`
        WHERE date >= :date AND `is_visible_to_partner` = 1
        GROUP BY `date`, `user_id`
        ORDER BY NULL
        ON DUPLICATE KEY UPDATE
          count_solds = VALUES(count_solds),
          sum_sold_profit_rub = VALUES(sum_sold_profit_rub),
          sum_sold_profit_eur = VALUES(sum_sold_profit_eur),
          sum_sold_profit_usd = VALUES(sum_sold_profit_usd)")
      ->bindValue(':date', $this->params->fromDate)
      ->execute();

    Yii::$app->db->createCommand("INSERT INTO `statistic_day_user_group`
        (`date`, `user_id`, `count_hits`)
        SELECT
          `date`, `user_id`,
          COUNT(count_hits) AS count_hits
        FROM `hits_day_group`
        WHERE date >= :date
        GROUP BY `date`, `user_id`
        ORDER BY NULL
        ON DUPLICATE KEY UPDATE
          count_hits = VALUES(count_hits)")
      ->bindValue(':date', $this->params->fromDate)
      ->execute();
  }
}