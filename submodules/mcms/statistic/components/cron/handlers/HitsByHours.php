<?php

namespace mcms\statistic\components\cron\handlers;

use mcms\statistic\components\cron\AbstractTableHandler;
use Yii;

/**
 * Class HitsByHours
 * @package mcms\statistic\components\cron\handlers
 */
class HitsByHours extends AbstractTableHandler
{
  public function run()
  {
    // Обновим существующее кол-во хитов для тех строк, у которых кол-во строк по группировке в hits стало равно NULL
    // (например если партнер продал подписку мы передаем хит другому юзеру)
    // все остальные строки обновит следующий запрос INSERT ON DUPLICATE UPDATE

    Yii::$app->db->createCommand("UPDATE hits_day_hour_group AS hdhg
        LEFT JOIN `hits` AS h ON
        h.date = hdhg.date AND
        h.hour = hdhg.hour AND
        h.source_id = hdhg.source_id AND
        h.landing_id = hdhg.landing_id AND
        h.operator_id = hdhg.operator_id AND
        h.platform_id = hdhg.platform_id AND
        h.landing_pay_type_id = hdhg.landing_pay_type_id AND
        h.is_cpa = hdhg.is_cpa
      SET 
        hdhg.count_hits = 0,
        hdhg.count_tb = 0,
        hdhg.count_uniques = 0,
        hdhg.count_unique_tb = 0
      WHERE hdhg.date >= :date AND h.id IS NULL;
      ")
      ->bindValue(':date', $this->params->fromDate)
      ->execute();


    Yii::$app->db->createCommand("INSERT INTO `hits_day_hour_group`
        (`count_hits`, `count_uniques`, `count_tb`, `count_unique_tb`, `date`, `hour`,
        `source_id`, `landing_id`, `operator_id`, `platform_id`, `landing_pay_type_id`, `is_cpa`)
        SELECT COUNT(id) AS count_hits,
          SUM(is_unique) AS count_uniques,
          SUM(IF(is_tb > 0, 1, 0)) AS count_tb,
          SUM(IF(is_tb > 0 AND is_unique = 1, 1, 0)) AS count_unique_tb,
          `date`, `hour`, `source_id`, `landing_id`, `operator_id`, `platform_id`, `landing_pay_type_id`, `is_cpa`
        FROM `hits`
        WHERE {$this->params->dateQuery}
        GROUP BY `date`, `source_id`, `landing_id`, `operator_id`, `platform_id`, `hour`, `landing_pay_type_id`, `is_cpa`
        ORDER BY NULL
        ON DUPLICATE KEY UPDATE
        count_hits = VALUES(count_hits),
        count_uniques = VALUES(count_uniques),
        count_tb = VALUES(count_tb),
        count_unique_tb = VALUES(count_unique_tb)")
      ->execute();

    Yii::$app->db->createCommand("UPDATE `hits_day_hour_group` hdhg
        INNER JOIN landings l ON l.id = hdhg.landing_id
        SET hdhg.provider_id = l.provider_id 
        WHERE {$this->params->dateQuery} AND hdhg.provider_id != l.provider_id")
      ->execute();

    Yii::$app->db->createCommand("UPDATE `hits_day_hour_group` hdhg
        INNER JOIN sources s ON s.id = hdhg.source_id
        SET hdhg.user_id = s.user_id, hdhg.stream_id = s.stream_id 
        WHERE {$this->params->dateQuery} AND (hdhg.user_id != s.user_id OR hdhg.stream_id != s.stream_id)")
      ->execute();

    Yii::$app->db->createCommand("UPDATE `hits_day_hour_group` hdhg
        INNER JOIN operators bo ON bo.id = hdhg.operator_id
        SET hdhg.country_id = bo.country_id 
        WHERE {$this->params->dateQuery} AND hdhg.country_id != bo.country_id")
      ->execute();
  }

}
