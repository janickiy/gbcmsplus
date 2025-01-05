<?php

namespace admin\modules\alerts\components\metrics;

use Yii;
use yii\db\Query;

trait SubscriptionsTrait
{
    /**
     * Условие по trial операторам
     * @param string $operatorField поле для которого применить условие
     * @param bool $not NOT IN
     * @return string
     */
    public function getTrialOperatorsInCondition($operatorField, $not = false)
    {
        $trialOperators = Yii::$app->getModule('promo')->api('trialOperators')->getResult();
        if (empty($trialOperators)) {
            return '1 = 1';
        }
        return $operatorField . ($not ? ' NOT' : '') . ' IN (' . implode(', ', $trialOperators) . ')';
    }

    /**
     * @param Query $query
     * @param $where
     */
    public function joinsTrial(Query &$query, $where)
    {
        $query->from(['st' => 'subscriptions'])
            ->leftJoin('subscription_rebills as r',
                "r.hit_id = st.hit_id AND
         r.landing_id = st.landing_id AND
         r.operator_id = st.operator_id AND
         r.platform_id = st.platform_id AND
         r.landing_pay_type_id = st.landing_pay_type_id AND
         r.is_cpa = st.is_cpa AND
         r.source_id = st.source_id AND
         (({$this->getTrialOperatorsInCondition('st.operator_id', true)} AND st.date = r.date) OR ({$this->getTrialOperatorsInCondition('st.operator_id')} AND st.date = date_add(r.date, INTERVAL -1 DAY)))"
            )
            ->innerJoin('operators o', 'o.id = st.operator_id')
            ->innerJoin('sources s', 's.id = st.source_id')
            ->andFilterWhere(['st.source_id' => $this->event->sources])
            ->andWhere($where);
    }

    /**
     * @param Query $query
     * @param $where
     */
    public function joins(Query &$query, $where)
    {
        $query->from(['st' => 'subscriptions'])
            ->leftJoin('subscription_rebills as r',
                "r.hit_id = st.hit_id AND
         r.landing_id = st.landing_id AND
         r.operator_id = st.operator_id AND
         r.platform_id = st.platform_id AND
         r.landing_pay_type_id = st.landing_pay_type_id AND
         r.is_cpa = st.is_cpa AND
         r.source_id = st.source_id"
            )
            ->innerJoin('operators o', 'o.id = st.operator_id')
            ->innerJoin('sources s', 's.id = st.source_id')
            ->andFilterWhere(['st.source_id' => $this->event->sources])
            ->andWhere($where);
    }
}