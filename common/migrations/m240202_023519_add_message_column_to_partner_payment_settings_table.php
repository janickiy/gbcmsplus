<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%partner_payment_settings}}`.
 */
class m240202_023519_add_message_column_to_partner_payment_settings_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%partner_payment_settings}}', 'message', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%partner_payment_settings}}', 'message');
    }
}
