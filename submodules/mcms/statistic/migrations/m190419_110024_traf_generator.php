<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m190419_110024_traf_generator extends Migration
{
  use PermissionTrait;
  /**
  */
  public function up()
  {
    $this->createPermission('StatisticTrafficGeneratorController', 'Контроллер TrafficGenerator', 'StatisticModule');
    $this->createPermission('StatisticTrafficGeneratorIndex', 'Генератор трафика', 'StatisticTrafficGeneratorController', ['root']);
  }

  /**
  */
  public function down()
  {
    $this->removePermission('StatisticTrafficGeneratorController');
    $this->removePermission('StatisticTrafficGeneratorIndex');
  }
}
