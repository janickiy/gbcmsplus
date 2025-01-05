<?php
namespace mcms\statistic\components\cron\handlers;

use mcms\statistic\components\cron\AbstractTableHandler;
use Yii;
use yii\db\Query;

/**
 * Заполнение таблицы статистики дохода реселера
 *
 * RES REV = subscription_rebills.reseller_profit_rub
 * RES IK = onetime_subscriptions.reseller_profit_rub
 * PART REV = subscription_rebills.profit_rub
 * PART IK = onetime_subscriptions.profit_rub
 * INV TURN = sold_subscriptions.profit_rub
 * INV RASHOD = sold_subscriptions.real_price_rub
 */
class ResellerProfitStatistics extends AbstractTableHandler
{
  private $ignoredIds;

  const CURRENCY_RUB = 1;
  const CURRENCY_USD = 2;
  const CURRENCY_EUR = 3;

  public function run()
  {
    //Если в таблице нет данных, то не учитываем параметр fromDate
    $tableIsNotEmpty = (new Query())->from('reseller_profit_statistics')->exists();


    // Обновим существующие RES REV и PART REV для тех строк, у которых они изменились
    // (например если партнер продал подписку)
    Yii::$app->db->createCommand('UPDATE reseller_profit_statistics AS rs
        LEFT JOIN subscription_rebills sr ON
          rs.date = sr.date AND
          rs.landing_pay_type_id = sr.landing_pay_type_id AND
          rs.operator_id = sr.operator_id AND
          rs.source_id = sr.source_id AND
          rs.landing_id = sr.landing_id AND
          rs.platform_id = sr.platform_id
        SET 
          rs.rebills_reseller_profit = 0,
          rs.rebills_profit = 0
        WHERE rs.date >= :date AND sr.id is NULL')
      ->bindValue(':date', $tableIsNotEmpty ? $this->params->fromDate : '2000-01-01')
      ->execute();


    // Получаем RES REV и PART REV
    Yii::$app->db->createCommand('INSERT INTO `reseller_profit_statistics`
      (`currency_id`, `date`, `landing_pay_type_id`, `provider_id`, `country_id`, `operator_id`, `user_id`, `stream_id`, `source_id`, `landing_id`,`platform_id`, `rebills_reseller_profit`, `rebills_profit`)
      SELECT sr.`currency_id`, sr.`date`, sr.`landing_pay_type_id`, sr.`provider_id`, ss.`country_id`, sr.`operator_id`, ss.`user_id`, ss.`stream_id`, sr.`source_id`, sr.`landing_id`, sr.`platform_id`,
        CASE
          WHEN sr.`currency_id` = :currency_rub then SUM(sr.`reseller_profit_rub`)
          WHEN sr.`currency_id` = :currency_usd then SUM(sr.`reseller_profit_usd`)
          WHEN sr.`currency_id` = :currency_eur then SUM(sr.`reseller_profit_eur`)
        END as rebills_reseller_profit,
        CASE
          WHEN sr.`currency_id` = :currency_rub then SUM(sr.`profit_rub`)
          WHEN sr.`currency_id` = :currency_usd then SUM(sr.`profit_usd`)
          WHEN sr.`currency_id` = :currency_eur then SUM(sr.`profit_eur`)
        END as rebills_profit
      FROM `subscription_rebills` sr
        LEFT JOIN `search_subscriptions` ss
          ON sr.`hit_id` = ss.`hit_id`
        LEFT JOIN `sold_subscriptions` sold
          ON sr.`hit_id` = sold.`hit_id` 
      WHERE sr.`date` >= :fromDate
        AND sold.id IS NULL
        AND ss.`user_id` NOT IN (' . $this->unavailableUserIdsStr() . ')
      GROUP BY `date`, sr.`source_id`, sr.`landing_id`, sr.`operator_id`, sr.`platform_id`, sr.`landing_pay_type_id`
      ORDER BY NULL
      ON DUPLICATE KEY UPDATE
        rebills_reseller_profit = VALUES(rebills_reseller_profit),
        rebills_profit = VALUES(rebills_profit)')
      ->bindValue(':currency_rub', self::CURRENCY_RUB)
      ->bindValue(':currency_usd', self::CURRENCY_USD)
      ->bindValue(':currency_eur', self::CURRENCY_EUR)
      ->bindValue(':fromDate', $tableIsNotEmpty ? $this->params->fromDate : '2000-01-01')
      ->execute();

    // Получаем RES IK и PART IK
    Yii::$app->db->createCommand('INSERT INTO `reseller_profit_statistics`
      (`currency_id`, `date`, `landing_pay_type_id`, `provider_id`, `country_id`, `operator_id`, `user_id`, `stream_id`, `source_id`, `landing_id`,`platform_id`, `onetime_reseller_profit`, `onetime_profit`)
      SELECT `currency_id`, `date`, `landing_pay_type_id`, `provider_id`, `country_id`, `operator_id`, `user_id`, `stream_id`, `source_id`, `landing_id`,`platform_id`,
        CASE
          WHEN `currency_id` = :currency_rub then SUM(`reseller_profit_rub`)
          WHEN `currency_id` = :currency_usd then SUM(`reseller_profit_usd`)
          WHEN `currency_id` = :currency_eur then SUM(`reseller_profit_eur`)
        END as onetime_reseller_profit,
        CASE
          WHEN `currency_id` = :currency_rub then SUM(`profit_rub`)
          WHEN `currency_id` = :currency_usd then SUM(`profit_usd`)
          WHEN `currency_id` = :currency_eur then SUM(`profit_eur`)
        END as onetime_profit
      FROM `onetime_subscriptions`
      WHERE `date` >= :fromDate
      GROUP BY `date`, `source_id`, `landing_id`, `operator_id`, `platform_id`, `landing_pay_type_id`
      ORDER BY NULL
      ON DUPLICATE KEY UPDATE
        onetime_reseller_profit = VALUES(onetime_reseller_profit),
        onetime_profit = VALUES(onetime_profit)')
      ->bindValue(':currency_rub', self::CURRENCY_RUB)
      ->bindValue(':currency_usd', self::CURRENCY_USD)
      ->bindValue(':currency_eur', self::CURRENCY_EUR)
      ->bindValue(':fromDate', $tableIsNotEmpty ? $this->params->fromDate : '2000-01-01')
      ->execute();

    // Получаем INV TURN
    Yii::$app->db->createCommand('INSERT INTO `reseller_profit_statistics`
      (`currency_id`, `date`, `landing_pay_type_id`, `provider_id`, `country_id`, `operator_id`, `user_id`, `stream_id`, `source_id`, `landing_id`,`platform_id`, `sold_profit`)
      SELECT
        sr.`currency_id`,
        sr.`date`,
        sr.`landing_pay_type_id`,
        sr.`provider_id`,
        ss.country_id as country_id,
        sr.`operator_id`,
        ss.user_id as user_id,
        ss.stream_id as stream_id,
        ss.source_id as source_id,
        sr.`landing_id`,
        sr.`platform_id`,
        CASE
          WHEN sr.`currency_id` = :currency_rub then SUM(sr.`reseller_profit_rub`)
          WHEN sr.`currency_id` = :currency_usd then SUM(sr.`reseller_profit_usd`)
          WHEN sr.`currency_id` = :currency_eur then SUM(sr.`reseller_profit_eur`)
        END as sold_profit
      FROM `subscription_rebills` sr
        LEFT JOIN sold_subscriptions ss
          ON sr.hit_id = ss.hit_id
      WHERE sr.`date` >= :fromDate
        AND ss.hit_id IS NOT NULL
      GROUP BY sr.`date`, `source_id`, sr.`landing_id`, sr.`operator_id`, sr.`platform_id`, sr.`landing_pay_type_id`
      ORDER BY NULL
      ON DUPLICATE KEY UPDATE
      sold_profit = VALUES(sold_profit)')
      ->bindValue(':currency_rub', self::CURRENCY_RUB)
      ->bindValue(':currency_usd', self::CURRENCY_USD)
      ->bindValue(':currency_eur', self::CURRENCY_EUR)
      ->bindValue(':fromDate', $tableIsNotEmpty ? $this->params->fromDate : '2000-01-01')
      ->execute();

    // Получаем INV RASHOD
    Yii::$app->db->createCommand('INSERT INTO `reseller_profit_statistics`
      (`currency_id`, `date`, `landing_pay_type_id`, `provider_id`, `country_id`, `operator_id`, `user_id`, `stream_id`, `source_id`, `landing_id`,`platform_id`, `sold_real_profit`)
      SELECT
        `currency_id`,
        `date`,
        `landing_pay_type_id`,
        `provider_id`,
        country_id,
        `operator_id`,
        user_id,
        stream_id,
        source_id,
        `landing_id`,
        `platform_id`,
        CASE
          WHEN `currency_id` = :currency_rub then SUM(`profit_rub`)
          WHEN `currency_id` = :currency_usd then SUM(`profit_usd`)
          WHEN `currency_id` = :currency_eur then SUM(`profit_eur`)
        END as sold_real_profit
      FROM sold_subscriptions
      WHERE `date` >= :fromDate
      GROUP BY `date`, `source_id`, `landing_id`, `operator_id`, `platform_id`, `landing_pay_type_id`
      ORDER BY NULL
      ON DUPLICATE KEY UPDATE
      sold_real_profit = VALUES(sold_real_profit)')
      ->bindValue(':currency_rub', self::CURRENCY_RUB)
      ->bindValue(':currency_usd', self::CURRENCY_USD)
      ->bindValue(':currency_eur', self::CURRENCY_EUR)
      ->bindValue(':fromDate', $tableIsNotEmpty ? $this->params->fromDate : '2000-01-01')
      ->execute();
  }

  /**
   * Недоступные ресу пользователи
   * @return string
   */
  protected function unavailableUserIdsStr()
  {
    if ($this->ignoredIds) {
      return $this->ignoredIds;
    }
    $reseller = Yii::$app->getModule('users')->api('usersByRoles', ['reseller'])->getResult();
    $resellerId = reset($reseller)['id'];
    $ignoredIds = Yii::$app->getModule('users')
      ->api('notAvailableUserIds', [
        'userId' => $resellerId
      ])
      ->getResult();

    $ignoredIds = array_map(function ($val) {
      return (int)$val;
    }, $ignoredIds);

    return $this->ignoredIds = implode(',', $ignoredIds);
  }
}
