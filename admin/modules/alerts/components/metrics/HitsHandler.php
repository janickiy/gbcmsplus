<?php

namespace admin\modules\alerts\components\metrics;

use yii\db\Query;

class HitsHandler extends BaseHandler
{
    /**
     * @inheritdoc
     */
    public function baseQuery(array $where = [])
    {
        return (new Query())
            ->select(
                [
                    'count_hits' => "COUNT(st.id)"
                ]
            )
            ->from(['st' => 'hits'])
            ->andFilterWhere(['st.source_id' => $this->event->sources])
            ->leftJoin('operators o', 'o.id = st.operator_id')
            ->leftJoin('sources s', 's.id = st.source_id')
            ->leftJoin('landings l', 'l.id = st.landing_id')
            ->andWhere($where);
    }
}
