<?php

namespace mcms\statistic\components\cron\handlers;


use mcms\statistic\components\cron\AbstractTableHandler;
use Yii;

/**
 * Запись данных для таблицы dashboard_landings (дашборд)
 * @package mcms\statistic\components\cron\handlers
 */
class DashboardLandings extends AbstractTableHandler
{
  public function run()
  {
    /**
     * Заполнение count_hits и count_tb из hits_day_group
     */
    Yii::$app->db->createCommand("INSERT INTO `dashboard_landings`
        (`landing_id`, `date`, `user_id`, `country_id`, `operator_id`, `count_hits`, `count_tb`)
        SELECT `landing_id`, `date`, `user_id`, `country_id`, `operator_id`, 
        SUM(`count_hits`) AS count_hits, SUM(`count_tb`) AS count_tb 
        FROM `hits_day_group`
        WHERE `date` >= :fromDate AND landing_id <> 0
        GROUP BY `landing_id`, `date`, `user_id`, `country_id`, `operator_id`
        ORDER BY NULL
        ON DUPLICATE KEY UPDATE
        count_hits = VALUES(count_hits),
        count_tb = VALUES(count_tb)")
      ->bindValue(':fromDate', $this->params->fromDate)
      ->execute();

    /**
     * Заполнение count_ons_revshare, count_ons_rejected, count_ons_cpa из statistic
     */
    Yii::$app->db->createCommand("INSERT INTO `dashboard_landings`
        (`landing_id`, `date`, `user_id`, `country_id`, `operator_id`, `count_ons_revshare`, `count_ons_rejected`, `count_ons_cpa`)
        SELECT `landing_id`, `date`, `user_id`, `country_id`, `operator_id`, 
        SUM(`count_ons_revshare`) AS count_ons_revshare, 
        SUM(`count_ons_rejected`) AS count_ons_rejected, 
        SUM(`count_ons_cpa`) AS count_ons_cpa
        FROM `statistic`
        WHERE `date` >= :fromDate AND landing_id <> 0
        GROUP BY `landing_id`, `date`, `user_id`, `country_id`, `operator_id`
        ORDER BY NULL
        ON DUPLICATE KEY UPDATE
        count_ons_revshare = VALUES(count_ons_revshare),
        count_ons_rejected = VALUES(count_ons_rejected),
        count_ons_cpa = VALUES(count_ons_cpa)")
      ->bindValue(':fromDate', $this->params->fromDate)
      ->execute();

    /**
     * Заполнение count_onetime из onetime_subscriptions
     */
    Yii::$app->db->createCommand("INSERT INTO `dashboard_landings`
        (`landing_id`, `date`, `user_id`, `country_id`, `operator_id`, `count_onetime`)
        SELECT `landing_id`, `date`, `user_id`, `country_id`, `operator_id`, 
        COUNT(`hit_id`) AS count_onetime 
        FROM `onetime_subscriptions`
        WHERE `date` >= :fromDate AND landing_id <> 0
        GROUP BY `landing_id`, `date`, `user_id`, `country_id`, `operator_id`
        ORDER BY NULL
        ON DUPLICATE KEY UPDATE
        count_onetime = VALUES(count_onetime)")
      ->bindValue(':fromDate', $this->params->fromDate)
      ->execute();
  }
}