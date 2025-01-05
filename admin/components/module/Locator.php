<?php

namespace admin\components\module;

use yii\helpers\ArrayHelper;

class Locator implements LocatorInterface
{
    const MODMANAGER = 'modmanager';

    private $_locatedModules = [];

    public function __construct()
    {
        $configFiles = array_merge(
            glob(\Yii::getAlias('@mcms') . '/*/*/config/main.php'),
            glob(\Yii::getAlias('@mcms') . '/*/config/main.php')
        );
        foreach ($configFiles as $configFile) {
            $config = require($configFile);
            $this->_locatedModules[ArrayHelper::getValue($config, 'id')] = $config;
        }
    }

    /**
     * @inheritdoc
     */
    public function getLocatedModules($includeModuleManager = true)
    {
        $modules = $this->_locatedModules;
        if ($includeModuleManager === false) {
            unset($modules[self::MODMANAGER]);
        }
        return $modules;
    }

    /**
     * @inheritdoc
     */
    public function locateByModuleId($moduleId)
    {
        return ArrayHelper::getValue($this->_locatedModules, $moduleId, []);
    }
}
