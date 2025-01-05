<?php

namespace mcms\statistic\components\cron\handlers;

use mcms\statistic\components\cron\AbstractTableHandler;
use Yii;

/**
 * Class HitsByDate
 * @package mcms\statistic\components\cron\handlers
 */
class HitsByDate extends AbstractTableHandler
{
  public function run()
  {
    // автоматом выбирает ошибочный индекс, ставим сами USE INDEX (`hdhg_group_by_day`)
    Yii::$app->db->createCommand("INSERT INTO `hits_day_group`
        (`count_hits`, `count_uniques`, `count_tb`, `count_unique_tb`, `date`,
        `source_id`, `landing_id`, `operator_id`, `platform_id`, `landing_pay_type_id`, `is_cpa`)
        SELECT SUM(count_hits) AS count_hits,
          SUM(count_uniques) AS count_uniques,
          SUM(count_tb) AS count_tb,
          SUM(count_unique_tb) AS count_unique_tb,
          `date`, `source_id`, `landing_id`, `operator_id`, `platform_id`, `landing_pay_type_id`, `is_cpa`
        FROM `hits_day_hour_group`
        WHERE date >= :date
        GROUP BY `date`, `source_id`, `landing_id`, `operator_id`, `platform_id`, `landing_pay_type_id`, `is_cpa`
        ORDER BY NULL
        ON DUPLICATE KEY UPDATE
          count_hits = VALUES(count_hits),
          count_uniques = VALUES(count_uniques),
          count_tb = VALUES(count_tb),
          count_unique_tb = VALUES(count_unique_tb)
          ")
      ->bindValue(':date', $this->params->fromDate)
      ->execute();

    Yii::$app->db->createCommand("UPDATE `hits_day_group` hdg
        INNER JOIN landings l ON l.id=hdg.landing_id
        SET hdg.provider_id=l.provider_id 
        WHERE date >= :date AND hdg.provider_id != l.provider_id")
      ->bindValue(':date', $this->params->fromDate)
      ->execute();

    Yii::$app->db->createCommand("UPDATE `hits_day_group` hdg
        INNER JOIN sources s ON s.id=hdg.source_id
        SET hdg.user_id = s.user_id, hdg.stream_id = s.stream_id 
        WHERE date >= :date AND (hdg.user_id != s.user_id OR hdg.stream_id != s.stream_id)")
      ->bindValue(':date', $this->params->fromDate)
      ->execute();

    Yii::$app->db->createCommand("UPDATE `hits_day_group` hdg
        INNER JOIN operators bo ON bo.id=hdg.operator_id
        SET hdg.country_id=bo.country_id 
        WHERE date >= :date AND hdg.country_id != bo.country_id")
      ->bindValue(':date', $this->params->fromDate)
      ->execute();
  }

}
