<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
 */
class m200429_175324_add_column_landing_comment extends Migration
{
  use PermissionTrait;

  public function up()
  {
    $this->addColumn(
      'landings',
      'comment',
      $this->text()->after('description')
    );
  }

  public function down()
  {
    $this->dropColumn('landings', 'comment');
  }
}
