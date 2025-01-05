<?php

namespace common\components\traffic\metrics;

use yii\db\Query;


/**
 * TRICKY если меньше 180 хитов в час, данные будут некорректные
 *
 * Class HitsMetric
 * @package common\components\traffic\metrics
 */
class HitsMetric extends BaseMetric
{
    /**
     * @var bool|int
     */
    protected $lastMinuteHitsAmount = false;

    /**
     * @var bool|int
     */
    protected $lastHourHitsAmount = false;

    /**
     * @return string[]
     */
    protected function findDeviations()
    {
        $currentAmount = $this->getLastMinuteHitsAmount();

        if ($currentAmount < 10) {
            return $this->findDeviationsForLowTraffic();
        }

        $minuteAverageAmount = (int)($this->getLastHourHitsAmount() / 60);

        if ($currentAmount < $minuteAverageAmount / 2) {
            return ["Трафик снизился более чем в 2 раза. За последнюю минуту ожидалось $minuteAverageAmount, пришло $currentAmount"];
        }

        return [];
    }

    /**
     * @return string[]
     */
    protected function findDeviationsForLowTraffic()
    {
        $minuteAverageAmount = (int)($this->getLastHourHitsAmount() / 60);
        if ($minuteAverageAmount < 3) {
            // отсутствие трафа - это норма
            return [];
        }

        $lastMinuteAmount = $this->getLastMinuteHitsAmount();

        if ($lastMinuteAmount < $minuteAverageAmount / 2) {
            // если меньше, час половина среднеминутного трафа, возвращаем отклонение
            return ["Трафик снизился более чем в 2 раза. За последнюю минуту ожидалось $minuteAverageAmount, пришло $lastMinuteAmount"];
        }

        if ($lastMinuteAmount < 3) {
            return $this->checkEmptyTraffic();
        }

        return [];
    }

    /**
     * @return string[]
     */
    protected function checkEmptyTraffic()
    {
        $last3MinuteAmount = $this->getLast3MinuteHitsAmount();

        $minuteAverageAmount = (int)($this->getLastHourHitsAmount() / 60);
        if ($last3MinuteAmount < $minuteAverageAmount) {
            $lastMinuteAmount = $this->getLastMinuteHitsAmount();

            // если за 3 минуты пришло меньше, чем ожидалось за минуту (а это больше 10)
            return ["Трафик значительно снизился. Ожидалось $minuteAverageAmount хитов в минуту, пришло $lastMinuteAmount"];
        }

        return [];
    }

    /**
     * @return int
     */
    protected function getLastMinuteHitsAmount()
    {
        if ($this->lastMinuteHitsAmount === false) {
            $this->lastMinuteHitsAmount = (new Query())
                ->select('COUNT(1)')
                ->from('hits')
                ->andWhere(['>', 'time', time() - 60])
                ->andWhere([
                    'date' => date('Y-m-d'),
                ])
                ->scalar();
        }

        return $this->lastMinuteHitsAmount;
    }

    /**
     * @return int
     */
    protected function getLast3MinuteHitsAmount()
    {
        return (new Query())
            ->select('COUNT(1)')
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