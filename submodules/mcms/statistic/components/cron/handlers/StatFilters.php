<?php

namespace mcms\statistic\components\cron\handlers;

use common\components\ClickhouseHelper;
use mcms\promo\models\Country;
use mcms\promo\models\Landing;
use mcms\promo\models\Operator;
use mcms\promo\models\Source;
use mcms\promo\models\Stream;
use mcms\statistic\components\cron\AbstractTableHandler;
use Yii;
use yii\db\Connection;
use yii\db\Query;
use kak\clickhouse\Query as ClickhouseQuery;

class StatFilters extends AbstractTableHandler
{
  /**
   * запускает обработчик
   */
  function run()
  {

    // TODO: понять почему удаляли (хотя первый DELETE удаляет 0 строк за 30сек)
//    Yii::$app->db->createCommand("
//      DELETE sf
//      FROM stat_filters sf
//      LEFT JOIN sources_operator_landings sol ON (`sol`.`landing_id` = sf.landing_id AND sol.operator_id = sf.operator_id)
//      LEFT JOIN hits_day_group hdg ON (hdg.landing_id = sf.landing_id AND hdg.operator_id = sf.operator_id)
//      WHERE hdg.landing_id IS NULL AND sol.landing_id IS NULL AND sf.landing_id <> 0 AND sf.operator_id <> 0
//    ")->execute();

//    if ($sourcesId = Source::getInactiveSourcesQuery()->select('id')->andWhere(['>=', 'updated_at', $this->params->fromTime])->column()) {
//      Yii::$app->db->createCommand("
//        DELETE s FROM `stat_filters` AS `s`
//        LEFT JOIN `hits_day_group` `h` ON `s`.`source_id` = `h`.`source_id`
//        WHERE `h`.`source_id` IS NULL AND s.source_id in (" . implode(', ', $sourcesId) . ")
//      ")
//        ->execute();
//    }

//////    if ($streamsId = Stream::getInactiveStreamsQuery()->select('id')->andWhere(['>=', 'updated_at', $this->params->fromTime])->column()) {
//////      Yii::$app->db->createCommand("
//////        DELETE s FROM `stat_filters` AS `s`
//////        LEFT JOIN `hits_day_group` `h` ON `s`.`stream_id` = `h`.`stream_id`
//////        WHERE `h`.`stream_id` IS NULL AND s.stream_id in (" . implode(', ', $streamsId) . ")
//////      ")->execute();
//////    }
//////
//////    if ($landingsId = Landing::getInactiveLandingQuery()->select('id')->andWhere(['>=', 'updated_at', $this->params->fromTime])->column()) {
//////      Yii::$app->db->createCommand("
//////        DELETE s FROM `stat_filters` AS `s`
//////        LEFT JOIN `hits_day_group` `h` ON `s`.`landing_id` = `h`.`landing_id`
//////        WHERE `h`.`landing_id` IS NULL AND s.landing_id in (" . implode(', ', $landingsId) . ")
//////      ")->execute();
//////    }
////
////    if ($operatorsId = Operator::getInactiveOperatorQuery()->select('id')->andWhere(['>=', 'updated_at', $this->params->fromTime])->column()) {
////      Yii::$app->db->createCommand("
////        DELETE s FROM `stat_filters` AS `s`
////        LEFT JOIN `hits_day_group` `h` ON `s`.`operator_id` = `h`.`operator_id`
////        WHERE `h`.`operator_id` IS NULL AND s.operator_id in (" . implode(', ', $operatorsId) . ")
////      ")->execute();
////    }
//
//    if ($countiesId = Country::getInactiveCountryQuery()->select('id')->andWhere(['>=', 'updated_at', $this->params->fromTime])->column()) {
//      Yii::$app->db->createCommand("
//        DELETE s FROM `stat_filters` AS `s`
//        LEFT JOIN `hits_day_group` `h` ON `s`.`country_id` = `h`.`country_id`
//        WHERE `h`.`country_id` IS NULL AND s.country_id in (" . implode(', ', $countiesId) . ")
//      ")->execute();
//    }

    Yii::$app->db->createCommand("
      INSERT INTO `stat_filters`
      SELECT 
        `user_id`, 
        `landing_id`, 
        `operator_id`,
        `country_id`,
        `platform_id`, 
        `landing_pay_type_id`, 
        `provider_id`, 
        `source_id`, 
        `stream_id` 
      FROM `hits_day_group`
      where `hits_day_group`.`date` > :date
      AND `hits_day_group`.`count_hits` > `hits_day_group`.`count_tb`
      GROUP BY 
        `user_id`, 
        `landing_id`, 
        `operator_id`,
        `country_id`,
        `platform_id`, 
        `landing_pay_type_id`, 
        `provider_id`, 
        `source_id`, 
        `stream_id` 
      ON DUPLICATE KEY UPDATE
          user_id = VALUES(user_id),
          landing_id = VALUES(landing_id),
          operator_id = VALUES(operator_id),
          country_id = VALUES(country_id),
          platform_id = VALUES(platform_id),
          landing_pay_type_id = VALUES(landing_pay_type_id),
          provider_id = VALUES(provider_id),
          source_id = VALUES(source_id),
          stream_id = VALUES(stream_id)
    ")
      ->bindValue(':date', $this->params->fromDate)
      ->execute();

    Yii::$app->db->createCommand("
      INSERT INTO `stat_filters`
      SELECT
        `sources`.`user_id`, 
        0 as `landing_id`, 
        0 as `operator_id`,
        0 as `country_id`,
        0 as `platform_id`, 
        0 as `landing_pay_type_id`, 
        0 as `provider_id`, 
        `id` as `source_id`, 
        `sources`.`stream_id`
      FROM `sources`
      WHERE `sources`.`updated_at` > UNIX_TIMESTAMP(:date) AND status = 1
      ON DUPLICATE KEY UPDATE
          user_id = VALUES(user_id),
          landing_id = landing_id,
          operator_id = operator_id,
          country_id = country_id,
          platform_id = platform_id,
          landing_pay_type_id = landing_pay_type_id,
          provider_id = provider_id,
          source_id = VALUES(source_id),
          stream_id = VALUES(stream_id)
    ")
      ->bindValue(':date', $this->params->fromDate)
      ->execute();

    Yii::$app->db->createCommand("
      INSERT INTO `stat_filters`
      SELECT
        `streams`.`user_id`, 
        0 as `landing_id`, 
        0 as `operator_id`,
        0 as `country_id`,
        0 as `platform_id`, 
        0 as `landing_pay_type_id`, 
        0 as `provider_id`, 
        0 as `source_id`, 
        `id` as `stream_id`
      FROM `streams`
      WHERE `streams`.`updated_at` > UNIX_TIMESTAMP(:date) AND status = 1
      ON DUPLICATE KEY UPDATE
          user_id = VALUES(user_id),
          landing_id = landing_id,
          operator_id = operator_id,
          country_id = country_id,
          platform_id = platform_id,
          landing_pay_type_id = landing_pay_type_id,
          provider_id = provider_id,
          source_id = source_id,
          stream_id = VALUES(stream_id)
    ")
      ->bindValue(':date', $this->params->fromDate)
      ->execute();

    Yii::$app->db->createCommand("
      INSERT INTO `stat_filters`
      SELECT 
        0 AS `user_id`, 
        `landing_id`, 
        0 AS `operator_id`,
        0 AS `country_id`,
        0 AS `platform_id`, 
        0 AS `landing_pay_type_id`, 
        `provider_id`, 
        0 as `source_id`, 
        0 AS `stream_id`
         FROM `sources_operator_landings` sol
      INNER JOIN landings l ON l.id = sol.landing_id
      INNER JOIN sources s ON s.id = sol.source_id AND s.status = 1
      WHERE `l`.`updated_at` > UNIX_TIMESTAMP(:date) AND l.status = 1
      ORDER BY NULL 
      ON DUPLICATE KEY UPDATE
          landing_id = VALUES(landing_id),
          provider_id = VALUES(provider_id),
          source_id = VALUES(source_id)
    ")
      ->bindValue(':date', $this->params->fromDate)
      ->execute();

    Yii::$app->db->createCommand("
      INSERT INTO `stat_filters`
      SELECT 
        s.user_id AS user_id,
        sol.landing_id AS landing_id,
        sol.operator_id AS operator_id,
        c.id AS country_id,
        0 AS platform_id,
        0 AS landing_pay_type_id,
        0 AS provider_id,
        sol.source_id AS `source_id`,
        s.stream_id AS stream_id
      FROM `sources_operator_landings` sol
      INNER JOIN operators o ON o.id = sol.operator_id
      INNER JOIN `countries` c ON c.id = o.`country_id`
      INNER JOIN sources s ON s.id = sol.`source_id`
      WHERE s.status = 1
      ON DUPLICATE KEY UPDATE
          user_id = VALUES(user_id),
          landing_id = VALUES(landing_id),
          operator_id = VALUES(stat_filters.operator_id),
          country_id = VALUES(country_id),
          platform_id = platform_id,
          landing_pay_type_id = landing_pay_type_id,
          provider_id = provider_id,
          source_id = VALUES(source_id),
          stream_id = VALUES(stream_id)
      ")
      ->bindValue(':date', $this->params->fromDate)
      ->execute();

    $this->syncTables();
  }

  private function syncTables()
  {
    /** @var Connection $clickhouse */
    $clickhouse = Yii::$app->clickhouse;
    $clickhouse->createCommand('insert into stat_filters select * from ' . ClickhouseHelper::getClickhouseMysqlConnectionString('stat_filters') . ' as stats')->execute();
    $clickhouse->createCommand('optimize table stat_filters')->execute();
  }
}