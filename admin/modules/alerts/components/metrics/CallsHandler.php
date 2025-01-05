<?php

namespace admin\modules\alerts\components\metrics;

use yii\db\Query;

class CallsHandler extends BaseHandler
{
    /**
     * @inheritdoc
     */
    public function baseQuery(array $where = [])
    {
        return (new Query())
            ->select(
                ['count_calls' => 'SUM(IF(st.type = 2, 1, 0))']
            )
            ->from(['st' => 'complains'])
            ->andWhere($where)
            ->andFilterWhere(['st.source_id' => $this->event->sources])
            ->innerJoin('landing_operators lo', 'lo.landing_id=st.landing_id and lo.operator_id=st.operator_id');
    }
}