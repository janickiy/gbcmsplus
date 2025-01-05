<?php

use console\components\Migration;

class m170818_053036_credit_uq extends Migration
{
    public function up()
    {
        $this->createIndex('credits_external_id_uq', 'credits', 'external_id', true);
        $this->createIndex('credit_transactions_external_id_uq', 'credit_transactions', 'external_id', true);
    }

    public function down()
    {
        $this->dropIndex('credits_external_id_uq', 'credits');
        $this->dropIndex('credit_transactions_external_id_uq', 'credit_transactions');
    }
}
