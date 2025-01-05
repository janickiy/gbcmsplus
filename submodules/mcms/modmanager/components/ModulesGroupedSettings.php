<?php

namespace mcms\modmanager\components;

use mcms\modmanager\models\Module;
use Yii;
use yii\base\Object;
use yii\helpers\ArrayHelper;
use yii\base\DynamicModel;

/**
 * Компонент для получения сгруппированных настроек модулей
 * Class ModulesGroupedSettings
 * @package mcms\modmanager\components
 */
class ModulesGroupedSettings extends Object
{
  /**
   * Возвращает список настроек модулей, динамических моделей модулей, моделей модуей и настроек модулей
   * @return array
   */
  public static function getModulesSettingsAndDynamicModels()
  {
    $models = Module::find()->all();
    //Список настроек модулей
    $modulesSettings = [];
    //Динамические модели модулей
    $moduleDynamicModels = [];
    //Модели модулей
    $moduleModels = [];
    //Настройки по модулям
    $moduleSettingsRepository = [];
    foreach ($models as $model) {
      $model->scenario = Module::SCENARIO_SETTINGS;
      $module = Yii::$app->getModule($model->module_id);
      $moduleSettings = ArrayHelper::getValue($module, 'settings');
      if ($moduleSettings && $model::canEdit($model->module_id)) {
        $moduleSettings->setModuleId($model->module_id);
        //Получаем настройки модуля
        $attributesMap = $model::getFromAttributesMap($moduleSettings->getFormAttributes());
        foreach ($attributesMap as $map) {
          foreach ($map as $key => $setting) {
            $setting['module_id'] = $model->module_id;
            $modulesSettings[$key] = $setting;
          }
        }
        
        //Создаем динамические модели
        $dynamicModel = new DynamicModel($moduleSettings->getValues());
        foreach ($moduleSettings as $key => $setting) {
          $validators = $setting->getValidator();
          if (count($validators) && is_array($validators)) {
            foreach ($validators as $validator) {
              //Устанавливаем валидаторы
              call_user_func_array([$dynamicModel, 'addRule'], array_merge([$key], $validator));
            }
          }
          //Устанавливаем поведения
          $dynamicModel->attachBehaviors($setting->getBehaviors());
        }
        $moduleDynamicModels[$model->module_id] = $dynamicModel;
        $moduleModels[$model->module_id] = $model;
        $moduleSettingsRepository[$model->module_id] = $moduleSettings;
      }
    }
    
    return [
      'modulesSettings' => $modulesSettings,
      'moduleDynamicModels' => $moduleDynamicModels,
      'moduleModels' => $moduleModels,
      'moduleSettingsRepository' => $moduleSettingsRepository,
    ];
  }
  
  /**
   * Возвращает сгруппированные и отсортированные настройки
   * @param $modulesSettings
   * @return array
   */
  public static function getGroupedSettings($modulesSettings)
  {
    $groupedSettings = [];
    //Получаем список групп и сортируем
    $groups = ArrayHelper::getColumn($modulesSettings, 'group', false);
    $groups = ArrayHelper::map($groups, 'sort', 'name');
    ksort($groups);
    foreach ($groups as $group) {
      foreach ($modulesSettings as $attribute => $setting) {
        if ($group === $setting['group']['name']) {
          $groupedSettings[$group][$attribute] = $setting;
        }
      }
    }
    
    $groupedFormSettings = [];
    foreach ($groupedSettings as $groupKey => $group) {
      //Получаем список подгрупп и сортируем
      $formGroups = ArrayHelper::getColumn($group, 'formGroup', false);
      $formGroups = ArrayHelper::map($formGroups, 'sort', 'name');
      ksort($formGroups);
      //Сортируем настройки
      ArrayHelper::multisort($group, ['sort'], [SORT_ASC]);
      foreach ($formGroups as $formGroup) {
        foreach ($group as $attribute => $setting) {
          if ($formGroup === $setting['formGroup']['name']) {
            $groupedFormSettings[$groupKey][$formGroup][$attribute] = $setting;
          }
        }
      }
    }
    
    return $groupedFormSettings;
  }
}