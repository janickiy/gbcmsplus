<?php

use console\components\Migration;

class m170817_092156_external_id extends Migration
{
    public function up()
    {
        $this->addColumn('credits', 'external_id', $this->integer(10)->unsigned()->defaultValue(null)->after('id'));
        $this->addColumn('credit_transactions', 'external_id', $this->integer(10)->unsigned()->defaultValue(null)->after('id'));
    }

    public function down()
    {
        $this->dropColumn('credits', 'external_id');
        $this->dropColumn('credit_transactions', 'external_id');
    }
}
