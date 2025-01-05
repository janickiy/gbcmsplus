<?php

namespace admin\modules\alerts\components\metrics;

use yii\db\Query;

class RevSubEurHandler extends BaseHandler
{
    use SubscriptionsTrait;

    /**
     * @inheritdoc
     */
    public function baseQuery(array $where = [])
    {
        $query = (new Query())->select(['sum_profit' => 'SUM(r.profit_eur)/COUNT(DISTINCT st.id)'])
            ->andWhere(['st.currency_id' => self::CURRENCY_EUR]);
        $this->joinsTrial($query, $where);
        return $query;
    }
}