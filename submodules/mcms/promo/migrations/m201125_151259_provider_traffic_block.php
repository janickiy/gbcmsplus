<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m201125_151259_provider_traffic_block extends Migration
{
  use PermissionTrait;
  /**
  */
  public function up()
  {
    $this->addColumn('traffic_blocks', 'provider_id', 'MEDIUMINT(5) UNSIGNED NULL AFTER user_id');
    $this->alterColumn('traffic_blocks', 'operator_id', 'MEDIUMINT(5) UNSIGNED NULL');
  }

  /**
  */
  public function down()
  {
    $this->dropColumn('traffic_blocks', 'provider_id');
    $this->alterColumn('traffic_blocks', 'operator_id', 'MEDIUMINT(5) UNSIGNED NOT NULL');
  }
}
