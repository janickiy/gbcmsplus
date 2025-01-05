<?php

namespace mcms\statistic\components\cron\handlers;

use mcms\common\helpers\ArrayHelper;
use mcms\statistic\components\cron\AbstractTableHandler;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\Exception;

/**
 * Заполнение таблицы statistic_rebills_day_group данными
 */
class StatisticAnalytics extends AbstractTableHandler
{
  protected $dateOn;

  /**
   * @inheritdoc
   */
  public function init()
  {
    parent::init();

    $this->dateOn = ArrayHelper::getValue(Yii::$app->params, 'analyticsDateOn', '2020-01-01');
  }
  /**
   * @throws InvalidConfigException
   * @throws Exception
   */
  public function run()
  {
    // Заполняем данными по подпискам
    Yii::$app->db->createCommand("INSERT INTO `statistic_analytics`  (
        `count_ons_revshare`, `count_ons_sold`, `count_ons_rejected`,
        `date_on`, `date`, `source_id`, `landing_id`, `operator_id`, 
        `platform_id`, `landing_pay_type_id`, `is_fake`, `currency_id`, `provider_id`
        )
        SELECT
          COUNT(IF(s.is_cpa = 0 AND ss.id IS NULL, s.id, null)) AS count_ons_revshare,
          COUNT(IF(s.is_cpa = 1 AND ss.id IS NOT NULL, s.id, null)) AS count_ons_sold,
          COUNT(IF(s.is_cpa = 1 AND ss.id IS NULL, s.id, null)) AS count_ons_rejected,
          s.date as date_on,
          s.`date`, s.`source_id`, s.`landing_id`, s.`operator_id`, 
          s.`platform_id`, s.`landing_pay_type_id`, s.is_fake, s.`currency_id`, s.`provider_id`
        FROM `subscriptions` s
          LEFT JOIN sold_subscriptions ss ON s.hit_id = ss.hit_id
        WHERE s.date >= :date
        GROUP BY date_on, s.`date`, s.`source_id`, s.`landing_id`, s.`operator_id`, s.`platform_id`, s.`landing_pay_type_id`, s.is_fake
        ORDER BY NULL
        ON DUPLICATE KEY UPDATE 
          count_ons_revshare = VALUES(count_ons_revshare),
          count_ons_sold = VALUES(count_ons_sold),
          count_ons_rejected = VALUES(count_ons_rejected)
      ")
      ->bindValue(':date', $this->params->fromDate)
      ->execute();

    // Заполняем данными по отпискам с указанием дня подписки
    Yii::$app->db->createCommand("INSERT INTO `statistic_analytics`  (
        `count_offs_revshare`, `count_offs_sold`, `count_offs_rejected`,
        `date_on`, `date_diff`, `date`, `source_id`, `landing_id`, `operator_id`, 
        `platform_id`, `landing_pay_type_id`, `is_fake`, `currency_id`, `provider_id`
        )
        SELECT
          COUNT(IF(s.is_cpa = 0 AND ss.id IS NULL, so.id, null)) AS count_offs_revshare,
          COUNT(IF(s.is_cpa = 1 AND ss.id IS NOT NULL, so.id, null)) AS count_offs_sold,
          COUNT(IF(s.is_cpa = 1 AND ss.id IS NULL, so.id, null)) AS count_offs_rejected,
          s.date as date_on, DATEDIFF(so.date, s.date) as date_diff,
          so.`date`, s.`source_id`, s.`landing_id`, s.`operator_id`, 
          s.`platform_id`, s.`landing_pay_type_id`, s.is_fake, s.`currency_id`, s.`provider_id`
        FROM `subscriptions` s
          INNER JOIN subscription_offs so on so.hit_id = s.hit_id
          LEFT JOIN sold_subscriptions ss ON s.hit_id = ss.hit_id
        WHERE s.date >= :dateOn
          AND so.date >= :date
        GROUP BY date_on, so.`date`, s.`source_id`, s.`landing_id`, s.`operator_id`, s.`platform_id`, s.`landing_pay_type_id`, s.is_fake
        ORDER BY NULL
        ON DUPLICATE KEY UPDATE
          count_offs_revshare = VALUES(count_offs_revshare), 
          count_offs_sold = VALUES(count_offs_sold), 
          count_offs_rejected = VALUES(count_offs_rejected)
      ")
      ->bindValue(':dateOn', $this->dateOn)
      ->bindValue(':date', $this->params->fromDate)
      ->execute();

    // Заполняем данными по ребиллам с указанием дня подписки
    Yii::$app->db->createCommand("INSERT INTO `statistic_analytics`  (
        `count_rebills_revshare`, `count_rebills_sold`, `count_rebills_rejected`,
        `profit_rub_revshare`, `profit_usd_revshare`, `profit_eur_revshare`,
        `profit_rub_sold`, `profit_usd_sold`, `profit_eur_sold`,
        `profit_rub_rejected`, `profit_usd_rejected`, `profit_eur_rejected`,
        `date_on`, `date_diff`, `date`, `source_id`, `landing_id`, `operator_id`, 
        `platform_id`, `landing_pay_type_id`, `is_fake`, `currency_id`, `provider_id`
        )
        SELECT
          COUNT(IF(s.is_cpa = 0 AND ss.id IS NULL, sr.id, null)) AS count_rebills_revshare,
          COUNT(IF(s.is_cpa = 1 AND ss.id IS NOT NULL, sr.id, null)) AS count_rebills_sold,
          COUNT(IF(s.is_cpa = 1 AND ss.id IS NULL, sr.id, null)) AS count_rebills_rejected,
          SUM(IF(s.is_cpa = 0 AND ss.id IS NULL, sr.real_profit_rub, 0)) AS profit_rub_revshare,
          SUM(IF(s.is_cpa = 0 AND ss.id IS NULL, sr.real_profit_usd, 0)) AS profit_usd_revshare,
          SUM(IF(s.is_cpa = 0 AND ss.id IS NULL, sr.real_profit_eur, 0)) AS profit_eur_revshare,
          SUM(IF(s.is_cpa = 1 AND ss.id IS NOT NULL, sr.real_profit_rub, 0)) AS profit_rub_sold,
          SUM(IF(s.is_cpa = 1 AND ss.id IS NOT NULL, sr.real_profit_usd, 0)) AS profit_usd_sold,
          SUM(IF(s.is_cpa = 1 AND ss.id IS NOT NULL, sr.real_profit_eur, 0)) AS profit_eur_sold,
          SUM(IF(s.is_cpa = 1 AND ss.id IS NULL, sr.real_profit_rub, 0)) AS profit_rub_rejected,
          SUM(IF(s.is_cpa = 1 AND ss.id IS NULL, sr.real_profit_usd, 0)) AS profit_usd_rejected,
          SUM(IF(s.is_cpa = 1 AND ss.id IS NULL, sr.real_profit_eur, 0)) AS profit_eur_rejected,
          s.date as date_on, DATEDIFF(sr.date, s.date) as date_diff,
          sr.`date`, s.`source_id`, s.`landing_id`, s.`operator_id`, 
          s.`platform_id`, s.`landing_pay_type_id`, s.is_fake, s.`currency_id`, s.`provider_id`
        FROM `subscriptions` s
          INNER JOIN subscription_rebills sr on sr.hit_id = s.hit_id
          LEFT JOIN sold_subscriptions ss ON s.hit_id = ss.hit_id
        WHERE s.date >= :dateOn
          AND sr.date >= :date
          AND sr.date >= s.date # почему-то такие записи иногда встречаются
        GROUP BY date_on, sr.`date`, s.`source_id`, s.`landing_id`, s.`operator_id`, s.`platform_id`, s.`landing_pay_type_id`, s.is_fake
        ORDER BY NULL
        ON DUPLICATE KEY UPDATE 
          count_rebills_revshare = VALUES(count_rebills_revshare), 
          count_rebills_sold = VALUES(count_rebills_sold), 
          count_rebills_rejected = VALUES(count_rebills_rejected),
          profit_rub_revshare = VALUES(profit_rub_revshare),
          profit_usd_revshare = VALUES(profit_usd_revshare),
          profit_eur_revshare = VALUES(profit_eur_revshare),
          profit_rub_sold = VALUES(profit_rub_sold),
          profit_usd_sold = VALUES(profit_usd_sold),
          profit_eur_sold = VALUES(profit_eur_sold),
          profit_rub_rejected = VALUES(profit_rub_rejected),
          profit_usd_rejected = VALUES(profit_usd_rejected),
          profit_eur_rejected = VALUES(profit_eur_rejected)
      ")
      ->bindValue(':dateOn', $this->dateOn)
      ->bindValue(':date', $this->params->fromDate)
      ->execute();

    // Заполняем все группы значением по подпискам от первого дня

    // 1. создаем временную таблицу со списком дат, в которые были хоть какие-то события
    // 2. по каждой дате получаем весь список групп из первого дня (все доступные событиия)
    // 3. заполняем все даты статы всеми группами, чтобы не было пробелов
    Yii::$app->db->createCommand("
      DROP TEMPORARY TABLE IF EXISTS _tmp_statistic_analytics_date;
      CREATE TEMPORARY TABLE _tmp_statistic_analytics_date
      SELECT
        `date_on`,
        `date`,
        `date_diff`
      FROM `statistic_analytics`
      WHERE `date` >= :date
        AND `date` <> `date_on`
      GROUP BY `date_on`, `date`;
      
      INSERT IGNORE INTO statistic_analytics (
        `count_ons_revshare`,
        `count_ons_sold`,
        `count_ons_rejected`,
        `date_on`,
        `date`,
        `source_id`,
        `landing_id`,
        `operator_id`,
        `platform_id`,
        `landing_pay_type_id`,
        `is_fake`,
        `date_diff`
      )
      SELECT
        0,
        0,
        0,
        tmp.`date_on`,
        tmp.`date`,
        st.`source_id`,
        st.`landing_id`,
        st.`operator_id`,
        st.`platform_id`,
        st.`landing_pay_type_id`,
        st.`is_fake`,
        tmp.`date_diff`
      FROM _tmp_statistic_analytics_date tmp
      INNER JOIN (
        SELECT
          st.`date_on`,
          st.`date`,
          st.`source_id`,
          st.`landing_id`,
          st.`operator_id`,
          st.`platform_id`,
          st.`landing_pay_type_id`,
          st.`is_fake`
        FROM statistic_analytics st
        WHERE st.date_diff = 0
        GROUP BY st.date_on, st.date, st.`source_id`, st.`landing_id`, st.`operator_id`, st.`platform_id`, st.`landing_pay_type_id`, st.is_fake
      ) st ON st.date_on = tmp.date_on;
      ")
      ->bindValue(':date', $this->params->fromDate)
      ->execute();

    // Заполненяем накопительный итог по отпискам, чтоб можно было посчитать количество активных
    // Заготовка подзапроса
    $subQuery = '
      SELECT SUM(:field)
      FROM statistic_analytics st_sub
        USE INDEX (`idx-statistic_analytics-date_diff-end`)
      WHERE
        st_sub.date_on = st.date_on
        AND st_sub.source_id = st.source_id
        AND st_sub.landing_id = st.landing_id
        AND st_sub.operator_id = st.operator_id
        AND st_sub.platform_id = st.platform_id
        AND st_sub.landing_pay_type_id = st.landing_pay_type_id
        AND st_sub.is_fake = st.is_fake
        AND st_sub.date_diff <= st.date_diff
    ';
    $subQueryRevshare = strtr($subQuery, [':field' => 'count_offs_revshare']);
    $subQuerySold = strtr($subQuery, [':field' => 'count_offs_sold']);
    $subQueryRejected = strtr($subQuery, [':field' => 'count_offs_rejected']);

    // Основной запрос
    Yii::$app->db->createCommand("INSERT INTO `statistic_analytics`  (
        cumulative_offs_revshare,
        cumulative_offs_sold,
        cumulative_offs_rejected,
        `date_on`, `date_diff`, `date`, `source_id`, `landing_id`, `operator_id`, 
        `platform_id`, `landing_pay_type_id`, `is_fake`
        )
        SELECT
          ($subQueryRevshare) AS cumulative_offs_revshare,
          ($subQuerySold) AS cumulative_offs_sold,
          ($subQueryRejected) AS cumulative_offs_rejected,
          st.date_on, st.date_diff,
          st.`date`, st.`source_id`, st.`landing_id`, st.`operator_id`, 
          st.`platform_id`, st.`landing_pay_type_id`, st.is_fake
        FROM `statistic_analytics` st
        WHERE st.date >= :date
        GROUP BY st.date_on, st.`date`, st.`source_id`, st.`landing_id`, st.`operator_id`, st.`platform_id`, st.`landing_pay_type_id`, st.is_fake
        ORDER BY NULL
        ON DUPLICATE KEY UPDATE 
          cumulative_offs_revshare = VALUES(cumulative_offs_revshare), 
          cumulative_offs_sold = VALUES(cumulative_offs_sold), 
          cumulative_offs_rejected = VALUES(cumulative_offs_rejected)
      ")
      ->bindValue(':date', $this->params->fromDate)
      ->execute();

    // Заполняем все даты одинаковым значением количества подписок, чтоб считать активных
    // Заготовка подзапроса
    $subQuery = '
      SELECT :field
      FROM statistic_analytics st_sub
      WHERE
        st_sub.date_on = st.date_on
        AND st_sub.date = st.date_on
        AND st_sub.source_id = st.source_id
        AND st_sub.landing_id = st.landing_id
        AND st_sub.operator_id = st.operator_id
        AND st_sub.platform_id = st.platform_id
        AND st_sub.landing_pay_type_id = st.landing_pay_type_id
        AND st_sub.is_fake = st.is_fake
    ';
    $subQueryRevshare = strtr($subQuery, [':field' => 'count_ons_revshare']);
    $subQuerySold = strtr($subQuery, [':field' => 'count_ons_sold']);
    $subQueryRejected = strtr($subQuery, [':field' => 'count_ons_rejected']);

    // Основной запрос
    Yii::$app->db->createCommand("UPDATE `statistic_analytics` st 
        SET
        st.count_ons_revshare = ($subQueryRevshare),
        st.count_ons_sold = ($subQuerySold),
        st.count_ons_rejected = ($subQueryRejected)
        WHERE st.date_diff > 0
          AND st.date >= :date
      ")
      ->bindValue(':date', $this->params->fromDate)
      ->execute();

    // Заполнение статы данными, которые не были добавлены в предыдущих запросах
    Yii::$app->db->createCommand(
      "
      UPDATE statistic_analytics st
        LEFT JOIN sources s ON s.id = st.source_id
        LEFT JOIN operators op ON op.id = st.operator_id
        LEFT JOIN landings l ON l.id = st.landing_id
      SET st.user_id = ifnull(s.user_id, 0),
          st.stream_id = ifnull(s.stream_id, 0),
          st.country_id = ifnull(op.country_id, 0),
          st.provider_id = ifnull(l.provider_id, 0)
      WHERE st.date >= :date
        AND st.user_id = 0
        AND st.stream_id = 0
        AND st.country_id = 0
      ")
      ->bindValue(':date', $this->params->fromDate)
      ->execute();
  }
}