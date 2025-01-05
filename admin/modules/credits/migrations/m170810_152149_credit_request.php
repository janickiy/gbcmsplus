<?php

use console\components\Migration;

/**
 * Class m170810_152149_credit_request
 */
class m170810_152149_credit_request extends Migration
{

    use \rgk\utils\traits\PermissionTrait;

    /**
     *
     */
    public function up()
    {
        $this->createPermission('CreditsCreditsCreateModal', 'Запрос кредита', 'CreditsCreditsController', ['root', 'admin', 'reseller']);
    }

    /**
     */
    public function down()
    {
        $this->removePermission('CreditsCreditsCreateModal');
    }
}
