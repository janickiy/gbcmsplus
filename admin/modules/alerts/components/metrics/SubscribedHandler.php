<?php

namespace admin\modules\alerts\components\metrics;

use yii\db\Query;

class SubscribedHandler extends BaseHandler
{
    /**
     * @inheritdoc
     */
    public function baseQuery(array $where = [])
    {
        return (new Query())
            ->select(
                ['count_ons' => 'COUNT(st.id)']
            )
            ->from(['st' => 'subscriptions'])
            ->andFilterWhere(['st.source_id' => $this->event->sources])
            ->innerJoin('operators o', 'o.id = st.operator_id')
            ->innerJoin('sources s', 's.id = st.source_id')
            ->andWhere($where);
    }
}