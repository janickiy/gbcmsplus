<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
 */
class m190131_101630_remove_reseller_profit_statistic extends Migration
{
  use PermissionTrait;

  /**
   */
  public function up()
  {
    $this->revokeRolesPermission('StatisticResellerProfitStatisticsIndex', ['admin', 'root', 'reseller']);
    $this->removeChildPermission('StatisticResellerProfitStatisticsController', 'StatisticResellerProfitStatisticsIndex');
    $this->removePermission('StatisticResellerProfitStatisticsIndex');
    $this->removeChildPermission('StatisticModule', 'StatisticResellerProfitStatisticsController');
    $this->removePermission('StatisticResellerProfitStatisticsController');
  }

  /**
   */
  public function down()
  {
    $this->createPermission('StatisticResellerProfitStatisticsController', '', 'StatisticModule');
    $this->createPermission('StatisticResellerProfitStatisticsIndex', '', 'StatisticResellerProfitStatisticsController', ['admin', 'root', 'reseller']);
  }
}
