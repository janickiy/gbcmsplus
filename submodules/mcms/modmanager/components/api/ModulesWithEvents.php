<?php

namespace mcms\modmanager\components\api;

use Yii;
use yii\data\ArrayDataProvider;
use mcms\modmanager\models\Module;
use mcms\common\module\api\ApiResult;

class ModulesWithEvents extends ApiResult
{
  private $useDbId;
  private $translateName;

  function init($params = [])
  {
    $this->useDbId = in_array('useDbId', $params);
    $this->translateName = in_array('translateName', $params);
    $this->setResultTypeMap();

    $modules = [];
    foreach (Module::findEnabled() as $enabledModule) {
      $moduleConfig = Yii::$app->getModule($enabledModule->getModuleId());
      if (empty($moduleConfig->events)) continue;

      $moduleName = $enabledModule->name;
      $moduleId = $enabledModule->id;

      $modules[$moduleId] = [
        'id' => $enabledModule->module_id,
        'dbId' => $enabledModule->id,
        'name' => $moduleName,
        'translatedName' => Yii::_t($moduleName)
      ];
    }

    if ($this->useDbId === false) {
      $this
        ->setDataProvider(new ArrayDataProvider(['allModels' => $modules]))
        ->setMapParams(['id', $this->translateName ? 'translatedName' : 'name'])
      ;

      return ;
    }

    $this
      ->setDataProvider(new ArrayDataProvider(['allModels' => $modules]))
      ->setMapParams(['dbId', $this->translateName ? 'translatedName' : 'name'])
    ;
  }
}