<?php

use console\components\Migration;
use mcms\payments\Module;
use rgk\settings\components\SettingsBuilder;
use rgk\settings\models\Setting;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m181204_083745_payment_cycle extends Migration
{
  use PermissionTrait;

  const SETTING_NAME = 'settings.default_invoicing_cycle';
  const TABLE_NAME = 'user_payment_settings';
  const WALLET_TABLE_NAME = 'user_wallets';


  /**
   * @var SettingsBuilder
   */
  private $settingsBuilder;

  public function init()
  {
    parent::init();

    $this->settingsBuilder = Yii::$app->settingsBuilder;
  }

  /**
  */
  public function up()
  {
    $this->settingsBuilder->createSetting(
      ['ru' => 'Периодичность выплат по-умолчанию', 'en' => 'Default invoicing cycle'],
      [],
      self::SETTING_NAME,
      ['EditModuleSettingsPayments'],
      Setting::TYPE_OPTIONS,
      'app.common.group_payments',
      Module::SETTING_DEFAULT_INVOICING_CYCLE_OFF,
      [["integer"]]
    );

    $title = ['ru' => 'Выкл', 'en' => 'Off'];
    $this->settingsBuilder->createOption($title,self::SETTING_NAME, Module::SETTING_DEFAULT_INVOICING_CYCLE_OFF);

    $title = ['ru' => 'Ежемесячно', 'en' => 'Monthly'];
    $this->settingsBuilder->createOption($title,self::SETTING_NAME, Module::SETTING_DEFAULT_INVOICING_CYCLE_MONTHLY);

    $title = ['ru' => 'Дважды в неделю', 'en' => 'BiWeekly'];
    $this->settingsBuilder->createOption($title,self::SETTING_NAME, Module::SETTING_DEFAULT_INVOICING_CYCLE_BIWEEKLY);

    $title = ['ru' => 'Раз в неделю', 'en' => 'Weekly'];
    $this->settingsBuilder->createOption($title,self::SETTING_NAME, Module::SETTING_DEFAULT_INVOICING_CYCLE_WEEKLY);

    $this->addColumn(
      self::TABLE_NAME,
      'invoicing_cycle',
      'TINYINT(1) UNSIGNED'
    );

    $this->addColumn(
      self::WALLET_TABLE_NAME,
      'is_autopayments',
      'TINYINT(1) UNSIGNED NOT NULL DEFAULT 0'
    );
  }

  /**
  */
  public function down()
  {
    $this->dropColumn(self::WALLET_TABLE_NAME, 'is_autopayments');
    $this->dropColumn(self::TABLE_NAME, 'invoicing_cycle');
    $this->settingsBuilder->removeSetting(self::SETTING_NAME);
  }
}
