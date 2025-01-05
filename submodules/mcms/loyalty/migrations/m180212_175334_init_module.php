<?php

use console\components\Migration;

class m180212_175334_init_module extends Migration
{
  public function up()
  {
    $this->insert('modules', [
      'module_id' => 'loyalty',
      'name' => 'loyalty.main.loyalty',
      'is_disabled' => 0,
      'created_at' => time(),
      'updated_at' => time(),
    ]);
  }

  public function down()
  {
    $this->delete('modules', ['module_id' => 'loyalty']);
  }
}
