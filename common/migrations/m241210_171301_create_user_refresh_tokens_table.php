<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user_refresh_tokens}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 */
class m241210_171301_create_user_refresh_tokens_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%user_refresh_token}}', [
            'id' => $this->primaryKey(),
            'user_id' => 'MEDIUMINT(5) UNSIGNED NOT NULL',
            'token' => $this->string(1000)->null(),
            'ip' => $this->string(50),
            'user_agent' => $this->string(1000),
            'created_at' => $this->integer()
        ]);

        // creates index for column `user_id`
        $this->createIndex(
            '{{%idx-user_refresh_token-user_id}}',
            '{{%user_refresh_token}}',
            'user_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-user_refresh_token-user_id}}',
            '{{%user_refresh_token}}',
            'user_id',
            '{{%users}}',
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
            '{{%fk-user_refresh_token-user_id}}',
            '{{%user_refresh_token}}'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            '{{%idx-user_refresh_token-user_id}}',
            '{{%user_refresh_token}}'
        );

        $this->dropTable('{{%user_refresh_token}}');
    }
}
