<?php

namespace admin\modules\alerts\components\metrics;

use yii\db\Query;

class ProfitUsdHandler extends BaseHandler
{
    use SubscriptionsTrait;

    /**
     * @inheritdoc
     */
    public function baseQuery(array $where = [])
    {
        $query = (new Query())->select(['sum_profit' => 'SUM(r.profit_usd)'])
            ->andWhere(['r.currency_id' => self::CURRENCY_USD]);
        $this->joins($query, $where);
        return $query;
    }
}