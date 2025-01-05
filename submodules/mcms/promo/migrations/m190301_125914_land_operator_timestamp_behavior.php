<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m190301_125914_land_operator_timestamp_behavior extends Migration
{
  use PermissionTrait;

  const TABLE = 'landing_operators';
  /**
  */
  public function up()
  {
    $this->addColumn(self::TABLE, 'updated_at', 'INT(10) UNSIGNED AFTER created_at');
  }

  /**
  */
  public function down()
  {
    $this->dropColumn(self::TABLE, 'updated_at');
  }
}
