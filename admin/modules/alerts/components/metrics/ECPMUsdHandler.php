<?php

namespace admin\modules\alerts\components\metrics;

use Yii;
use yii\db\Query;

class ECPMUsdHandler extends BaseHandler
{
    /**
     * @inheritdoc
     */
    public function baseQuery(array $where = [])
    {
        return (new Query())
            ->select(
                [
                    'ecpm' => "((({$this->getSumSold($where)}) + ({$this->getSumOnetime($where)}))/(((COUNT(st.id)) - ({$this->tbCount($where)}))/1000))"
                ]
            )
            ->from(['st' => 'hits'])
            ->andFilterWhere(['st.source_id' => $this->event->sources])
            ->innerJoin('operators o', 'o.id = st.operator_id')
            ->innerJoin('sources s', 's.id = st.source_id')
            ->innerJoin('landings l', 'l.id = st.landing_id')
            ->andWhere(['st.is_cpa' => 1])
            ->leftJoin(
                'landing_operators',
                'landing_operators.landing_id = st.landing_id and landing_operators.operator_id = st.operator_id'
            )->andWhere([
                'or',
                ['st.landing_id' => 0],
                ['default_currency_id' => self::CURRENCY_USD],
                ['IS', 'landing_operators.landing_id', NULL]
            ])
            ->andWhere($where);
    }

    /**
     * Cумма солдов
     * @param array $where
     * @return false|null|string
     */
    private function getSumSold(array $where = [])
    {
        $query = (new Query())
            ->select(
                [
                    'sum' => 'SUM(ss.price_usd)'
                ]
            )
            ->from(['ss' => 'sold_subscriptions'])
            ->andFilterWhere(['ss.source_id' => $this->event->sources])
            ->innerJoin('hits st', 'st.id = ss.hit_id')
            ->andWhere(['ss.currency_id' => self::CURRENCY_USD])
            ->andWhere($where);

        $this->handleFilters($query);
        return $query->scalar() ?: 0;
    }

    /**
     * Сумма вантаймов
     * @param array $where
     * @return string
     */
    private function getSumOnetime(array $where = [])
    {
        $query = (new Query())
            ->select(
                [
                    'sum' => 'SUM(os.profit_usd)'
                ]
            )
            ->from(['os' => 'onetime_subscriptions'])
            ->andFilterWhere(['os.source_id' => $this->event->sources])
            ->innerJoin('hits st', 'st.id = os.hit_id')
            ->andWhere(['os.currency_id' => self::CURRENCY_USD])
            ->andWhere($where);

        $this->handleFilters($query);
        return $query->scalar() ?: 0;
    }

    /**
     * количество ТБ
     * @param array $where
     * @return int
     */
    public function tbCount(array $where = [])
    {
        $query = (new Query())
            ->select(['count_tb' => 'COUNT(st.id)'])
            ->from(['st' => 'hits'])
            ->andFilterWhere(['st.source_id' => $this->event->sources])
            ->innerJoin('operators o', 'o.id = st.operator_id')
            ->innerJoin('sources s', 's.id = st.source_id')
            ->innerJoin('landings l', 'l.id = st.landing_id')
            ->andWhere(['st.is_cpa' => 1])
            ->leftJoin(
                'landing_operators',
                'landing_operators.landing_id = st.landing_id and landing_operators.operator_id = st.operator_id'
            )->andWhere([
                'or',
                ['st.landing_id' => 0],
                ['default_currency_id' => self::CURRENCY_USD],
                ['IS', 'landing_operators.landing_id', NULL]
            ])
            ->andWhere($where)
            ->andWhere(
                $this->isRatioUniquesEnabled()
                    ? 'is_tb > 0 and is_unique = 1)'
                    : 'is_tb > 0'
            );
        $this->handleFilters($query);
        return $query->scalar() ?: 0;
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