<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m190124_153342_source_cid extends Migration
{
  use PermissionTrait;

  const TABLE = 'sources';
  /**
  */
  public function up()
  {
    $this->addColumn(self::TABLE, 'cid', 'varchar(255) after subid2');
  }

  /**
  */
  public function down()
  {
    $this->dropColumn(self::TABLE, 'cid');
  }
}
