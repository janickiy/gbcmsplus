<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
 */
class m231219_185914_add_partner_auto_payment_permissions extends Migration
{
    use PermissionTrait;

    /**
     */
    public function up()
    {
        $this->createPermission('PartnersPaymentsAutoPaymentForm', 'Сохранение формы автоматических выплат', 'PartnersModule', ['partner']);
    }

    /**
     */
    public function down()
    {
        $this->revokeRolesPermission('PartnersPaymentsAutoPaymentForm', ['partner']);
        $this->removeChildPermission('PartnersModule', 'PartnersPaymentsAutoPaymentForm');
        $this->removePermission('PartnersPaymentsAutoPaymentForm');
    }
}
