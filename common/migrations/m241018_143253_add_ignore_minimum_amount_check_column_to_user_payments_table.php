l<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%user_payments}}`.
 */
class m241018_143253_add_ignore_minimum_amount_check_column_to_user_payments_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%user_payments}}', 'ignore_minimum_amount_check', $this->boolean()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%user_payments}}', 'ignore_minimum_amount_check');
    }
}
