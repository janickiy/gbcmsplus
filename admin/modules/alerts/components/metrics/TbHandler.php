<?php

namespace admin\modules\alerts\components\metrics;


use Yii;
use yii\db\Query;

class TbHandler extends BaseHandler
{
    /**
     * @inheritdoc
     */
    public function baseQuery(array $where = [])
    {
        return (new Query())
            ->select(['count_tb' => 'COUNT(st.id)'])
            ->from(['st' => 'hits'])
            ->innerJoin('operators o', 'o.id = st.operator_id')
            ->innerJoin('sources s', 's.id = st.source_id')
            ->leftJoin('landings l', 'l.id = st.landing_id')
            ->andFilterWhere(['st.source_id' => $this->event->sources])
            ->andWhere($where)
            ->andWhere(
                $this->isRatioUniquesEnabled()
                    ? 'is_tb > 0 and is_unique = 1)'
                    : 'is_tb > 0'
            );
    }

    /**
     * Включена ли настройка разрешающая расчет именно по уникам.
     * @return bool
     */
    public function isRatioUniquesEnabled()
    {
        return Yii::$app->getModule('statistic')->isRatioByUniquesEnabled();
    }
}