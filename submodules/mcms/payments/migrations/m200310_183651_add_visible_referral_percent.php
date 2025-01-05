<?php

use console\components\Migration;
use mcms\payments\Module;
use rgk\settings\components\SettingsBuilder;
use rgk\settings\models\Setting;
use rgk\utils\traits\PermissionTrait;

/**
 */
class m200310_183651_add_visible_referral_percent extends Migration
{
  const SETTING_NAME = 'settings.visible_referral_percent_profit';
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

  /**
   */
  public function up()
  {
    $this->settingsBuilder->createSetting(
      ['ru' => 'Отображаемый процент дохода по рефералам', 'en' => 'Visible referral percent'],
      [],
      self::SETTING_NAME,
      ['EditModuleSettingsPayments'],
      Setting::TYPE_INTEGER,
      'app.common.form_group_referals',
      3,
      [["integer"]]
    );

    $this->addColumn(
      self::TABLE_NAME,
      'visible_referral_percent',
      'TINYINT(1) UNSIGNED AFTER referral_percent'
    );

    $this->db->createCommand("
      UPDATE `user_payment_settings` SET `visible_referral_percent` = `referral_percent`
    ")->execute();

    $this->alterColumn(self::TABLE_NAME, 'visible_referral_percent', 'TINYINT(1) UNSIGNED NOT NULL');
  }

  /**
   */
  public function down()
  {
    $this->dropColumn(self::TABLE_NAME, 'visible_referral_percent');
    $this->settingsBuilder->removeSetting(self::SETTING_NAME);
  }
}
