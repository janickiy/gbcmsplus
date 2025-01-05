<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m201204_161053_add_statistic_ltv_permissions extends Migration
{
  use PermissionTrait;
  /**
  */
  public function up()
  {
    $this->createPermission(
      'StatisticAnalyticsLtv',
      'Просмотр аналитики по LTV',
      'StatisticAnalyticsController',
      ['root', 'admin', 'reseller', 'manager']
    );
  }

  /**
  */
  public function down()
  {
    $this->revokeRolesPermission('StatisticAnalyticsLtv', ['root', 'admin', 'reseller', 'manager']);
  }
}
