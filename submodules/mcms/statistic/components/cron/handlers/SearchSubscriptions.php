<?php

namespace mcms\statistic\components\cron\handlers;

use mcms\statistic\components\cron\AbstractTableHandler;
use Yii;

/**
 * Class SearchSubscriptions
 * @package mcms\statistic\components\cron\handlers
 */
class SearchSubscriptions extends AbstractTableHandler
{
  public function run()
  {
    Yii::$app->db->createCommand("INSERT INTO `search_subscriptions`
        (`hit_id`, `time_on`, `phone`, `source_id`, `landing_id`, `operator_id`, `platform_id`, `landing_pay_type_id`, `is_cpa`, `currency_id`, `provider_id`, `is_fake`)
        SELECT
        `hit_id`, `time`, `phone`, `source_id`, `landing_id`, `operator_id`, `platform_id`, `landing_pay_type_id`, `is_cpa`, `currency_id`, `provider_id`, `is_fake`
        FROM `subscriptions`
        WHERE `time` >= :time
        ORDER BY NULL
        ON DUPLICATE KEY UPDATE phone = VALUES(phone), source_id = VALUES(source_id), time_on = VALUES(time_on)")
      ->bindValue(':time', $this->params->getFromTime())
      ->execute();

    Yii::$app->db->createCommand("INSERT INTO `search_subscriptions`
        (`hit_id`, `time_off`, `source_id`, `landing_id`, `operator_id`, `platform_id`, `landing_pay_type_id`, `is_cpa`, `currency_id`, `provider_id`, `is_fake`)
        SELECT
        `hit_id`, `time`, `source_id`, `landing_id`, `operator_id`, `platform_id`, `landing_pay_type_id`, `is_cpa`, `currency_id`, `provider_id`, `is_fake`
        FROM `subscription_offs`
        WHERE `time` >= :time
        ORDER BY NULL
        ON DUPLICATE KEY UPDATE source_id = VALUES(source_id), time_off = VALUES(time_off)")
      ->bindValue(':time', $this->params->getFromTime())
      ->execute();

    Yii::$app->db->createCommand("INSERT INTO `search_subscriptions`
          (`hit_id`, `time_rebill`, `source_id`, `landing_id`, `operator_id`, `platform_id`, `landing_pay_type_id`, `is_cpa`, `currency_id`, `provider_id`)
        SELECT
          `hit_id`, MAX(`time`) AS `time_rebill`, `source_id`, `landing_id`, `operator_id`, `platform_id`, `landing_pay_type_id`, `is_cpa`, `currency_id`, `provider_id`
        FROM `subscription_rebills`
        WHERE `time` >= :time
        GROUP BY `hit_id`
        ORDER BY NULL
        ON DUPLICATE KEY UPDATE source_id = VALUES(source_id), time_rebill = VALUES(time_rebill)")
      ->bindValue(':time', $this->params->getFromTime())
      ->execute();

    // Устанавливаем поле last_time сразу после добавления пдп, отп, ребиллов.
    // Если запрос опустить ниже, то запросы в промежутке между ним и инсертами
    // будут работать некорректно если у них условие where last_time >= ...
    Yii::$app->db->createCommand("UPDATE `search_subscriptions` ss
        SET last_time = IF(time_off > time_rebill, time_off, IF(time_rebill > time_on, time_rebill, time_on))
        WHERE ss.time_on >= :time OR ss.time_off >= :time OR ss.time_rebill >= :time")
      ->bindValue(':time', $this->params->getFromTime())
      ->execute();

    Yii::$app->db->createCommand("UPDATE `search_subscriptions` ss
        INNER JOIN `subscriptions` ON ss.hit_id = `subscriptions`.hit_id
        SET ss.phone=`subscriptions`.phone
        WHERE last_time >= :time")
      ->bindValue(':time', $this->params->getFromTime())
      ->execute();

    Yii::$app->db->createCommand("UPDATE `search_subscriptions` ss
        INNER JOIN sources s ON s.id=ss.source_id
        SET ss.user_id = s.user_id, ss.stream_id = s.stream_id 
        WHERE last_time >= :time AND (ss.user_id != s.user_id OR ss.stream_id != s.stream_id)")
      ->bindValue(':time', $this->params->getFromTime())
      ->execute();

    Yii::$app->db->createCommand("UPDATE `search_subscriptions` ss
        INNER JOIN operators bo ON bo.id=ss.operator_id
        SET ss.country_id=bo.country_id 
        WHERE last_time >= :time AND ss.country_id != bo.country_id")
      ->bindValue(':time', $this->params->getFromTime())
      ->execute();

    // возможно тут ему не место, но в скрипт выкупа тоже не норм его вставлять
    Yii::$app->db->createCommand("UPDATE `sold_subscriptions` ss
        INNER JOIN operators bo ON bo.id=ss.operator_id
        SET ss.country_id=bo.country_id 
        WHERE time >= :time AND ss.country_id != bo.country_id")
      ->bindValue(':time', $this->params->getFromTime())
      ->execute();

    $fromTime = $this->params->getFromTime();
    for (;;) {
      $toTime = $fromTime + (60 * 5);
      echo "From " . date('Y-m-d H:i:s', $fromTime) . ' to ' . date('Y-m-d H:i:s', $toTime) . PHP_EOL;
      // Для партнеров обновляем суммы для всех валют сразу
      Yii::$app->db->createCommand("
        UPDATE `search_subscriptions` ss
        INNER JOIN (
          SELECT `hit_id`,
          MAX(`time`) AS `time_rebill`,
          COUNT(`id`) AS `count_rebills`,
          SUM(`real_profit_rub`) AS `sum_real_profit_rub`,
          SUM(`real_profit_eur`) AS `sum_real_profit_eur`,
          SUM(`real_profit_usd`) AS `sum_real_profit_usd`,
          SUM(`reseller_profit_rub`) AS `sum_reseller_profit_rub`,
          SUM(`reseller_profit_eur`) AS `sum_reseller_profit_eur`,
          SUM(`reseller_profit_usd`) AS `sum_reseller_profit_usd`,
          SUM(`profit_rub`) AS `sum_profit_rub`,
          SUM(`profit_eur`) AS `sum_profit_eur`,
          SUM(`profit_usd`) AS `sum_profit_usd`
          FROM `subscription_rebills`
          WHERE `hit_id` IN (SELECT DISTINCT `hit_id` FROM `subscription_rebills` WHERE `time` >= :time and `time` < :toTime)
          GROUP BY `hit_id`
          ORDER BY NULL
        ) s ON (s.hit_id = ss.hit_id)
        SET ss.time_rebill = s.time_rebill, ss.count_rebills = s.count_rebills,
        ss.sum_real_profit_rub = s.sum_real_profit_rub, ss.sum_real_profit_eur = s.sum_real_profit_eur, ss.sum_real_profit_usd = s.sum_real_profit_usd,
        ss.sum_reseller_profit_rub = s.sum_reseller_profit_rub, ss.sum_reseller_profit_eur = s.sum_reseller_profit_eur, ss.sum_reseller_profit_usd = s.sum_reseller_profit_usd,
        ss.sum_profit_rub = s.sum_profit_rub, ss.sum_profit_eur = s.sum_profit_eur, ss.sum_profit_usd = s.sum_profit_usd")
        ->bindValue(':time', $fromTime)
        ->bindValue(':toTime', $toTime)
        ->execute();

      $fromTime = $toTime;
      if ($fromTime > time()) break;
    }

    // Обновляем данные для подписок, которые создались автоматически после постбека о ребилле
    Yii::$app->db->createCommand("UPDATE `search_subscriptions` ss
        INNER JOIN `subscriptions` ON ss.hit_id = `subscriptions`.hit_id
        SET ss.time_on=`subscriptions`.time
        WHERE ss.time_on = 0")
      ->execute();

    Yii::$app->db->createCommand("UPDATE `search_subscriptions` ss
        SET ss.is_updated = 1 WHERE ss.last_time >= :time AND ss.is_updated <> 1")
      ->bindValue(':time', $this->params->getFromTime())
      ->execute();
  }
}