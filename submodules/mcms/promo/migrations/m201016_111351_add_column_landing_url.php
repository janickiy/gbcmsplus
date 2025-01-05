<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
 */
class m201016_111351_add_column_landing_url extends Migration
{
  use PermissionTrait;

  public function up()
  {
    $this->addColumn(
      'landings',
      'custom_url',
      $this->string()->after('description')
    );
  }

  public function down()
  {
    $this->dropColumn('landings', 'custom_url');
  }
}
