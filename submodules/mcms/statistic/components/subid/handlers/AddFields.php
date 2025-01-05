<?php

namespace mcms\statistic\components\subid\handlers;


use mcms\statistic\components\subid\BaseHandler;
use Yii;

/**
 * Заполнение статы данными, которые не были добавлены в предыдущих запросах
 */
class AddFields extends BaseHandler
{
  /**
   * @throws \yii\base\InvalidConfigException
   * @throws \yii\db\Exception
   */
  public function run()
  {
    foreach ($this->cfg->getUserIds() as $userId) {
      $this->log("$userId|");
      Yii::$app->sdb->createCommand("
        /** @lang MySQL */
        UPDATE statistic_user_$userId st
        LEFT JOIN {$this->getMainSchemaName()}.sources s ON s.id = st.source_id
        LEFT JOIN {$this->getMainSchemaName()}.operators op ON op.id = st.operator_id
        LEFT JOIN {$this->getMainSchemaName()}.landings l ON l.id = st.landing_id
      SET st.stream_id = s.stream_id, st.country_id = op.country_id, st.provider_id = l.provider_id
      WHERE st.date >= :dateFrom AND st.date <= :dateTo AND st.stream_id IS NULL AND st.country_id IS NULL AND st.provider_id IS NULL
      ")
        ->bindValue(':dateFrom', $this->cfg->getDateFrom())
        ->bindValue(':dateTo', $this->cfg->getDateTo())
        ->execute();
    }
  }


}