<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m181114_153008_buyout_conditions_landing extends Migration
{
  const TABLE = 'buyout_conditions';
  const INDEX = 'buyout_conditions_unique';

  use PermissionTrait;
  /**
  */
  public function up()
  {
    $this->dropIndex(self::INDEX, self::TABLE);
    $this->addColumn(self::TABLE, 'landing_id', 'MEDIUMINT(5) UNSIGNED NOT NULL DEFAULT 0 after user_id');
    $this->createIndex(self::INDEX, self::TABLE, ['operator_id', 'user_id', 'landing_id', 'type'], true);
  }

  /**
  */
  public function down()
  {
    $this->dropIndex(self::INDEX, self::TABLE);
    $this->dropColumn(self::TABLE, 'landing_id');
    $this->createIndex(self::INDEX, self::TABLE, ['operator_id', 'user_id', 'type'], true);
  }
}
