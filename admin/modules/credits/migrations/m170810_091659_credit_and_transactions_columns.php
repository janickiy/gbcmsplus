<?php

use console\components\Migration;

class m170810_091659_credit_and_transactions_columns extends Migration
{
    public function up()
    {
        $this->addColumn('credits', 'approved_at', $this->integer(10)->unsigned()->defaultValue(null)->comment('Дата одобрения'));
        $this->addColumn(
            'credit_transactions',
            'fee_date',
            $this->date()->after('type')->comment('Дата за которую списана комиссия')
        );
    }

    public function down()
    {
        $this->dropColumn('credits', 'approved_at');
        $this->dropColumn('credit_transactions', 'fee_date');
    }
}
