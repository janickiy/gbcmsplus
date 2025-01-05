<?php

use console\components\Migration;
use rgk\settings\components\SettingsBuilder;
use rgk\utils\traits\PermissionTrait;
use rgk\settings\models\Setting;

/**
*/
class m181224_084721_dodosalt extends Migration
{
  use PermissionTrait;
  const SETTING_KEY = 'settings.dodopay_salt';
  const CATEGORY_KEY = 'app.common.dodopay_settings';
  const PERMISSION = 'EditModuleSettingsDodoPaySettings';

  /**
   * @var SettingsBuilder
   */
  private $settingsBuilder;

  public function init()
  {
    parent::init();

    $this->settingsBuilder = Yii::$app->settingsBuilder;
    if (!$this->authManager) $this->authManager = Yii::$app->authManager;
  }

  /**
  */
  public function up()
  {
    $this->createPermission(self::PERMISSION, 'Редактирование настроек DodoPay', 'ModmanagerPermissions', ['root', 'admin', 'reseller']);

    $parentCategoryId = Yii::$app->db->createCommand('SELECT id FROM rgk_settings_category WHERE `key`=\'app.common.group_links\'')->queryScalar();

    $this->settingsBuilder->createCategory(
      ['ru' => 'Настройки провайдера DodoPay', 'en' => 'DodoPay settings'],
      self::CATEGORY_KEY,
      $parentCategoryId
    );

    $this->settingsBuilder->createSetting(
      ['ru' => 'Ключ DodoPay', 'en' => 'DodoPay key'],
      [],
      self::SETTING_KEY,
      [self::PERMISSION],
      Setting::TYPE_STRING,
      self::CATEGORY_KEY
    );
  }

  /**
  */
  public function down()
  {
    $this->settingsBuilder->removeSetting(self::SETTING_KEY);
    $this->settingsBuilder->removeCategory(self::CATEGORY_KEY);
    $this->removePermission(self::PERMISSION);
  }
}
