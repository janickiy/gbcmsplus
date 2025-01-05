<?php

use console\components\Migration;

class m170913_113826_approved_at_rename extends Migration
{
    public function up()
    {
        $this->renameColumn('credits', 'approved_at', 'activated_at');
    }

    public function down()
    {
        $this->renameColumn('credits', 'activated_at', 'approved_at');
    }
}
