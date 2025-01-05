<?php

namespace console\controllers;

use mcms\common\module\settings\NavDivider;
use mcms\common\module\settings\Repository;
use mcms\common\module\settings\SettingsAbstract;
use Yii;
use yii\console\Controller;
use yii\helpers\Json;

class CompareController extends Controller
{
    public function actionIndex()
    {
        $data = [];
        foreach ($this->getModulesSettings() as $moduleId => $repository) {
            $countSettingsInModule = 0;
            /** @var Repository $repository */
            foreach ($repository as $setting) {
                /** @var SettingsAbstract $setting */
                // tricky: исключаем разделители табов
                if ($setting instanceof NavDivider) continue;
                $key = $setting->getKey();
                if ($moduleId == 'support' && $key == 'settings.notify_sms') continue;
                if ($moduleId == 'partners' && $key == 'auto_submit') $key = 'partners.auto_submit';
                if ($moduleId == 'users' && $key == 'export_limit') $key = 'users.export_limit';
                if ($key == 'settings.default_banner') continue;


                $countSettingsInModule++;

                $value = $setting->getValue();

                $data[$key] = $value;
            }
            $this->stdout("В модуле '$moduleId' $countSettingsInModule настроек\n");
        }
        $filename = __DIR__ . '/file.txt';
        file_put_contents($filename, Json::encode($data));

        $countSettings = count($data);

        $this->stdout("Всего $countSettings настроек\n");
    }

    public function actionCompare()
    {
        $filename = __DIR__ . '/file.txt';
        $settings = Json::decode(file_get_contents($filename));
        foreach ($settings as $key => $value) {
            $exists = Yii::$app->settingsManager->offsetExists($key);
            if (!$exists) {
                $this->stdout("Значение с ключем '$key' не существует в новых настройках\n");
                continue;
            }
            $newValue = Yii::$app->settingsManager->getValueByKey($key);
            if ($value != $newValue) {
                $this->stdout("Значение с ключем '$key' не совпадает со старым. Старое значение '$value', новое значение '$newValue'\n");
            }
        }
    }

    private function getModulesSettings()
    {
        $modules = [
            'users',
            'notifications',
            'promo',
            'support',
            'partners',
            'payments',
            'pages',
            'logs',
            'statistic',
            'alerts',
            'credits',
        ];
        $settings = [];
        foreach ($modules as $module) {
            $moduleObj = Yii::$app->getModule($module);
            if (!$moduleObj) {
                $this->stdout("Модуль '$module' отсутствует\n");
                continue;
            }
            if (!$moduleObj->settings) {
                $this->stdout("Настройки модуля '$module' отсутствуют\n");
                continue;
            }
            $settings[$module] = $moduleObj->settings;
        }
        return $settings;
    }
}