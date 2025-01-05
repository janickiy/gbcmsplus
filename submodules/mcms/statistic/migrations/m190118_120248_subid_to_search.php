<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m190118_120248_subid_to_search extends Migration
{
  use PermissionTrait;

  const TABLE = 'search_subscriptions';
  /**
  */
  public function up()
  {
    $this->addColumn(self::TABLE, 'subid1_id', 'bigint unsigned default null');
    $this->addColumn(self::TABLE, 'subid2_id', 'bigint unsigned default null');
  }

  /**
  */
  public function down()
  {
    $this->dropColumn(self::TABLE, 'subid1_id');
    $this->dropColumn(self::TABLE, 'subid2_id');
  }
}
