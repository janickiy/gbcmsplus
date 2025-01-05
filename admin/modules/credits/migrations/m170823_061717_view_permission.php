<?php

use console\components\Migration;

class m170823_061717_view_permission extends Migration
{
    use \rgk\utils\traits\PermissionTrait;

    public function up()
    {
        $this->createPermission('CreditsCreditsView', 'Просмотр кредита', 'CreditsCreditsController', ['root', 'admin', 'reseller']);
    }

    public function down()
    {
        $this->removePermission('CreditsCreditsView');
    }
}
