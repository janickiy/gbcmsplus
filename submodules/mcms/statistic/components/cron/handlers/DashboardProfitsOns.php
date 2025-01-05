<?php

namespace mcms\statistic\components\cron\handlers;


use mcms\statistic\components\cron\AbstractTableHandler;
use Yii;

/**
 * Запись данных для таблицы dashboard_profits_ons (дашборд)
 * @package mcms\statistic\components\cron\handlers
 */
class DashboardProfitsOns extends AbstractTableHandler
{
  public function run()
  {
    /**
     * Заполнение res_profit_, real_profit_, partner_profit_ и count_ons из statistic
     */
    Yii::$app->db->createCommand("INSERT INTO `dashboard_profits_ons`
        (`date`, `country_id`, `user_id`, `operator_id`, 
        `res_revshare_profit_rub`, `res_revshare_profit_usd`, `res_revshare_profit_eur`,
        `res_rejected_profit_rub`, `res_rejected_profit_usd`, `res_rejected_profit_eur`,
        `res_sold_profit_rub`, `res_sold_profit_usd`, `res_sold_profit_eur`,
        
        `partner_revshare_profit_rub`, `partner_revshare_profit_usd`, `partner_revshare_profit_eur`,
        
        `count_ons_revshare`, `count_ons_rejected`, `count_ons_cpa`)
        SELECT `date`, `country_id`, `user_id`, `operator_id`, 
        SUM(`res_revshare_profit_rub`) AS res_revshare_profit_rub,
        SUM(`res_revshare_profit_usd`) AS res_revshare_profit_usd, 
        SUM(`res_revshare_profit_eur`) AS res_revshare_profit_eur,
        SUM(`res_rejected_profit_rub`) AS res_rejected_profit_rub,
        SUM(`res_rejected_profit_usd`) AS res_rejected_profit_usd, 
        SUM(`res_rejected_profit_eur`) AS res_rejected_profit_eur,
        SUM(`res_sold_profit_rub`) AS res_sold_profit_rub,
        SUM(`res_sold_profit_usd`) AS res_sold_profit_usd, 
        SUM(`res_sold_profit_eur`) AS res_sold_profit_eur,
        SUM(`partner_revshare_profit_rub`) AS partner_revshare_profit_rub,
        SUM(`partner_revshare_profit_usd`) AS partner_revshare_profit_usd, 
        SUM(`partner_revshare_profit_eur`) AS partner_revshare_profit_eur, 
        SUM(`count_ons_revshare`) AS count_ons_revshare, 
        SUM(`count_ons_rejected`) AS count_ons_rejected, 
        SUM(`count_ons_cpa`) AS count_ons_cpa
        FROM `statistic`
        WHERE `date` >= :fromDate
        GROUP BY `date`, `country_id`, `user_id`, `operator_id`
        ORDER BY NULL
        ON DUPLICATE KEY UPDATE
        res_revshare_profit_rub = VALUES(res_revshare_profit_rub),
        res_revshare_profit_usd = VALUES(res_revshare_profit_usd),
        res_revshare_profit_eur = VALUES(res_revshare_profit_eur),
        res_rejected_profit_rub = VALUES(res_rejected_profit_rub),
        res_rejected_profit_usd = VALUES(res_rejected_profit_usd),
        res_rejected_profit_eur = VALUES(res_rejected_profit_eur),
        res_sold_profit_rub = VALUES(res_sold_profit_rub),
        res_sold_profit_usd = VALUES(res_sold_profit_usd),
        res_sold_profit_eur = VALUES(res_sold_profit_eur),
        partner_revshare_profit_rub = VALUES(partner_revshare_profit_rub),
        partner_revshare_profit_usd = VALUES(partner_revshare_profit_usd),
        partner_revshare_profit_eur = VALUES(partner_revshare_profit_eur),
        count_ons_revshare = VALUES(count_ons_revshare),
        count_ons_rejected = VALUES(count_ons_rejected),
        count_ons_cpa = VALUES(count_ons_cpa)")
      ->bindValue(':fromDate', $this->params->fromDate)
      ->execute();


    /**
     * Заполнение res_onetime_profit_, real_onetime_profit_, partner_onetime_profit_ и count_onetime из onetime_subscriptions
     */
    Yii::$app->db->createCommand("INSERT INTO `dashboard_profits_ons`
        (`date`, `country_id`, `user_id`, `operator_id`, 
        `res_onetime_profit_rub`, `res_onetime_profit_usd`, `res_onetime_profit_eur`,
        `real_onetime_profit_rub`, `real_onetime_profit_usd`, `real_onetime_profit_eur`,
        `partner_onetime_profit_rub`, `partner_onetime_profit_usd`, `partner_onetime_profit_eur`, `count_onetime`)
        SELECT `date`, `country_id`, `user_id`, `operator_id`, 
        SUM(`reseller_profit_rub`) AS res_onetime_profit_rub, 
        SUM(`reseller_profit_usd`) AS res_onetime_profit_usd, 
        SUM(`reseller_profit_eur`) AS res_onetime_profit_eur,
        SUM(`real_profit_rub`) AS real_onetime_profit_rub, 
        SUM(`real_profit_usd`) AS real_onetime_profit_usd, 
        SUM(`real_profit_eur`) AS real_onetime_profit_eur,
        SUM(`profit_rub`) AS partner_onetime_profit_rub, 
        SUM(`profit_usd`) AS partner_onetime_profit_usd, 
        SUM(`profit_eur`) AS partner_onetime_profit_eur, 
        COUNT(hit_id) AS count_onetime
        FROM `onetime_subscriptions`
        WHERE `date` >= :fromDate
        GROUP BY `date`, `country_id`, `user_id`, `operator_id`
        ORDER BY NULL
        ON DUPLICATE KEY UPDATE
        res_onetime_profit_rub = VALUES(res_onetime_profit_rub),
        res_onetime_profit_usd = VALUES(res_onetime_profit_usd),
        res_onetime_profit_eur = VALUES(res_onetime_profit_eur),
        real_onetime_profit_rub = VALUES(real_onetime_profit_rub),
        real_onetime_profit_usd = VALUES(real_onetime_profit_usd),
        real_onetime_profit_eur = VALUES(real_onetime_profit_eur),
        partner_onetime_profit_rub = VALUES(partner_onetime_profit_rub),
        partner_onetime_profit_usd = VALUES(partner_onetime_profit_usd),
        partner_onetime_profit_eur = VALUES(partner_onetime_profit_eur),
        count_onetime = VALUES(count_onetime)")
      ->bindValue(':fromDate', $this->params->fromDate)
      ->execute();

    /**
     * Заполнение partner_sold_profit_  из sold_subscriptions
     */
    Yii::$app->db->createCommand("INSERT INTO `dashboard_profits_ons`
        (`date`, `country_id`, `user_id`, `operator_id`, 
        `partner_sold_profit_rub`, `partner_sold_profit_usd`, `partner_sold_profit_eur`)
        SELECT `date`, `country_id`, `user_id`, `operator_id`,
        SUM(`profit_rub`) AS partner_sold_profit_rub, 
        SUM(`profit_usd`) AS partner_sold_profit_usd, 
        SUM(`profit_eur`) AS partner_sold_profit_eur
        FROM `sold_subscriptions`
        WHERE `date` >= :fromDate
        GROUP BY `date`, `country_id`, `user_id`, `operator_id`
        ORDER BY NULL
        ON DUPLICATE KEY UPDATE
        partner_sold_profit_rub = VALUES(partner_sold_profit_rub),
        partner_sold_profit_usd = VALUES(partner_sold_profit_usd),
        partner_sold_profit_eur = VALUES(partner_sold_profit_eur)")
      ->bindValue(':fromDate', $this->params->fromDate)
      ->execute();

    /**
     * Заполнение count_hits из hits_day_group
     */
    Yii::$app->db->createCommand("INSERT INTO `dashboard_profits_ons`
        (`date`, `country_id`, `user_id`, `operator_id`, `count_hits`)
        SELECT `date`, `country_id`, `user_id`, `operator_id`, SUM(`count_hits`) AS count_hits
        FROM `hits_day_group`
        WHERE `date` >= :fromDate
        GROUP BY `date`, `country_id`, `user_id`, `operator_id`
        ORDER BY NULL
        ON DUPLICATE KEY UPDATE
        count_hits = VALUES(count_hits)")
      ->bindValue(':fromDate', $this->params->fromDate)
      ->execute();

    /**
     * Заполнение res_sold_tb_profit_ из sold_trafficback
     */
    /*
     * TODO: Раскоментить при выполнении MCMS-2105
    Yii::$app->db->createCommand("INSERT INTO `dashboard_profits_ons`
        (`date`, `country_id`, `user_id`, `operator_id`, `res_sold_tb_profit_rub`, `res_sold_tb_profit_usd`, `res_sold_tb_profit_eur`)
        SELECT `date`, `country_id`, `user_id`, `operator_id`, 
        SUM(`reseller_profit_rub`) AS res_sold_tb_profit_rub,
        SUM(`reseller_profit_usd`) AS res_sold_tb_profit_usd,
        SUM(`reseller_profit_eur`) AS res_sold_tb_profit_eur
        FROM `sold_trafficback`
        WHERE `date` >= :fromDate
        GROUP BY `date`, `country_id`, `user_id`, `operator_id`
        ORDER BY NULL
        ON DUPLICATE KEY UPDATE
        res_sold_tb_profit_rub = VALUES(res_sold_tb_profit_rub),
        res_sold_tb_profit_usd = VALUES(res_sold_tb_profit_usd),
        res_sold_tb_profit_eur = VALUES(res_sold_tb_profit_eur)")
      ->bindValue(':fromDate', $this->params->fromDate)
      ->execute();
    */
  }
}