<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m181004_133621_new_statistic extends Migration
{
  use PermissionTrait;
  /**
  */
  public function up()
  {
    $this->createPermission('StatisticNewController', 'Контроллер новой статистики', 'StatisticModule', ['root']);
    $this->createPermission('StatisticNewIndex', 'Статистика', 'StatisticNewController');
  }

  /**
  */
  public function down()
  {
    $this->removePermission('StatisticNewIndex');
    $this->removePermission('StatisticNewController');
  }
}
