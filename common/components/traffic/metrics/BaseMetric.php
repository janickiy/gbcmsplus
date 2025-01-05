<?php

namespace common\components\traffic\metrics;


use yii\base\BaseObject;

/**
 * Class BaseMetric
 * @package common\components\traffic\metrics
 */
abstract class BaseMetric extends BaseObject
{
    /**
     * @var array
     */
    protected $deviations = [];

    /**
     * @return string[]
     */
    abstract protected function findDeviations();

    /**
     * @return bool
     */
    public function isOk()
    {
        try {
            $this->deviations = $this->findDeviations();
        } catch (\Throwable $e) {
            return false;
        }

        return !$this->deviations;
    }

    /**
     * @return bool
     */
    public function hasDeviations()
    {
        return (bool)$this->deviations;
    }

    /**
     * @return array
     */
    public function getDeviations()
    {
        return $this->deviations;
    }
}