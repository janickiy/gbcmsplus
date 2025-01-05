<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
 */
class m200526_123243_create_login_attempt extends Migration
{
    use PermissionTrait;

    /**
     */
    public function up()
    {
        $this->createTable('login_attempt', [
            'id' => $this->primaryKey(10)->unsigned(),
            'user_id' => 'MEDIUMINT(5) UNSIGNED',
            'login' => $this->string()->notNull(),
            'password' => $this->string(),
            'ip' => $this->string(),
            'user_agent' => $this->string(512),
            'server_data' => $this->text(),
            'fail_reason' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
            'created_at' => $this->integer(10)->unsigned()->notNull(),
        ]);

        $this->createIndex(
            'idx-login_attempt-login',
            'login_attempt',
            ['login']
        );

        $this->createIndex(
            'idx-login_attempt-created_at',
            'login_attempt',
            ['created_at']
        );

        $this->createPermission(
            'UsersLoginAttempts',
            'Попытки входа',
            'UserModule'
        );

        $this->createPermission(
            'UsersLoginAttemptsIndex',
            'Просмотр попыток входа',
            'UsersLoginAttempts',
            ['root', 'admin',]
        );

        $this->createPermission(
            'UsersLoginAttemptsViewModal',
            'Детальный просмотр попытки',
            'UsersLoginAttempts',
            ['root', 'admin',]
        );

        $this->createPermission(
            'UsersLoginAttemptsShowPassword',
            'Просмотр пароля попытки входа',
            'UsersLoginAttempts',
            ['root', 'admin',]
        );
    }

    /**
     */
    public function down()
    {
        $this->removePermission('UsersLoginAttemptsShowPassword');
        $this->removePermission('UsersLoginAttemptsViewModal');
        $this->removePermission('UsersLoginAttemptsIndex');
        $this->removePermission('UsersLoginAttempts');

        $this->dropTable('login_attempt');
    }
}
