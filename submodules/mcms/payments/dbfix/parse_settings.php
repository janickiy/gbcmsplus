<?php

namespace mcms\payments\dbfix;

use rgk\settings\models\Setting;
use rgk\settings\models\SettingsOption;
use Yii;
use console\components\Migration;

class parse_settings extends Migration
{
  const MODULE_ID = 'payments';

  public function up()
  {
    $module = $userModuleId = Yii::$app->getModule('modmanager')
      ->api('moduleById', ['moduleId' => self::MODULE_ID])
      ->getResult();

    $settings = $module->getSettings() ?: [];

    foreach ($settings as $setting) {
      $key = $setting->getKey();
      $newSetting = Setting::findOne(['key' => $key]);
      if (!$newSetting) {
        echo "Настройка с ключем '$key' отсутствует\n";
        continue;
      }
      $value = $setting->getValue();
      if ($newSetting->type == Setting::TYPE_OPTIONS) {
        $option = SettingsOption::findOne(['value' => $value]);
        if (!$option) {
          echo "Опция '$value' настройки с ключем '$key' отсутствует\n";
          continue;
        }
      }

      Yii::$app->settingsManager->offsetSet($key, $value);
    }
  }

}
