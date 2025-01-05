<?php

use console\components\Migration;

class m170816_063532_payments_permissions extends Migration
{
    use \rgk\utils\traits\PermissionTrait;

    public function up()
    {
        $this->createPermission('CreditsCreditPaymentsController', 'Контроллер выплат по кредитам', 'CreditsModule', ['root', 'admin', 'reseller']);
        $this->createPermission('CreditsCreditPaymentsCreateModal', 'Создание выплаты', 'CreditsCreditPaymentsController', ['root', 'admin', 'reseller']);
        $this->createPermission('CreditsCreditPaymentsUpdateModal', 'Изменение выплаты', 'CreditsCreditPaymentsController', ['root', 'admin', 'reseller']);
    }

    public function down()
    {
        $this->removePermission('CreditsCreditPaymentsController');
        $this->removePermission('CreditsCreditPaymentsCreateModal');
        $this->removePermission('CreditsCreditPaymentsUpdateModal');
    }
}
