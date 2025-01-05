<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
 */
class m191104_081527_add_users_export_permission extends Migration
{
    use PermissionTrait;

    /**
     */
    public function up()
    {
        $this->createPermission(
            'UsersUsersExport',
            'Экспортировать юзеров',
            'UsersController',
            ['root', 'admin', 'reseller', 'manager']
        );
    }

    /**
     */
    public function down()
    {
        $this->revokeRolesPermission('UsersUsersExport', ['root', 'admin', 'reseller', 'manager']);
        $this->removePermission('UsersUsersExport');
    }
}
