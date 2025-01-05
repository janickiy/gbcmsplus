<?php

namespace mcms\statistic\components\cron\handlers;

use mcms\statistic\components\cron\AbstractTableHandler;
use Yii;

/**
 * Храним кол-во ТБ хитов, которые отправили на продажу
 */
class SellTbHitsGrouped extends AbstractTableHandler
{
  public function run()
  {
    Yii::$app->db->createCommand("
      INSERT INTO `sell_tb_hits_grouped` (
        `count_hits`, 
        `date`, 
        `hour`,
        `source_id`, 
        `operator_id`, 
        `platform_id`, 
        `tb_provider_id`, 
        `category_id`, 
        `landing_id`, 
        `landing_pay_type_id`)
      SELECT 
        COUNT(hit_id) AS count_hits,
        `date`, 
        `hour`, 
        `source_id`, 
        `operator_id`, 
        `platform_id`, 
        `tb_provider_id`, 
        `category_id`, 
        0, 
        0
      FROM sell_tb_hits s
      WHERE {$this->params->dateQuery}
      GROUP BY `date`, `source_id`, `operator_id`, `platform_id`, `hour`, `tb_provider_id`, `category_id`
      ORDER BY NULL
      ON DUPLICATE KEY UPDATE
        count_hits = VALUES(count_hits)")
      ->execute();

    Yii::$app->db->createCommand("UPDATE `sell_tb_hits_grouped` gr
        INNER JOIN sources s ON s.id = gr.source_id
        SET gr.user_id = s.user_id, gr.stream_id = s.stream_id 
        WHERE {$this->params->dateQuery} AND (gr.user_id != s.user_id OR gr.stream_id != s.stream_id)")
      ->execute();

    Yii::$app->db->createCommand("UPDATE `sell_tb_hits_grouped` gr
        INNER JOIN operators bo ON bo.id = gr.operator_id
        SET gr.country_id = bo.country_id 
        WHERE {$this->params->dateQuery} AND gr.country_id != bo.country_id")
      ->execute();
  }
}
