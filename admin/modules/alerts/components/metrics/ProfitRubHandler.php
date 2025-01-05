<?php

namespace admin\modules\alerts\components\metrics;

use yii\db\Query;

class ProfitRubHandler extends BaseHandler
{
    use SubscriptionsTrait;

    /**
     * @inheritdoc
     */
    public function baseQuery(array $where = [])
    {
        $query = (new Query())->select(['sum_profit' => 'SUM(r.profit_rub)'])
            ->andWhere(['r.currency_id' => self::CURRENCY_RUB]);
        $this->joins($query, $where);
        return $query;
    }
}