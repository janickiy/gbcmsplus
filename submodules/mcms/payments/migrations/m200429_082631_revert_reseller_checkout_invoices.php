<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m200429_082631_revert_reseller_checkout_invoices extends Migration
{
  use PermissionTrait;
  /**
  */
  public function up()
  {
    $this->createPermission('PaymentsResellerCheckoutCreate', 'Создание выплаты реселлера', 'PaymentsResellerCheckoutController', ['root', 'admin']);
  }

  /**
  */
  public function down()
  {
    $this->revokeRolesPermission('PaymentsResellerCheckoutCreate', ['root', 'admin']);
    $this->removePermission('PaymentsResellerCheckoutCreate');
  }
}
