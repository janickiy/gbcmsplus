<?php

namespace common\components\traffic\metrics;

use yii\db\Query;


/**
 * Class HitsMetric
 * @package common\components\traffic\metrics
 */
class TbMetric extends BaseMetric
{
    /**
     * @var bool|int
     */
    protected $lastMinuteTbRate = false;

    /**
     * @var bool|int
     */
    protected $lastHourTbRate = false;

    /**
     * @var bool|int
     */
    protected $lastHourHitsAmount = false;

    /**
     * @var bool|int
     */
    protected $lastMinuteHitsAmount = false;

    /**
     * @return string[]
     */
    protected function findDeviations()
    {
        if ($this->getLastHourHitsAmount() / 60 < 10) {
            return $this->findDeviationsForLowTraffic();
        }

        $currentRate = $this->getLastMinuteTbRate();
        if (!$currentRate) {
            return [];
        }

        $minuteAverageRate = $this->getLastHourTbRate();

        if ($currentRate > $minuteAverageRate && $currentRate > 0.9) {
            return ["ТБ увеличился. За последнюю минуту ожидалось: $minuteAverageRate, пришло: $currentRate"];
        }

        return [];
    }

    /**
     * @return string[]
     */
    protected function findDeviationsForLowTraffic()
    {
        if ($this->getLastMinuteHitsAmount() < 3) {
            return []; // нет трафа, рассчет тб будет не корректным
        }

        $last3MinuteRate = $this->getLast3MinuteTbRate();

        $minuteAverageRate = $this->getLastHourTbRate();

        if ($last3MinuteRate < $minuteAverageRate && $last3MinuteRate > 0.8) {
            return ["Процент ТБ предельно увеличился. Ожидалось: $minuteAverageRate, фактически: $last3MinuteRate"];
        }

        return [];
    }

    /**
     * @return int
     */
    protected function getLastMinuteTbRate()
    {
        if ($this->lastMinuteTbRate === false) {
            $this->lastMinuteTbRate = (float)(new Query())
                ->select('COUNT(IF(is_tb > 0, id, NULL)) / COUNT(1)')
                ->from('hits')
                ->andWhere(['>', 'time', time() - 60])
                ->andWhere([
                    'date' => date('Y-m-d'),
                ])
                ->scalar();
        }

        return $this->lastMinuteTbRate;
    }

    /**
     * @return int
     */
    protected function getLast3MinuteTbRate()
    {
        return (float)(new Query())
            ->select('COUNT(IF(is_tb > 0, id, NULL)) / COUNT(1)')
            ->from('hits')
            ->andWhere(['>', 'time', time() - 180])
            ->andWhere([
                'date' => date('Y-m-d'),
            ])
            ->scalar();
    }

    /**
     * @return int
     */
    protected function getLastHourTbRate()
    {
        if ($this->lastHourTbRate === false) {
            $this->lastHourTbRate = (float)(new Query())
                ->select('COUNT(IF(is_tb > 0, id, NULL)) / COUNT(1)')
                ->from('hits')
                ->where(['>', 'time', time() - 3600])
                ->andWhere([
                    'date' => date('Y-m-d'),
                ])
                ->scalar();
        }

        return $this->lastHourTbRate;
    }

    /**
     * @return int
     */
    protected function getLastMinuteHitsAmount()
    {
        if ($this->lastMinuteHitsAmount === false) {
            $this->lastMinuteHitsAmount = (int)(new Query())
                ->select('COUNT(1)')
                ->from('hits')
                ->andWhere(['>', 'time', time() - 60])
                ->andWhere([
                    'date' => date('Y-m-d'),
                    'hour' => date('G'),
                ])
                ->scalar();
        }

        return $this->lastMinuteHitsAmount;
    }

    /**
     * @return int
     */
    protected function getLastHourHitsAmount()
    {
        if ($this->lastHourHitsAmount === false) {
            $this->lastHourHitsAmount = (int)(new Query())
                ->select('COUNT(1)')
                ->from('hits')
                ->where(['>', 'time', time() - 3600])
                ->andWhere([
                    'date' => date('Y-m-d'),
                ])
                ->scalar();
        }

        return $this->lastHourHitsAmount;
    }
}