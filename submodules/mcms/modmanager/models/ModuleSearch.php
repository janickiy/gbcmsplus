<?php

namespace mcms\modmanager\models;

use mcms\common\helpers\ArrayHelper;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use mcms\modmanager\models\Module;

use yii\data\ArrayDataProvider;

/**
 * ModuleSearch represents the model behind the search form about `mcms\modmanager\models\Module`.
 */
class ModuleSearch extends Module
{

  public $cr_from;
  public $cr_to;
  public $up_from;
  public $up_to;

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['is_disabled'], 'boolean'],
      [['module_id', 'name', 'settings'], 'safe'],
      [['cr_from', 'cr_to', 'up_from', 'up_from'], 'date', 'format' => 'php:Y-m-d'],
    ];
  }

  /**
   * @inheritdoc
   */
  public function scenarios()
  {
    return [
      'search' => ['name', 'module_id', 'is_disabled', 'created_at', 'updated_at', 'cr_from', 'cr_to', 'up_from', 'up_from']
    ];
  }

  /**
   * Creates data provider instance with search query applied
   *
   * @return ActiveDataProvider
   */
  public function search()
  {
    $existedModules = Module::find()->all();
    $existedModuleIds = ArrayHelper::getColumn($existedModules, 'module_id');

    $availableModules = $this->findAvailable();
    foreach($availableModules as $moduleId => $moduleConfig) {
      if ($moduleConfig['is_enabled'] == true) continue;
      if (in_array($moduleId, $existedModuleIds)) continue;
      $moduleConfig['available'] = true;
      $moduleConfig['id'] = '-';
      $moduleConfig['module_id'] = $moduleId;
      $existedModules[$moduleId] = $moduleConfig;
    }

    return new ArrayDataProvider(['allModels' => $existedModules]);
  }
}
