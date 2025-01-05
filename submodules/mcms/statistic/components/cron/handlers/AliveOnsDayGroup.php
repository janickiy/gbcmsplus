<?php

namespace mcms\statistic\components\cron\handlers;

use mcms\statistic\components\cron\AbstractTableHandler;
use Yii;

/**
 */
class AliveOnsDayGroup extends AbstractTableHandler
{

  public function run()
  {
    $today = Yii::$app->formatter->asDate('today', 'php:Y-m-d');
    for ($date = $this->params->fromDate; $date <= $today; $date = Yii::$app->formatter->asDate($date . ' +1day', 'php:Y-m-d')) {
      Yii::$app->db->createCommand('
        INSERT INTO alive_ons_day_group (date, source_id, landing_id, operator_id, platform_id, landing_pay_type_id, is_fake, currency_id, user_id, stream_id, country_id, provider_id, total_ons_without_offs_revshare, total_ons_without_offs_cpa)
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
          SUM(count_ons_revshare) - SUM(count_offs_revshare),
          SUM(count_ons_cpa) - SUM(count_offs_rejected) - SUM(count_offs_sold)
        FROM statistic
        where date <= :date
        GROUP BY source_id, landing_id, operator_id, platform_id, landing_pay_type_id, is_fake
        ON DUPLICATE KEY UPDATE
          total_ons_without_offs_revshare = VALUES(total_ons_without_offs_revshare),
          total_ons_without_offs_cpa = VALUES(total_ons_without_offs_cpa)
          ;
      ')
        ->bindValue(':date', $date)
        ->execute();
    }
    // Заполнение статы данными, которые не были добавлены в предыдущих запросах
    Yii::$app->db->createCommand(
      'UPDATE alive_ons_day_group st
        LEFT JOIN sources s ON s.id = st.source_id
        LEFT JOIN operators op ON op.id = st.operator_id
      SET st.user_id = s.user_id, st.stream_id = s.stream_id, st.country_id = op.country_id
      WHERE st.date >= :date AND st.user_id = 0 AND st.stream_id = 0 AND st.country_id = 0'
    )
      ->bindValue(':date', $this->params->fromDate)
      ->execute();
  }
}
