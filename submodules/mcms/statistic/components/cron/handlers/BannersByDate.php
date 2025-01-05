<?php

namespace mcms\statistic\components\cron\handlers;

use mcms\statistic\components\cron\AbstractTableHandler;
use Yii;

/**
 * Class BannersByDate
 * @package mcms\statistic\components\cron\handlers
 */
class BannersByDate extends AbstractTableHandler
{
  public function run()
  {
    $typeWebmasterSources = Yii::$app->getModule('promo')->api('sources')->getTypeWebmasterSource();
    $db = Yii::$app->db;

    // показы и хиты
    $bannerShowsAndHitsSQL = <<<SQL
INSERT INTO banners_day_group
(banner_id, date, source_id, operator_id, platform_id, count_shows, count_hits)
SELECT
  bs.banner_id,
  bs.date,
  bs.source_id,
  bs.operator_id,
  bs.platform_id,
  COUNT(1) as count_shows,
  COUNT(bs.hit_id) as count_hits
FROM banner_shows bs
INNER JOIN sources s
  ON s.id = bs.source_id
WHERE date >= :fromDate
  AND s.source_type = :sourceType
GROUP BY banner_id, date, source_id, operator_id, platform_id
ORDER BY NULL
ON DUPLICATE KEY 
UPDATE 
  count_shows = VALUES(count_shows),
  count_hits = VALUES(count_hits)
SQL;

    $db->createCommand($bannerShowsAndHitsSQL)
      ->bindValue(':fromDate', $this->params->fromDate)
      ->bindValue(':sourceType', $typeWebmasterSources)
      ->execute();

    // подписки
    $bannerSubscriptionsSQL = <<<SQL
INSERT INTO `banners_day_group`
(`count_ons`, `banner_id`, `date`, `source_id`, `operator_id`, `platform_id`, `is_fake`)
SELECT
  COUNT(1) AS count_ons,
  bs.`banner_id`,
  bs.`date`,
  bs.`source_id`,
  bs.`operator_id`,
  bs.`platform_id`,
  ss.`is_fake`
FROM `banner_shows` bs
INNER JOIN sources s
  ON s.id = bs.source_id
INNER JOIN `search_subscriptions` ss
  ON bs.`hit_id` = ss.`hit_id`
WHERE bs.`date` >= :fromDate
  AND s.source_type = :sourceType
GROUP BY bs.`banner_id`, bs.`date`, bs.`source_id`, bs.`operator_id`, bs.`platform_id`, ss.`is_fake`
ORDER BY NULL
ON DUPLICATE KEY 
UPDATE 
  count_ons = VALUES(count_ons)
SQL;

    $db->createCommand($bannerSubscriptionsSQL)
      ->bindValue(':fromDate', $this->params->fromDate)
      ->bindValue(':sourceType', $typeWebmasterSources)
      ->execute();

    // одноразовые подписки
    $bannerOnetimesSQL = <<<SQL
INSERT INTO `banners_day_group`
(`count_onetimes`, `banner_id`, `date`, `source_id`, `operator_id`, `platform_id`, `is_visible_to_partner`)
SELECT
  COUNT(1) AS count_onetimes,
  bs.`banner_id`,
  bs.`date`,
  bs.`source_id`,
  bs.`operator_id`,
  bs.`platform_id`,
  os.`is_visible_to_partner`
FROM `banner_shows` bs
INNER JOIN sources s
  ON s.id = bs.source_id
INNER JOIN `onetime_subscriptions` os
  ON bs.`hit_id` = os.`hit_id`
WHERE bs.`date` >= :fromDate
  AND s.source_type = :sourceType
GROUP BY bs.`banner_id`, bs.`date`, bs.`source_id`, bs.`operator_id`, bs.`platform_id`, os.`is_visible_to_partner`
ORDER BY NULL
ON DUPLICATE KEY 
UPDATE 
  count_onetimes = VALUES(count_onetimes)
SQL;

    $db->createCommand($bannerOnetimesSQL)
      ->bindValue(':fromDate', $this->params->fromDate)
      ->bindValue(':sourceType', $typeWebmasterSources)
      ->execute();


    // выкупы
    $bannerSoldSQL = <<<SQL
INSERT INTO `banners_day_group`
(`count_solds`, `banner_id`, `date`, `source_id`, `operator_id`, `platform_id`, `is_visible_to_partner`)
SELECT
  COUNT(1) AS count_solds,
  bs.`banner_id`,
  bs.`date`,
  bs.`source_id`,
  bs.`operator_id`,
  bs.`platform_id`,
  ss.`is_visible_to_partner`
FROM `banner_shows` bs
INNER JOIN sources s
  ON s.id = bs.source_id
INNER JOIN `sold_subscriptions` ss
  ON bs.`hit_id` = ss.`hit_id`
WHERE bs.`date` >= :fromDate
  AND s.source_type = :sourceType
GROUP BY bs.`banner_id`, bs.`date`, bs.`source_id`, bs.`operator_id`, bs.`platform_id`, ss.`is_visible_to_partner`
ORDER BY NULL
ON DUPLICATE KEY 
UPDATE 
  count_solds = VALUES(count_solds)
SQL;

    $db->createCommand($bannerSoldSQL)
      ->bindValue(':fromDate', $this->params->fromDate)
      ->bindValue(':sourceType', $typeWebmasterSources)
      ->execute();

    // записываем user_id и country_id

    $db->createCommand("
      UPDATE `banners_day_group` bdg
      INNER JOIN sources s ON s.id=bdg.source_id
      SET bdg.user_id = s.user_id
      WHERE date >= :fromDate
    ")->bindValue(':fromDate', $this->params->fromDate)->execute();

    $db->createCommand("
      UPDATE `banners_day_group` bdg
      INNER JOIN operators bo ON bo.id=bdg.operator_id
      SET bdg.country_id=bo.country_id 
      WHERE date >= :fromDate
    ")->bindValue(':fromDate', $this->params->fromDate)->execute();

  }
}