<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
 */
class m220520_091841_fix_notifications_for_roles_default_value extends Migration
{
    use PermissionTrait;

    /**
     */
    public function up()
    {
        $this->db->createCommand("ALTER TABLE `notifications_for_roles` MODIFY `is_send` SMALLINT(6) DEFAULT NULL;")->execute();
    }

    /**
     */
    public function down()
    {
        $this->db->createCommand("ALTER TABLE `notifications_for_roles` ALTER `is_send` DROP DEFAULT;")->execute();
    }
}
