<?php

use console\components\Migration;
use rgk\settings\components\SettingsBuilder;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m190326_140922_label_permissions extends Migration
{
  use PermissionTrait;
  /**
  */
  public function up()
  {
    Yii::$app->settingsBuilder->updateSettingsTitle('enable_label_stat', ['ru' => 'Статистика по subid включена', 'en' => 'Subid statistic is enabled']);

    $this->removePermission('StatisticViewLabel1');
    $this->removePermission('StatisticViewLabel2');
  }

  /**
  */
  public function down()
  {
    Yii::$app->settingsBuilder->updateSettingsTitle('enable_label_stat', ['ru' => 'Статистика по меткам включена', 'en' => 'Label statistic is enabled']);

    $this->createPermission('StatisticViewLabel1', 'Просмотр Метки1', 'StatisticView', ['admin', 'manager', 'partner', 'reseller', 'root']);
    $this->createPermission('StatisticViewLabel2', 'Просмотр Метки2', 'StatisticView', ['admin', 'manager', 'partner', 'reseller', 'root']);
  }
}
