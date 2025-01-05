<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m181129_103817_send_all_get_params_to_pb extends Migration
{
  use PermissionTrait;

  const TABLE = 'sources';
  /**
  */
  public function up()
  {
    $this->addColumn(self::TABLE, 'send_all_get_params_to_pb', 'tinyint(1) unsigned NOT NULL DEFAULT "0"');
    $this->addColumn(self::TABLE, 'subid1', 'varchar(255) after label2');
    $this->addColumn(self::TABLE, 'subid2', 'varchar(255) after subid1');
  }

  /**
  */
  public function down()
  {
    $this->dropColumn(self::TABLE, 'send_all_get_params_to_pb');
    $this->dropColumn(self::TABLE, 'subid1');
    $this->dropColumn(self::TABLE, 'subid2');
  }
}
