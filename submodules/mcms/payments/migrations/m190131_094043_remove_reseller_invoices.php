<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
 */
class m190131_094043_remove_reseller_invoices extends Migration
{
  use PermissionTrait;

  /**
   */
  public function up()
  {
    $this->revokeRolesPermission('PaymentsResellerInvoicesIndex', ['admin', 'reseller', 'root']);
    $this->revokeRolesPermission('PaymentsResellerInvoicesDownloadFile', ['admin', 'reseller', 'root']);
    $this->revokeRolesPermission('PaymentsResellerInvoicesConvertModal', ['admin', 'reseller', 'root']);
    $this->revokeRolesPermission('PaymentsResellerInvoicesConvert', ['admin', 'reseller', 'root']);

    $this->removeChildPermission('PaymentsResellerInvoicesController', 'PaymentsResellerInvoicesIndex');
    $this->removeChildPermission('PaymentsResellerInvoicesController', 'PaymentsResellerInvoicesDownloadFile');
    $this->removeChildPermission('PaymentsResellerInvoicesController', 'PaymentsResellerInvoicesConvertModal');
    $this->removeChildPermission('PaymentsResellerInvoicesController', 'PaymentsResellerInvoicesConvert');

    $this->removePermission('PaymentsResellerInvoicesIndex');
    $this->removePermission('PaymentsResellerInvoicesDownloadFile');
    $this->removePermission('PaymentsResellerInvoicesConvertModal');
    $this->removePermission('PaymentsResellerInvoicesConvert');

    $this->removeChildPermission('PaymentsModule', 'PaymentsResellerInvoicesController');
    $this->removePermission('PaymentsResellerInvoicesController');
  }

  /**
   */
  public function down()
  {
    $this->createPermission('PaymentsResellerInvoicesController', 'Контроллер ResellerInvoices', 'PaymentsModule');
    $this->createPermission('PaymentsResellerInvoicesConvert', 'Конвертация валют', 'PaymentsResellerInvoicesController', ['admin', 'reseller', 'root']);
    $this->createPermission('PaymentsResellerInvoicesConvertModal', 'Модалка конвертации баланса реселлера', 'PaymentsResellerInvoicesController', ['admin', 'reseller', 'root']);
    $this->createPermission('PaymentsResellerInvoicesDownloadFile', 'Просмотр файлов инвойсов реселлера', 'PaymentsResellerInvoicesController', ['admin', 'reseller', 'root']);
    $this->createPermission('PaymentsResellerInvoicesIndex', 'Просмотр инвойсов реселлера', 'PaymentsResellerInvoicesController', ['admin', 'reseller', 'root']);
  }
}
