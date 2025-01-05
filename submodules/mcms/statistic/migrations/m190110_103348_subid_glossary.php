<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m190110_103348_subid_glossary extends Migration
{
  use PermissionTrait;

  const TABLE = 'subid_glossary';
  const UNIQUE_INDEX = 'subid_reference_hash_unique';

  /**
  */
  public function up()
  {
    $this->createTable(self::TABLE, [
      'id' => 'bigint UNSIGNED AUTO_INCREMENT PRIMARY KEY',
      'value' => 'varchar(512)',
      'hash' => 'char(32)',
      'last_touched_at' => 'int UNSIGNED NOT NULL',
    ]);
    $this->createIndex(self::UNIQUE_INDEX, self::TABLE, 'hash', true);
  }

  /**
  */
  public function down()
  {
    $this->dropIndex(self::UNIQUE_INDEX, self::TABLE);
    $this->dropTable(self::TABLE);
  }
}
