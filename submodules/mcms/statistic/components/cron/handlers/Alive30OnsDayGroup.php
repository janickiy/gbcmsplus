<?php

namespace mcms\statistic\components\cron\handlers;

use mcms\statistic\components\cron\AbstractTableHandler;
use Yii;

/**
 */
class Alive30OnsDayGroup extends AbstractTableHandler
{

  public function run()
  {
    $today = Yii::$app->formatter->asDate('today', 'php:Y-m-d');
    for ($date = $this->params->fromDate; $date <= $today; $date = Yii::$app->formatter->asDate($date . ' +1day', 'php:Y-m-d')) {
      Yii::$app->db->createCommand('
        INSERT INTO alive30_ons_day_group (date, source_id, landing_id, operator_id, platform_id, landing_pay_type_id, is_fake, currency_id, user_id, stream_id, country_id, provider_id, revshare_alive30_ons, to_buyout_alive30_ons)
        SELECT
          :date,
          source_id,
          landing_id,
          operator_id,
          platform_id,
          landing_pay_type_id,
          is_fake,
          currency_id,
          user_id,
          stream_id,
          country_id,
          provider_id,
          COUNT(IF(is_cpa = 1, 1, null)) as cpa,
          COUNT(IF(is_cpa = 0, 1, null)) as rev
        FROM search_subscriptions
        WHERE 
          time_rebill < (UNIX_TIMESTAMP(:date) + 3600 * 24) AND
          time_rebill >= (UNIX_TIMESTAMP(:date) - 3600 * 24 * 30) AND
          time_on < (UNIX_TIMESTAMP(:date) + 3600 * 24) AND
          (time_off = 0 OR (time_off >= UNIX_TIMESTAMP(:date) + 3600 * 24))
        GROUP BY source_id, landing_id, operator_id, platform_id, landing_pay_type_id, is_fake
        ORDER BY NULL
        ON DUPLICATE KEY UPDATE
          revshare_alive30_ons = VALUES(revshare_alive30_ons),
          to_buyout_alive30_ons = VALUES(to_buyout_alive30_ons)
          ;
      ')
        ->bindValue(':date', $date)
        ->execute();
    }
    // Заполнение статы данными, которые не были добавлены в предыдущих запросах
    Yii::$app->db->createCommand(
      'UPDATE alive30_ons_day_group st
        LEFT JOIN sources s ON s.id = st.source_id
        LEFT JOIN operators op ON op.id = st.operator_id
      SET st.user_id = s.user_id, st.stream_id = s.stream_id, st.country_id = op.country_id
      WHERE st.date >= :date AND st.user_id = 0 AND st.stream_id = 0 AND st.country_id = 0'
    )
      ->bindValue(':date', $this->params->fromDate)
      ->execute();
  }
}
