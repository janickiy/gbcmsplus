<?php

use console\components\Migration;

/**
 */
class m180820_123840_remove_mgmp_settings extends Migration
{

  const CATEGORY = 'payments.settings.mgmp';
  const SETTINGS_MGMP_URL = 'settings.mgmp.url';
  const PERMISSION_EDIT_MGMP_SETTINGS = 'PaymentsEditMgmpSettings';
  const SETTINGS_MGMP_RESELLER_ID = 'settings.mgmp.reseller_id';
  const SETTINGS_MGMP_SECRET_KEY = 'settings.mgmp.secret_key';
  const MODULE_ID = 'payments';

  const SETTINGS_TABLE = 'rgk_settings';
  const PERMISSIONS_TABLE = 'rgk_settings_permissions';
  const OPTIONS_TABLE = 'rgk_settings_options';
  const VALUES_TABLE = 'rgk_settings_values';


  /** @var \rgk\settings\components\SettingsBuilder $settingsBuilder */
  private $settingsBuilder;
  /** @var \rgk\settings\components\SettingsManager $settingsManager */
  private $settingsManager;

  public function init()
  {
    parent::init();
    $this->settingsBuilder = Yii::$app->settingsBuilder;
    $this->settingsManager = Yii::$app->settingsManager;
  }

  public function up()
  {
    $this->settingsBuilder->removeSetting(self::SETTINGS_MGMP_URL);
    $this->settingsBuilder->removeSetting(self::SETTINGS_MGMP_RESELLER_ID);
    $this->settingsBuilder->removeSetting(self::SETTINGS_MGMP_SECRET_KEY);
    $this->settingsBuilder->removeCategory(self::CATEGORY);

  }

  public function down()
  {
    $category = $this->settingsManager->getCategoryByKey('app.common.group_payments');
    $this->settingsBuilder->createCategory(['ru' => 'MGMP', 'en'=>'MGMP'], self::CATEGORY, $category->id);
    $this->insertValues();


  }

  private function getRepository()
  {
    return (new admin\migrations\dbfix\Repository())
      ->set(
        (new admin\migrations\dbfix\StringObject())
          ->setName('payments.settings.mgmp_url')
          ->setKey(self::SETTINGS_MGMP_URL)
          ->setValidators([['string']])
          ->setPermissions([self::PERMISSION_EDIT_MGMP_SETTINGS])
          ->setGroup(['name' => 'app.common.group_payments', 'sort' => 99123])
          ->setFormGroup(['name' => 'payments.settings.mgmp', 'sort' => 3])
          ->setSort(14)
      )
      ->set(
        (new admin\migrations\dbfix\StringObject())
          ->setName('payments.settings.mgmp_reseller_id')
          ->setKey(self::SETTINGS_MGMP_RESELLER_ID)
          ->setValidators([['string']])
          ->setPermissions([self::PERMISSION_EDIT_MGMP_SETTINGS])
          ->setGroup(['name' => 'app.common.group_payments', 'sort' => 99123])
          ->setFormGroup(['name' => 'payments.settings.mgmp', 'sort' => 3])
          ->setSort(15)
      )
      ->set(
        (new admin\migrations\dbfix\StringObject())
          ->setName('payments.settings.mgmp_secret_key')
          ->setKey(self::SETTINGS_MGMP_SECRET_KEY)
          ->setValidators([['string']])
          ->setPermissions([self::PERMISSION_EDIT_MGMP_SETTINGS])
          ->setGroup(['name' => 'app.common.group_payments', 'sort' => 99123])
          ->setFormGroup(['name' => 'payments.settings.mgmp', 'sort' => 3])
          ->setSort(16)
      );
  }
}
