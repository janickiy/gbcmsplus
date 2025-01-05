<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
 */
class m190131_084840_remove_reseller_checkout extends Migration
{
  use PermissionTrait;

  /**
   * @throws Exception
   */
  public function up()
  {
    $this->revokeRolesPermission('PaymentsResellerCheckoutController', ['admin', 'reseller', 'root']);
    $this->removeChildPermission('PaymentsResellerCheckoutController', 'PaymentsResellerCheckoutViewModal');
    $this->removeChildPermission('PaymentsResellerCheckoutController', 'PaymentsResellerCheckoutIndex');
    $this->removeChildPermission('PaymentsModule', 'PaymentsResellerCheckoutController');

    $this->removePermission('PaymentsResellerCheckoutViewModal');
    $this->removePermission('PaymentsResellerCheckoutIndex');
    $this->removePermission('PaymentsResellerCheckoutController');
  }

  /**
   */
  public function down()
  {
    $this->createPermission('PaymentsResellerCheckoutController', 'Контроллер ResellerCheckout', 'PaymentsModule', ['admin', 'reseller', 'root']);
    $this->createPermission('PaymentsResellerCheckoutIndex', 'Просмотр расчетов реселлера', 'PaymentsResellerCheckoutController');
    $this->createPermission('PaymentsResellerCheckoutViewModal', 'Просмотр детальной информации по выплате', 'PaymentsResellerCheckoutController');
  }
}
