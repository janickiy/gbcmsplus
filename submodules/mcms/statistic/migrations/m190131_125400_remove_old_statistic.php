<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
 */
class m190131_125400_remove_old_statistic extends Migration
{
  use PermissionTrait;

  /**
   */
  public function up()
  {
    $this->revokeRolesPermission('StatisticMainIndex', ['admin', 'root', 'reseller', 'manager']);

    $this->assignRolesPermission('StatisticNewColumnTemplatesCreate', ['admin', 'root', 'reseller']);
    $this->assignRolesPermission('StatisticNewColumnTemplatesDelete', ['admin', 'root', 'reseller']);
    $this->assignRolesPermission('StatisticNewColumnTemplatesGetTemplate', ['admin', 'root', 'reseller']);
    $this->assignRolesPermission('StatisticNewColumnTemplatesUpdate', ['admin', 'root', 'reseller']);
    $this->assignRolesPermission('StatisticNewController', ['admin', 'root', 'reseller']);
  }

  /**
   */
  public function down()
  {
    $this->assignRolesPermission('StatisticMainIndex', ['admin', 'root', 'reseller', 'manager']);
    $this->revokeRolesPermission('StatisticNewColumnTemplatesCreate', ['admin', 'root', 'reseller']);
    $this->revokeRolesPermission('StatisticNewColumnTemplatesDelete', ['admin', 'root', 'reseller']);
    $this->revokeRolesPermission('StatisticNewColumnTemplatesGetTemplate', ['admin', 'root', 'reseller']);
    $this->revokeRolesPermission('StatisticNewColumnTemplatesUpdate', ['admin', 'root', 'reseller']);
    $this->revokeRolesPermission('StatisticNewController', ['admin', 'root', 'reseller']);
  }
}
