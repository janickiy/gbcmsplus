<?php

namespace admin\modules\alerts\components\metrics;

use yii\db\Query;

class CPRRubHandler extends BaseHandler
{
    /**
     * @inheritdoc
     */
    public function baseQuery(array $where = [])
    {
        return (new Query())
            ->select(
                ['cpr_eur' => 'ROUND(SUM(price_rub)/COUNT(ss.hit_id), 3)']
            )
            ->from(['ss' => 'sold_subscriptions'])
            ->andFilterWhere(['ss.source_id' => $this->event->sources])
            ->innerJoin('hits st', 'st.id = ss.hit_id')
            ->andWhere(['ss.currency_id' => self::CURRENCY_RUB])
            ->andWhere($where);
    }
}