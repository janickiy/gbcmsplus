<?php

namespace common\components;

use Yii;
use yii\base\BaseObject;

class MaintenanceComponent extends BaseObject
{
    /**
     * Ключ для зранения значения в кеше
     * @var string
     */
    public $cacheKey = 'app.maintenance';

    /**
     * Время жизни кеша
     * @var int
     */
    public $lifetime = 600;

    /**
     * @return bool
     */
    public function isMaintenance()
    {
        return Yii::$app->cache->get($this->cacheKey);
    }

    public function setMaintenance()
    {
        $this->setMode(true);
    }

    public function setNotMaintenance()
    {
        $this->setMode(false);
    }

    /**
     * @param bool $isMaintenance
     */
    private function setMode($isMaintenance)
    {
        if ($isMaintenance === false) {
            Yii::$app->cache->delete($this->cacheKey);

            return;
        }

        Yii::$app->cache->set($this->cacheKey, $isMaintenance, $this->lifetime);
    }
}