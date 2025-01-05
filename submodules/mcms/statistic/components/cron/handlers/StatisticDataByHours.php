<?php

namespace mcms\statistic\components\cron\handlers;

use mcms\statistic\components\cron\AbstractTableHandler;
use Yii;

/**
 * Стата по отпискам 24 и первичным ребиллам.
 */
class StatisticDataByHours extends AbstractTableHandler
{

  public function run()
  {
    $fromDate = $this->getFromDate();

    // Вычитаем 1 день, т.к. по-умолчанию крон считает -12часов.
    // Следовательно чтоб попали все подписки за сутки а не за 12ч, нужно сместить дату
    $fromDate = Yii::$app->formatter->asDate($fromDate . ' - 1day', 'php:Y-m-d');
    Yii::$app->db->createCommand("
      INSERT INTO `statistic_data_hour_group` (
        `count_scope_offs`,
        `date`, 
        `hour`, 
        `source_id`,
        `landing_id`, 
        `operator_id`, 
        `platform_id`,
        `landing_pay_type_id`, `currency_id`, `provider_id`, `is_fake`, `is_cpa`)
      SELECT
        COUNT(s.id) AS count_scope_offs,
        s.`date`,
        s.`hour`,
        s.`source_id`,
        s.`landing_id`,
        s.`operator_id`,
        s.`platform_id`,
        s.`landing_pay_type_id`,
        s.`currency_id`,
        s.`provider_id`,
        s.`is_fake`,
        s.`is_cpa`
      FROM `subscriptions` s
      INNER JOIN `subscription_offs` so 
        ON s.hit_id = so.hit_id
      WHERE s.`date` >= :date AND so.time <= (:groupOffsHours * 60 * 60 + s.time)
      GROUP BY s.`date`, s.`source_id`, s.`landing_id`, s.`operator_id`, s.`platform_id`, s.`hour`, s.`landing_pay_type_id`, s.`is_fake`, s.`is_cpa`
      ORDER BY NULL
      ON DUPLICATE KEY UPDATE 
        count_scope_offs = VALUES(count_scope_offs)
    ")
      ->bindValue(':groupOffsHours', $this->getOffsScopeHours())
      ->bindValue(':date', $fromDate)
      ->execute();

    // Поправим кол-во отписок, т.к. сменился флаг is_cpa
    Yii::$app->db->createCommand("
       UPDATE `statistic_data_hour_group` AS sdhg
        LEFT JOIN (
           SELECT
             s.`date`,
             s.`hour`,
             s.`source_id`,
             s.`landing_id`,
             s.`operator_id`,
             s.`platform_id`,
             s.`landing_pay_type_id`,
             s.is_cpa,
             s.is_fake,
             COUNT(s.id) as count_scope_offs
             FROM `subscription_offs` AS so
             LEFT JOIN `subscriptions` s ON s.hit_id = so.hit_id
           WHERE s.`date` >= :date AND so.time <= (:groupOffsHours * 60 * 60 + s.time) AND s.is_fake = 0
           GROUP BY s.`date`, s.`source_id`, s.`landing_id`, s.`operator_id`, s.`platform_id`, s.`hour`, s.`landing_pay_type_id`, s.`is_cpa`
         ) AS new ON
            sdhg.date = new.date AND
            sdhg.hour = new.hour AND
            sdhg.source_id = new.source_id AND
            sdhg.landing_id = new.landing_id AND
            sdhg.operator_id = new.operator_id AND
            sdhg.platform_id = new.platform_id AND
            sdhg.is_cpa = new.is_cpa AND
            sdhg.is_fake = new.is_fake AND
            sdhg.landing_pay_type_id = new.landing_pay_type_id
      SET sdhg.count_scope_offs = 0
      WHERE
        sdhg.is_fake = 0 
        AND sdhg.is_cpa = 1 
        AND new.count_scope_offs IS NULL 
        AND sdhg.count_scope_offs > 0;
    ")
      ->bindValue(':groupOffsHours', $this->getOffsScopeHours())
      ->bindValue(':date', $fromDate)
      ->execute();



    Yii::$app->db->createCommand("
      INSERT INTO `statistic_data_hour_group`(
        `date`,
        `hour`, 
        `source_id`,
        `landing_id`, 
        `operator_id`, 
        `platform_id`,
        `landing_pay_type_id`, 
        `currency_id`, 
        `provider_id`, 
        `is_cpa`,
        `count_rebills_date_by_date`,
        `sum_profit_rub_date_by_date`,
        `sum_profit_usd_date_by_date`,
        `sum_profit_eur_date_by_date`
      )
        SELECT 
          s.`date`,
          s.`hour`,
          s.`source_id`,
          s.`landing_id`,
          s.`operator_id`,
          s.`platform_id`,
          s.`landing_pay_type_id`,
          s.`currency_id`,
          s.`provider_id`,
          s.`is_cpa`,
          COUNT(s.id) as count_rebills_date_by_date,
          SUM(IF(s.id IS NULL, 0, r.`profit_rub`)) as sum_profit_rub_date_by_date,
          SUM(IF(s.id IS NULL, 0, r.`profit_usd`)) as sum_profit_usd_date_by_date,
          SUM(IF(s.id IS NULL, 0, r.`profit_eur`)) as sum_profit_eur_date_by_date
        FROM `subscription_rebills` AS r
        LEFT JOIN `subscriptions` s
          ON s.hit_id = r.hit_id AND 
            (({$this->getTrialOperatorsInCondition('s.operator_id', true)} AND s.date = r.date) OR ({$this->getTrialOperatorsInCondition('s.operator_id')} AND s.date = date_add(r.date, INTERVAL -1 DAY)))
        WHERE s.date >= :date AND s.is_fake = 0
        GROUP BY s.`date`, s.`source_id`, s.`landing_id`, s.`operator_id`, s.`platform_id`, s.`hour`, s.`landing_pay_type_id`, s.`is_cpa`
        ORDER BY NULL
        ON DUPLICATE KEY UPDATE 
          count_rebills_date_by_date = VALUES(count_rebills_date_by_date),
          sum_profit_rub_date_by_date = VALUES(sum_profit_rub_date_by_date),
          sum_profit_eur_date_by_date = VALUES(sum_profit_eur_date_by_date),
          sum_profit_usd_date_by_date = VALUES(sum_profit_usd_date_by_date)
    ")->bindValue(':date', $fromDate)
      ->execute();

    // уже выкупленные строки (которые были поначалу Rejected) надо до-обнулить.
    Yii::$app->db->createCommand("
      UPDATE `statistic_data_hour_group` AS sdhg
        LEFT JOIN (
           SELECT
             s.`date`,
             s.`hour`,
             s.`source_id`,
             s.`landing_id`,
             s.`operator_id`,
             s.`platform_id`,
             s.`landing_pay_type_id`,
             s.is_cpa,
             s.is_fake,
             COUNT(s.id) as count_rebills_date_by_date,
             SUM(IF(s.id is NULL, 0, r.`profit_rub`)) as sum_profit_rub_date_by_date,
             SUM(IF(s.id is NULL, 0, r.`profit_eur`)) as sum_profit_eur_date_by_date,
             SUM(IF(s.id is NULL, 0, r.`profit_usd`)) as sum_profit_usd_date_by_date
             FROM `subscription_rebills` AS r
             LEFT JOIN `subscriptions` s
               ON s.hit_id = r.hit_id AND (({$this->getTrialOperatorsInCondition('s.operator_id', true)} AND s.date = r.date) OR ({$this->getTrialOperatorsInCondition('s.operator_id')} AND s.date = date_add(r.date, INTERVAL -1 DAY)))
           WHERE s.date >= :date AND s.is_fake = 0
           GROUP BY s.`date`, s.`source_id`, s.`landing_id`, s.`operator_id`, s.`platform_id`, s.`hour`, s.`landing_pay_type_id`, s.`is_cpa`
         ) AS new ON
            sdhg.date = new.date AND
            sdhg.hour = new.hour AND
            sdhg.source_id = new.source_id AND
            sdhg.landing_id = new.landing_id AND
            sdhg.operator_id = new.operator_id AND
            sdhg.platform_id = new.platform_id AND
            sdhg.is_cpa = new.is_cpa AND
            sdhg.is_fake = new.is_fake AND
            sdhg.landing_pay_type_id = new.landing_pay_type_id
      SET
        sdhg.count_rebills_date_by_date = 0,
        sdhg.sum_profit_rub_date_by_date = 0,
        sdhg.sum_profit_eur_date_by_date = 0,
        sdhg.sum_profit_usd_date_by_date = 0
      WHERE 
        sdhg.is_fake = 0 
        AND sdhg.is_cpa = 1 
        AND new.count_rebills_date_by_date IS NULL 
        AND sdhg.count_rebills_date_by_date > 0;
    ")
      ->bindValue(':date', $fromDate)
      ->execute();

    Yii::$app->db->createCommand("UPDATE `statistic_data_hour_group` sdhg
        INNER JOIN sources s ON s.id=sdhg.source_id
        SET sdhg.user_id = s.user_id, sdhg.stream_id = s.stream_id 
        WHERE sdhg.date >= :date AND (sdhg.user_id != s.user_id OR sdhg.stream_id != s.stream_id)")
      ->bindValue(':date', $fromDate)
      ->execute();

    Yii::$app->db->createCommand("UPDATE `statistic_data_hour_group` sdhg
        INNER JOIN operators bo ON bo.id=sdhg.operator_id
        SET sdhg.country_id=bo.country_id 
        WHERE sdhg.date >= :date AND sdhg.country_id != bo.country_id")
      ->bindValue(':date', $fromDate)
      ->execute();

  }
}
