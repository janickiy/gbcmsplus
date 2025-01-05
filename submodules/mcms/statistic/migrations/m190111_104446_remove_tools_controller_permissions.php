<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m190111_104446_remove_tools_controller_permissions extends Migration
{
  use PermissionTrait;
  /**
  */
  public function up()
  {
    $this->revokeRolesPermission('StatisticToolsBeeline', ['admin', 'root']);

    $this->removePermission('StatisticToolsBeeline');
    $this->removePermission('StatisticToolsController');
  }

  /**
  */
  public function down()
  {
    $this->createPermission(
      'StatisticToolsController',
      'Контроллер Tools',
      'StatisticModule'
    );

    $this->createPermission(
      'StatisticToolsBeeline',
      'Обработчик файла от Билайна',
      'StatisticToolsController',
      ['admin', 'root']
    );
  }
}
