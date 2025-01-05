<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
 */
class m231027_154833_add_mass_status_user extends Migration
{
    use PermissionTrait;

    /**
     */
    public function up()
    {
        $this->createPermission('UsersUsersMassActivate', 'Массовая активация пользователей', 'UsersController', ['root', 'admin', 'manager']);
        $this->createPermission('UsersUsersMassDeactivate', 'Массовая деактивация пользователей', 'UsersController', ['root', 'admin', 'manager']);

    }

    /**
     */
    public function down()
    {
        $this->removePermission('UsersUsersMassActivate');
        $this->removePermission('UsersUsersMassDeactivate');
    }
}
