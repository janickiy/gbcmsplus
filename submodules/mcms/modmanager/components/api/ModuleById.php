<?php

namespace mcms\modmanager\components\api;

use mcms\common\module\api\ApiResult;
use mcms\modmanager\models\Module;
use yii\helpers\ArrayHelper;

class ModuleById extends ApiResult
{
  private $moduleId;

  function init($params = [])
  {
    $this->moduleId = ArrayHelper::getValue($params, 'moduleId');
  }

  /**
   * Получить модуль по его id
   * @return null|static
   */
  public function getResult()
  {
    return $this->moduleId ? Module::findOne(['module_id' => $this->moduleId]) : null;
  }

  /**
   * Получить все модули
   * @return array|\yii\db\ActiveRecord[]
   */
  public function getAll()
  {
    return Module::find()->all();
  }
}