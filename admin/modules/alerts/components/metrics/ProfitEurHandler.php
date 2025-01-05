<?php

namespace admin\modules\alerts\components\metrics;

use yii\db\Query;

class ProfitEurHandler extends BaseHandler
{
  use SubscriptionsTrait;

  /**l
   * @inheritdoc
   */
  public function baseQuery(array $where = [])
  {
    $query = (new Query())->select(['sum_profit' => 'SUM(r.profit_eur)'])
      ->andWhere(['r.currency_id' => self::CURRENCY_EUR]);
    $this->joins($query, $where);
    return $query;
  }
}