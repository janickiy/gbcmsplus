<?php

namespace admin\modules\alerts\components\metrics;

use yii\db\Query;

class RevSubUsdHandler extends BaseHandler
{
    use SubscriptionsTrait;

    /**
     * @inheritdoc
     */
    public function baseQuery(array $where = [])
    {
        $query = (new Query())->select(['sum_profit' => 'SUM(r.profit_usd)/COUNT(DISTINCT st.id)'])
            ->andWhere(['st.currency_id' => self::CURRENCY_USD]);
        $this->joinsTrial($query, $where);
        return $query;
    }
}