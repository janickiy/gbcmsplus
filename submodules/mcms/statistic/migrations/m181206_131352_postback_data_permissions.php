<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m181206_131352_postback_data_permissions extends Migration
{
  use PermissionTrait;
  /**
  */
  public function up()
  {
    $this->createPermission('PostbackDataController', 'Контроллер логов приемщика постбеков', 'StatisticModule', ['root']);
    $this->createPermission('StatisticPostbackDataIndex', 'Список логов приемщика постбеков', 'PostbackDataController');
    $this->createPermission('StatisticPostbackDataViewModal', 'Просмотр логов приемщика постбеков', 'PostbackDataController');
  }

  /**
  */
  public function down()
  {
    $this->removePermission('StatisticPostbackDataController');
    $this->removePermission('StatisticPostbackDataIndex');
    $this->removePermission('StatisticPostbackDataViewModal');
  }
}
