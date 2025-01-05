<?php

use console\components\Migration;
use rgk\settings\models\Setting;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m181211_104731_global_setting_payments_disabled extends Migration
{
  use PermissionTrait;

  const SETTING = 'setting.disable_payments';
  const SETTING_CATEGORY = 'app.common.group_payments';

  /** @var \rgk\settings\components\SettingsBuilder */
  private $settingsBuilder;

  public function init()
  {
    parent::init();
    $this->settingsBuilder = Yii::$app->settingsBuilder;
  }

  /**
   * @throws \yii\base\Exception
   */
  public function up()
  {
    $title = ['en' => 'Disable payments', 'ru' => 'Запретить заказ выплат'];
    $this->settingsBuilder->createSetting($title, [], self::SETTING, ['EditModuleSettingsPayments'], Setting::TYPE_BOOLEAN, self::SETTING_CATEGORY, 0, [["required"],["boolean"]]);
  }

  /**
  */
  public function down()
  {
    $this->settingsBuilder->removeSetting(self::SETTING);
  }
}
