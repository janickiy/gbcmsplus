<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
 */
class m190131_100025_remove_reseller_profit extends Migration
{
  use PermissionTrait;

  /**
   */
  public function up()
  {
    $this->revokeRolesPermission('StatisticResellerProfitUnholdPlan', ['admin', 'reseller', 'root']);
    $this->revokeRolesPermission('StatisticResellerProfitIndex', ['admin', 'reseller', 'root']);
    $this->removeChildPermission('StatisticResellerProfitController', 'StatisticResellerProfitIndex');
    $this->removeChildPermission('StatisticResellerProfitController', 'StatisticResellerProfitUnholdPlan');
    $this->removeChildPermission('StatisticModule', 'StatisticResellerProfitController');

    $this->removePermission('StatisticResellerProfitUnholdPlan');
    $this->removePermission('StatisticResellerProfitIndex');
    $this->removePermission('StatisticResellerProfitController');
  }

  /**
   */
  public function down()
  {
    $this->createPermission('StatisticResellerProfitController', 'Контроллер ResellerProfit', 'StatisticModule');
    $this->createPermission('StatisticResellerProfitUnholdPlan', 'План расхолда средств', 'StatisticResellerProfitController', ['admin', 'reseller', 'root']);
    $this->createPermission('StatisticResellerProfitIndex', 'Статистика профита реселлера', 'StatisticResellerProfitController', ['admin', 'reseller', 'root']);
  }
}
