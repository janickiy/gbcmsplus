<?php

use console\components\Migration;
use rgk\settings\models\Setting;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m190514_125725_all_rebills_hide extends Migration
{
  use PermissionTrait;

  const SETTING = 'settings.notify_partners_about_rebills';
  const PERMISSION = 'EditNotifyingRebills';
  /**
  */
  public function up()
  {
    $this->createIndex('subscr_rebills_corrected_trans_id_provider_id_uk', 'subscription_rebills_corrected', ['trans_id', 'provider_id'], true);

    $this->createPermission(self::PERMISSION, 'Изменение настройки "Информировать партнеров о ребилах"', 'StatisticPermissions', ['root']);

    $title = ['ru' => 'Информировать партнеров о ребилах', 'en' => 'Notify partners about rebills'];
    $description = [];
    $permissions = [self::PERMISSION];
    $category = 'app.common.group_buyouts_rebills';

    Yii::$app->settingsBuilder->createSetting(
      $title,
      $description,
      self::SETTING,
      $permissions,
      Setting::TYPE_BOOLEAN,
      $category,
      true
    );
  }

  /**
  */
  public function down()
  {
    Yii::$app->settingsBuilder->removeSetting(self::SETTING);
    $this->removePermission(self::PERMISSION);
  }
}
