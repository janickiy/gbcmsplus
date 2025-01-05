<?php

use console\components\Migration;
use rgk\settings\components\SettingsBuilder;
use rgk\settings\models\Setting;
use rgk\utils\traits\PermissionTrait;

/**
 * Class m181128_152159_add_wallets_manage_config
 */
class m181128_152159_add_wallets_manage_config extends Migration
{
  const SETTING_NAME = 'settings.wallets_manage_disabled_globally';
  const TABLE_NAME = 'user_payment_settings';

  /**
   * @var SettingsBuilder
   */
  private $settingsBuilder;

  public function init()
  {
    parent::init();

    $this->settingsBuilder = Yii::$app->settingsBuilder;
  }

  public function up()
  {
    $this->settingsBuilder->createSetting(
      ['ru' => 'Глобально отключить управление кошельками в ПП', 'en' => 'Globally disable wallets management in PP'],
      [],
      self::SETTING_NAME,
      ['EditModuleSettingsPayments'],
      Setting::TYPE_BOOLEAN,
      'app.common.group_payments',
      0,
      [["integer"]]
    );

    $this->addColumn(
      self::TABLE_NAME,
      'is_wallets_manage_disabled',
      'TINYINT(1) UNSIGNED NOT NULL DEFAULT 0'
    );
  }

  public function down()
  {
    $this->dropColumn(self::TABLE_NAME, 'is_wallets_manage_disabled');
    $this->settingsBuilder->removeSetting(self::SETTING_NAME);
  }

}
