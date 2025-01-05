<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
 */
class m190404_123133_user_contact_delete extends Migration
{
    use PermissionTrait;

    /**
     */
    public function up()
    {
        $this->createPermission('UsersUsersRemoveContact', 'Удаление своего контакта', 'UsersController', ['reseller', 'manager', 'admin', 'root']);
        $this->createPermission('UsersUsersCreateContact', 'Добавление своего контакта', 'UsersController', ['reseller', 'manager', 'admin', 'root']);
    }

    /**
     */
    public function down()
    {
        $this->removePermission('UsersUsersRemoveContact');
        $this->removePermission('UsersUsersCreateContact');
    }
}
