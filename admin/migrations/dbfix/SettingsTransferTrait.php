<?php

namespace admin\migrations\dbfix;

use rgk\settings\models\Setting;
use rgk\settings\models\SettingsCategory;
use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\BaseInflector;

trait SettingsTransferTrait
{
    private function insertValues()
    {
        // запоминаем язык
        $language = Yii::$app->language;
        // Костыль для правильного отображения переводов
        $app = Yii::getAlias('@app');
        Yii::setAlias('@app', dirname(dirname(__DIR__)) . '/../../admin');

        if (!file_exists(Yii::getAlias('@uploadPath') . '/settings/')) {
            @mkdir(Yii::getAlias('@uploadPath') . '/settings/');
        }

        $settings = $this->getRepository();
        foreach ($settings as $key => $setting) {

            if (file_exists(Yii::getAlias('@uploadPath') . '/' . self::MODULE_ID . '/' . $key)) {
                $this->recurseCopy(Yii::getAlias('@uploadPath') . '/' . self::MODULE_ID . '/' . $key, Yii::getAlias('@uploadPath') . '/settings/' . $key);
            }

            $categoryKey = $this->getCategoryKey($setting);
            $categoryId = (new Query())->from(SettingsCategory::tableName())->select('id')->andWhere(['key' => $categoryKey])->scalar();

            // Заполняем данные на русском
            Yii::$app->language = 'ru';
            $title_ru = $setting->getName();
            $description_ru = $setting->getHint();

            // Заполняем данные на английском
            Yii::$app->language = 'en';
            $title_en = $setting->getName();
            $description_en = $setting->getHint();

            $this->insert(self::SETTINGS_TABLE, [
                'category_id' => $categoryId,
                'type' => $this->getFieldType($setting),
                'key' => $setting->getKey(),
                'default_value' => $this->getDefaultValue($setting),
                'title_ru' => $title_ru,
                'title_en' => $title_en,
                'description_ru' => $description_ru,
                'description_en' => $description_en,
                'validators' => Json::encode($setting->getValidator()),
                'order' => $setting->getSort(),
            ]);
            $setting_id = Yii::$app->db->getLastInsertID();

            // Общий пермишен для модуля
            $modulePermission = 'EditModuleSettings' . BaseInflector::camelize(self::MODULE_ID);
            $this->insert(self::PERMISSIONS_TABLE, [
                'setting_id' => $setting_id,
                'permission_name' => $modulePermission,
            ]);

            foreach ($setting->getPermissions() as $permission) {
                $this->insert(self::PERMISSIONS_TABLE, [
                    'setting_id' => $setting_id,
                    'permission_name' => $permission,
                ]);
            }
            //Заполняем значения
            $this->setValue($setting, $setting_id);
        }

        Yii::setAlias('@app', $app);
        // Возвращаем язык
        Yii::$app->language = $language;
    }

    /**
     * @param $src
     * @param $dst
     */
    private function recurseCopy($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    $this->recurseCopy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    rename($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    /**
     * @param SettingsAbstract $setting
     * @param $setting_id
     * @return bool
     */
    private function setValue(SettingsAbstract $setting, $setting_id)
    {
        $value = $setting->getValue();
        if ($setting instanceof Options) {
            $option_id = null;
            foreach ($setting->getOptions() as $key => $option) {

                // Заполняем данные на русском
                Yii::$app->language = 'ru';
                $title_ru = $setting->getOption($key);

                // Заполняем данные на английском
                Yii::$app->language = 'en';
                $title_en = $setting->getOption($key);

                $this->insert(self::OPTIONS_TABLE, [
                    'setting_id' => $setting_id,
                    'title_ru' => $title_ru,
                    'title_en' => $title_en,
                    'value' => $key
                ]);
                if ($value == $key) {
                    $option_id = Yii::$app->db->getLastInsertID();
                }
            }
            $this->insert(self::VALUES_TABLE, [
                'setting_id' => $setting_id,
                'option_id' => $option_id,
            ]);
            return true;
        }

        if ($setting instanceof Text) {
            $this->insert(self::VALUES_TABLE, [
                'setting_id' => $setting_id,
                'text_value' => $value,
            ]);
            return true;
        }
        $this->insert(self::VALUES_TABLE, [
            'setting_id' => $setting_id,
            'string_value' => $value,
        ]);
        return true;
    }

    /**
     * Определение типа настройки
     * @param SettingsAbstract $setting
     * @return int
     */
    private function getFieldType(SettingsAbstract $setting)
    {
        if ($setting instanceof StringObject) {
            return Setting::TYPE_STRING;
        }
        if ($setting instanceof Text) {
            return Setting::TYPE_TEXT;
        }
        if ($setting instanceof Lists) {
            return Setting::TYPE_LISTS;
        }
        if ($setting instanceof Options) {
            return Setting::TYPE_OPTIONS;
        }
        if ($setting instanceof Boolean) {
            return Setting::TYPE_BOOLEAN;
        }
        if ($setting instanceof Integer) {
            return Setting::TYPE_INTEGER;
        }
        if ($setting instanceof Float) {
            return Setting::TYPE_FLOAT;
        }
        if ($setting instanceof FileUpload) {
            return Setting::TYPE_FILE;
        }
    }

    /**
     * Получить значение по умолчанию для файла
     * @param SettingsAbstract $setting
     * @return bool|StringObject|void
     */
    private function getDefaultValue(SettingsAbstract $setting)
    {
        if (!$setting instanceof FileUpload) return;
        return $setting->getDefaultFileUrl();
    }

    /**
     * Получить ключ категории настройки
     * @param SettingsAbstract $setting
     * @return StringObject
     */
    private function getCategoryKey(SettingsAbstract $setting)
    {
        $formGroup = $setting->getFormGroup();
        $formGroupName = ArrayHelper::getValue($formGroup, 'name');

        // если есть подкатегория, возвращаем ее
        if ($formGroupName && $formGroupName != SettingsAbstract::NO_FORM_GROUP) {
            return $formGroupName;
        }

        $group = $setting->getGroup();
        $groupName = ArrayHelper::getValue($group, 'name');

        // иначе возвращаем категорию
        if ($groupName && $groupName != SettingsAbstract::OTHER_GROUP) {
            return $groupName;
        }

        return SettingsCategory::CATEGORY_OTHER_KEY;
    }
}
