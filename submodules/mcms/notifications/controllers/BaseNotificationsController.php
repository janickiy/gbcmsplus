<?php

namespace mcms\notifications\controllers;

use mcms\common\helpers\ArrayHelper;
use mcms\common\controller\AdminBaseController;
use mcms\modmanager\models\Module;
use Yii;

/**
 * Базовый класс для контроллеров уведомлений
 * @package mcms\notifications\controllers
 */
class BaseNotificationsController extends AdminBaseController
{
  public $layout = '@app/views/layouts/main';

  /**
   * @return array
   */
  protected function _getModulesEventList()
  {
    $modules = [];
    $modulesList = [];
    $enabledModules = Yii::$app->getModules();

    if (count($enabledModules)) foreach ($enabledModules as $enabledModule) {
      $moduleId = ArrayHelper::getValue($enabledModule, 'id');
      $moduleConfig = Yii::$app->getModule($moduleId);
      if (empty($moduleConfig->events)) continue;
      $modulesList[] = $moduleId;
    }

    foreach (Module::find()->where(['module_id' => $modulesList])->asArray()->all() as $module) {
      $modules[$module['id']] = Yii::_t($module['name']);
    }
    return $modules;
  }
}