<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%partner_payment_settings}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 * - `{{%user_wallets}}`
 */
class m231219_062815_create_partner_payment_settings_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%partner_payment_settings}}', [
            'id' => $this->primaryKey(),
            'user_id' => 'MEDIUMINT(5) UNSIGNED NOT NULL',
            'amount' => $this->decimal(10, 0)->defaultValue(0),
            'totality' => $this->boolean()->defaultValue(0),
            'wallet_id' => $this->integer(),
            'invoicing_cycle' => $this->smallInteger(1),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'last_checked_at' => $this->integer(),
        ], $tableOptions);

        // creates index for column `user_id`
        $this->createIndex(
            '{{%idx-partner_payment_settings-user_id}}',
            '{{%partner_payment_settings}}',
            'user_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-partner_payment_settings-user_id}}',
            '{{%partner_payment_settings}}',
            'user_id',
            '{{%users}}',
            'id',
            'CASCADE'
        );

        // creates index for column `wallet_id`
        $this->createIndex(
            '{{%idx-partner_payment_settings-wallet_id}}',
            '{{%partner_payment_settings}}',
            'wallet_id'
        );

        // add foreign key for table `{{%user_wallets}}`
        $this->addForeignKey(
            '{{%fk-partner_payment_settings-wallet_id}}',
            '{{%partner_payment_settings}}',
            'wallet_id',
            '{{%user_wallets}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-partner_payment_settings-user_id}}',
            '{{%partner_payment_settings}}'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            '{{%idx-partner_payment_settings-user_id}}',
            '{{%partner_payment_settings}}'
        );

        // drops foreign key for table `{{%user_wallets}}`
        $this->dropForeignKey(
            '{{%fk-partner_payment_settings-wallet_id}}',
            '{{%partner_payment_settings}}'
        );

        // drops index for column `wallet_id`
        $this->dropIndex(
            '{{%idx-partner_payment_settings-wallet_id}}',
            '{{%partner_payment_settings}}'
        );

        $this->dropTable('{{%partner_payment_settings}}');
    }
}
