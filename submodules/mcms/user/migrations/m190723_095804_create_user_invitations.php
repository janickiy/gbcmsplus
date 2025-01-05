<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
 */
class m190723_095804_create_user_invitations extends Migration
{
    use PermissionTrait;

    /**
     */
    public function up()
    {
        $tableName = 'users_invitations';

        $this->createTable($tableName, [
            'id' => $this->primaryKey(10)->unsigned(),
            'hash' => $this->string(32)->notNull(),
            'username' => $this->string()->notNull(),
            'password' => $this->string(16)->notNull(),
            'contact' => $this->string(),
            'user_id' => 'MEDIUMINT(5) unsigned',
            'status' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
            'created_at' => $this->integer(10)->notNull()->unsigned(),
            'updated_at' => $this->integer(10)->notNull()->unsigned(),
        ]);

        $this->createIndex(
            'idx_users_invitations_hash',
            $tableName,
            'hash',
            true
        );

        $this->createIndex(
            'ids_users_invitations_username',
            $tableName,
            'username',
            true
        );

        $this->addForeignKey(
            'fk_users_invitations_user',
            $tableName,
            'user_id',
            'users',
            'id',
            'SET NULL'
        );

        $this->createPermission('UsersUsersInvitations', 'Приглашения пользователей', 'UserModule', ['root', 'admin']);
        $this->createPermission('UsersUsersInvitationsIndex', 'Просмотр приглашений пользователей', 'UsersUsersInvitations', ['reseller', 'manager']);
        $this->createPermission('UsersUsersInvitationsCreateModal', 'Создание приглашений пользователей', 'UsersUsersInvitations', ['reseller', 'manager']);
        $this->createPermission('UsersUsersInvitationsUpdateModal', 'Редактирование приглашений пользователей', 'UsersUsersInvitations', ['reseller', 'manager']);
        $this->createPermission('UsersUsersInvitationsDelete', 'Удаление приглашений пользователей', 'UsersUsersInvitations', ['reseller', 'manager']);
        $this->createPermission('UsersUsersInvitationsSelect2', 'Поиск пригланеия для селекта', 'UsersUsersInvitations', ['reseller', 'manager']);
    }

    /**
     */
    public function down()
    {
        $this->removePermission('UsersUsersInvitationsSelect2');
        $this->removePermission('UsersUsersInvitationsDelete');
        $this->removePermission('UsersUsersInvitationsUpdateModal');
        $this->removePermission('UsersUsersInvitationsCreateModal');
        $this->removePermission('UsersUsersInvitationsIndex');
        $this->removePermission('UsersUsersInvitations');

        $this->dropForeignKey('fk_users_invitations_user', 'users_invitations');
        $this->dropTable('users_invitations');
    }
}
