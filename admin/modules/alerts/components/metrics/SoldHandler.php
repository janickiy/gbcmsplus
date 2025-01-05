<?php

namespace admin\modules\alerts\components\metrics;

use yii\db\Query;

class SoldHandler extends BaseHandler
{
    /**
     * @inheritdoc
     */
    public function baseQuery(array $where = [])
    {
        return (new Query())
            ->select(
                ['count_sold' => 'COUNT(ss.hit_id)']
            )
            ->from(['ss' => 'sold_subscriptions'])
            ->andFilterWhere(['ss.source_id' => $this->event->sources])
            ->innerJoin('hits st', 'st.id = ss.hit_id')
            ->andWhere($where);
    }
}