<?php

namespace admin\modules\alerts\components\metrics;

use yii\db\Query;

class IKHandler extends BaseHandler
{
    /**
     * @inheritdoc
     */
    public function baseQuery(array $where = [])
    {
        return (new Query())
            ->select(
                ['count_onetime' => 'COUNT(st.hit_id)']
            )
            ->from(['st' => 'onetime_subscriptions'])
            ->andFilterWhere(['st.source_id' => $this->event->sources])
            ->andWhere($where);
    }
}