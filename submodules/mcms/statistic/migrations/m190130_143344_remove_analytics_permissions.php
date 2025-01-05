<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
 */
class m190130_143344_remove_analytics_permissions extends Migration
{
  use PermissionTrait;

  /**
   */
  public function up()
  {
    $this->revokeRolesPermission('StatisticAnalyticsIndex', ['root', 'admin', 'reseller', 'manager']);
    $this->revokeRolesPermission('StatisticAnalyticsFilters', ['root', 'admin', 'reseller', 'manager']);
  }

  /**
   */
  public function down()
  {
    $this->assignRolesPermission('StatisticAnalyticsIndex', ['root', 'admin', 'reseller', 'manager']);
    $this->assignRolesPermission('StatisticAnalyticsFilters', ['root', 'admin', 'reseller', 'manager']);
  }
}
