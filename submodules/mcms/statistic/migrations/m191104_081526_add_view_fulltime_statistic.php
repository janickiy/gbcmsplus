<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m191104_081526_add_view_fulltime_statistic extends Migration
{
  use PermissionTrait;
  /**
  */
  public function up()
  {
    $this->createPermission(
      'StatisticViewFullTimeStatistic',
      'Просматривать статистику за всё время',
      'StatisticModule',
      ['root', 'admin', 'reseller', 'manager']
    );
    $this->createPermission(
      'StatisticNewExport',
      'Экспортировать статистику',
      'StatisticNewController',
      ['root', 'admin', 'reseller', 'manager']
    );
    $this->createPermission(
      'StatisticDetailExport',
      'Экспортировать статистику',
      'StatisticDetailController',
      ['root', 'admin', 'reseller', 'manager']
    );
  }

  /**
  */
  public function down()
  {
    $this->revokeRolesPermission('StatisticDetailExport', ['root', 'admin', 'reseller', 'manager']);
    $this->revokeRolesPermission('StatisticNewExport', ['root', 'admin', 'reseller', 'manager']);
    $this->revokeRolesPermission('StatisticViewFullTimeStatistic', ['root', 'admin', 'reseller', 'manager']);
    $this->removePermission('StatisticDetailExport');
    $this->removePermission('StatisticNewExport');
    $this->removePermission('StatisticViewFullTimeStatistic');
  }
}
