<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m191107_081526_add_subid_permissions extends Migration
{
  use PermissionTrait;
  /**
  */
  public function up()
  {
    $this->createPermission(
      'StatisticViewSubid',
      'Просматривать subid в статистике',
      'StatisticModule',
      ['root', 'admin', 'reseller', 'manager']
    );
    $this->createPermission(
      'StatisticViewCid',
      'Просматривать cid в статистике',
      'StatisticModule',
      ['root', 'admin', 'reseller', 'manager']
    );
  }

  /**
  */
  public function down()
  {
    $this->revokeRolesPermission('StatisticViewSubid', ['root', 'admin', 'reseller', 'manager']);
    $this->revokeRolesPermission('StatisticViewCid', ['root', 'admin', 'reseller', 'manager']);
    $this->removePermission('StatisticViewSubid');
    $this->removePermission('StatisticViewCid');
  }
}
