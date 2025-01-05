<?php

namespace common\components\traffic\metrics;

use Yii;


/**
 * Class Factory
 * @package common\components\traffic\metrics
 */
class Factory
{
    const TYPE_HITS = 1;
    const TYPE_TB = 2;

    /**
     * @var array доступные метрики анализа трафика
     */
    public static $enabledMetricTypes = [
        self::TYPE_HITS,
        self::TYPE_TB,
    ];

    /**
     * @param $type
     * @return BaseMetric
     */
    public static function factory($type)
    {
        switch ($type) {
            case self::TYPE_HITS:
                return Yii::createObject(HitsMetric::class);
                break;
            case self::TYPE_TB:
                return Yii::createObject(TbMetric::class);
                break;
        }

        return null;
    }
}