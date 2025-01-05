<?php

namespace admin\components\module;

interface LocatorInterface
{
    /**
     * Получить конфиг модуля по id
     * @param $moduleId
     * @return array
     */
    public function locateByModuleId($moduleId);

    /**
     * Получить массив конфигов всех модулей
     * @param bool $includeModuleManager
     * @return array
     */
    public function getLocatedModules($includeModuleManager = true);
}