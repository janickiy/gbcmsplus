<?php

namespace common\components\traffic;

use common\components\traffic\metrics\Factory;
use Yii;
use yii\base\BaseObject;

/**
 * Class Checker
 * @package common\components\traffic
 */
class Checker extends BaseObject
{
    const CACHE_KEY = 'traffic-checker-state';

    /**
     * @var array доступные типы метрик для мониторинга
     */
    public $enabledMetrics = [];

    /**
     * @var array объекты доступных метрик
     */
    protected $metrics = [];

    /**
     * @param int $time время в секундах, на которое включается мониторинг
     */
    public static function enable($time = 1800)
    {
        Yii::$app->cache->set(self::CACHE_KEY, true, $time);
    }

    /**
     *
     */
    public static function disable()
    {
        Yii::$app->cache->delete(self::CACHE_KEY);
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!$this->enabledMetrics) {
            $this->enabledMetrics = Factory::$enabledMetricTypes;
        }
    }

    /**
     * @return bool
     */
    public function run()
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $deviations = [];

        foreach ($this->enabledMetrics as $type) {
            $metric = Factory::factory($type);

            // Если проверка завершилась с ошибкой или обнаружилось отклонение
            if (!$metric->isOk() && $metric->hasDeviations()) {
                $deviations = array_merge($deviations, $metric->getDeviations());
            }
        }

        if (count($deviations) > 0) {
            $this->notify($deviations);

            // Отключаем мониторинг, если обнаружилось отклонение (чтобы не засирать логи)
            static::disable();
        }

        return true;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return Yii::$app->cache->exists(self::CACHE_KEY);
    }

    /**
     * @param string[] $deviations
     */
    protected function notify($deviations)
    {
        foreach ($deviations as $deviation) {
            Yii::error($deviation, __METHOD__);
        }
    }
}