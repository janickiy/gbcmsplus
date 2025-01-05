<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
 */
class m220517_130117_fix_users_table extends Migration
{
    use PermissionTrait;

    /**
     */
    public function up()
    {
        $this->db->createCommand("ALTER TABLE `users` MODIFY `access_token` TEXT DEFAULT NULL;")->execute();
        $this->db->createCommand("ALTER TABLE `users` MODIFY `auth_key` VARCHAR(32) DEFAULT NULL;")->execute();
    }

    /**
     */
    public function down()
    {
        $this->db->createCommand("ALTER TABLE `users` ALTER `access_token` DROP DEFAULT;")->execute();
        $this->db->createCommand("ALTER TABLE `users` ALTER `auth_key` DROP DEFAULT;")->execute();

    }
}
