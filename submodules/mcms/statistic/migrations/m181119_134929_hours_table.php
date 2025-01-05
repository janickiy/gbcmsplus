<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
 * таблица нужна для удобства делать джойны к ней
*/
class m181119_134929_hours_table extends Migration
{
  use PermissionTrait;
  /**
  */
  public function up()
  {
    $this->createTable('hours', [
      'hour' => 'tinyint(1) unsigned not null'
    ]);
    for ($i = 0; $i <= 23; $i++) {
      $this->insert('hours', ['hour' => $i]);
    }
  }

  /**
  */
  public function down()
  {
    $this->dropTable('hours');
  }
}
