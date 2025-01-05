<?php

namespace mcms\common\traits;

use mcms\modmanager\models\Module;
use Yii;

/**
 * Class ModuleSettings
 * @package mcms\common\traits
 */
trait ModuleSettings
{
  /**
   * @param $moduleId
   * @param $setting
   * @param $value
   * @return bool
   */
  public function setModuleSetting($moduleId, $setting, $value)
  {
    $module = Yii::$app->getModule($moduleId)->settings;
    $module->offsetSet($setting, $value);
  }
}