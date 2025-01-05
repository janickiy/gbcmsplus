<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m190207_081219_cid_value extends Migration
{
  use PermissionTrait;
  /**
  */
  const TABLE = 'sources';
  /**
   */
  public function up()
  {
    $this->addColumn(self::TABLE, 'cid_value', 'varchar(255) after cid');
  }

  /**
   */
  public function down()
  {
    $this->dropColumn(self::TABLE, 'cid_value');
  }
}
