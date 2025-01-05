<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m201201_101522_add_statistic_by_date_permissions extends Migration
{
  use PermissionTrait;
  /**
  */
  public function up()
  {
    $this->createPermission(
      'StatisticAnalyticsByDate',
      'Просмотр аналитики по дням',
      'StatisticAnalyticsController',
      ['root', 'admin', 'reseller', 'manager']
    );
  }

  /**
  */
  public function down()
  {
    $this->revokeRolesPermission('StatisticAnalyticsByDate', ['root', 'admin', 'reseller', 'manager']);
  }
}
