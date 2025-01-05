<?php

namespace mcms\statistic\commands;

use Yii;
use yii\console\Controller;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

class FakeOffSubscriptionController extends Controller
{
  const AMOUNT_DAYS_FOR_AVERAGE = 3;
  const CRON_PERIOD_MINUTES = 15;

  private $settings;

  public function beforeAction($action)
  {
    $this->settings = Yii::$app->getModule('promo')
      ->api('fakeRevshareSettings')
      ->getResult();
    return parent::beforeAction($action);
  }

  public function actionIndex()
  {
    $offSubscriptionPercent = ArrayHelper::getValue($this->settings, 'off_subscriptions_percent_before_time');
    $subscriptionPeriod = ArrayHelper::getValue($this->settings, 'off_subscriptions_time', 0);
    $subscriptionTimePeriod = time() - (86400 * $subscriptionPeriod);
    $maxRejectionPercent = ArrayHelper::getValue($this->settings, 'off_subscriptions_max_rejection_percent', 100);

    Yii::$app
      ->db
      ->createCommand('
      INSERT INTO subscription_offs (`hit_id`, `trans_id`, `time`, `date`, `hour`, `landing_id`, `source_id`, `operator_id`, `platform_id`, `landing_pay_type_id`, `is_cpa`, `currency_id`, `provider_id`, `is_fake`)

      SELECT
        hit_id,
        (SELECT trans_id FROM subscriptions s WHERE s.hit_id = ss.hit_id) AS trans_id,
        UNIX_TIMESTAMP(NOW()) AS `TIME`,
        DATE_FORMAT(NOW(), "%Y-%m-%d") AS `DATE`,
        DATE_FORMAT(NOW(), "%H") AS `HOUR`,
        ss.landing_id,
        source_id,
        ss.operator_id,
        platform_id,
        landing_pay_type_id,
        is_cpa,
        lo.default_currency_id AS currency_id,
        provider_id,
        is_fake

      FROM `search_subscriptions` ss
      LEFT JOIN landing_operators lo ON lo.operator_id = ss.operator_id AND lo.landing_id = ss.landing_id

      WHERE (time_off = 0) AND (time_on < :time) AND (is_fake = 1) ON DUPLICATE KEY UPDATE hit_id = VALUES(hit_id)', [
        ':time' => $subscriptionTimePeriod,
      ])
      ->execute();

    /*
     * младше даты отписки, отписываем количество в процентах из настроек
     */

    //берем все фейковые подписки, от их числа убираем количество в процентах

    $count = (new Query())
      ->from('search_subscriptions')
      ->where([
        'and',
        'is_fake = 1',
        'time_off = 0',
        'time_on > :period',
      ], [
        ':period' => $subscriptionTimePeriod
      ])
      ->count();

    $offSubscriptionsCount = round(($count / 100) * $offSubscriptionPercent);

    // если процент максимального отклонения не 0, корректируем количество отписок
    if ($maxRejectionPercent > 0) {
      $offSubscriptionsCount = $this->getCorrectedCount($offSubscriptionsCount, $maxRejectionPercent);
    }

    Yii::$app
      ->db
      ->createCommand("
      INSERT INTO subscription_offs (`hit_id`, `trans_id`, `time`, `date`, `hour`, `landing_id`, `source_id`, `operator_id`, `platform_id`, `landing_pay_type_id`, `is_cpa`, `currency_id`, `provider_id`, `is_fake`)

      SELECT
        hit_id,
        (SELECT trans_id FROM subscriptions s WHERE s.hit_id = ss.hit_id) AS trans_id,
        UNIX_TIMESTAMP(NOW()) AS `TIME`,
        DATE_FORMAT(NOW(), \"%Y-%m-%d\") AS `DATE`,
        DATE_FORMAT(NOW(), \"%H\") AS `HOUR`,
        ss.landing_id,
        source_id,
        ss.operator_id,
        platform_id,
        landing_pay_type_id,
        is_cpa,
        lo.default_currency_id AS currency_id,
        provider_id,
        is_fake

      FROM `search_subscriptions` ss
      LEFT JOIN landing_operators lo ON lo.operator_id = ss.operator_id AND lo.landing_id = ss.landing_id

      WHERE (time_off = 0) AND (time_on > :time) AND (is_fake = 1)
      ORDER BY RAND()
      LIMIT :limit ON DUPLICATE KEY UPDATE hit_id = VALUES(hit_id)", [
        ':limit' => (int)$offSubscriptionsCount,
        ':time' => $subscriptionTimePeriod,
      ])
      ->execute();

    $db_log = Yii::getLogger()->getProfiling(['yii\db*']);
    foreach ($db_log as $query) {
      $this->stdout($query['info'] . "\n" . round($query['duration'], 5) . " sec\n", Console::FG_GREY);
    }
  }

  /**
   * Метод корректирует количество текущих отписок,
   * исходя из среднего их числа за аналогичные периоды
   *
   * @param int $count количество текущих отписок
   * @param int $maxRejection процент макс. отклонения
   * @return int
   */
  protected function getCorrectedCount($count, $maxRejection)
  {
    $cronPeriod = self::CRON_PERIOD_MINUTES * 60;
    $avgOffs = $this->getAverageOffs($cronPeriod);

    $maxRejection = round($maxRejection / 100, 2);

    /**
     * если отписок больше чем максимально допустимого
     * макс. допустимое рассчитывается исходя из 10 + 100% = 20, 10 + 200% = 30
     */
    if ($count > ($avgOffs + $avgOffs * $maxRejection)) {
      return round($avgOffs + ($avgOffs * $maxRejection));
    }

    /**
     * если отписок меньше чем максимально допустимого
     * макс. допустимое рассчитывается исходя из 10 - 100% = 5, 10 - 200% = 3.3
     */
    if ($count < ($avgOffs / (1 + $maxRejection))) {
      return round($avgOffs / (1 + $maxRejection));
    }

    return $count;
  }

  /**
   * Метод возвращает среднее количество отписок за аналогичные периоды
   *
   * @param int $cronPeriod
   * @return float
   */
  protected function getAverageOffs($cronPeriod)
  {
    $arrayOfDates = [];
    for ($i = 1; $i <= self::AMOUNT_DAYS_FOR_AVERAGE; $i++) {
      $arrayOfDates[] = date('Y-m-d', time() - 86400 * $i);
    }

    $query = (new Query())
      ->select(['COUNT(id) as count'])
      ->from('subscription_offs')
    ->where([
      'and',
      [
        'is_fake' => 1,
        'hour' => date('G', time()),
        'date' => $arrayOfDates
      ],
    ])
    ->groupBy('date');

    $hourAverage = (float)$query->average('count');

    return $hourAverage / (3600 / $cronPeriod);
  }
}