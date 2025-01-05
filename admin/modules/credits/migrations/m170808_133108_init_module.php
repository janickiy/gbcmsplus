<?php

use console\components\Migration;

class m170808_133108_init_module extends Migration
{
    public function up()
    {
        $this->insert('modules', [
            'module_id' => 'credits',
            'name' => 'credits.main.credits',
            'is_disabled' => 0,
            'created_at' => time(),
            'updated_at' => time(),
        ]);
    }

    public function down()
    {
        $this->delete('modules', ['module_id' => 'credits']);
    }
}
