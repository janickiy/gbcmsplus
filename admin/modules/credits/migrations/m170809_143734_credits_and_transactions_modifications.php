l<?php

use console\components\Migration;

/**
 * Class m170809_143734_credits_and_transactions_modifications
 */
class m170809_143734_credits_and_transactions_modifications extends Migration
{
    /**
     *
     */
    public function up()
    {
        $this->addColumn('credits', 'decline_reason', $this->string(1024)->defaultValue(null)->after('percent'));

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('credit_transactions_invoices', [
            'id' => $this->primaryKey(),
            'transaction_id' => $this->integer(10)->unsigned()->notNull(),
            'invoice_id' => $this->integer(10)->unsigned()->notNull(),
        ], $tableOptions);

        $this->createIndex('credit_transactions_invoices_invoice_transaction_uq', 'credit_transactions_invoices', ['transaction_id', 'invoice_id'], true);
    }

    /**
     *
     */
    public function down()
    {
        $this->dropColumn('credits', 'decline_reason');
        $this->dropTable('credit_transactions_invoices');
    }
}