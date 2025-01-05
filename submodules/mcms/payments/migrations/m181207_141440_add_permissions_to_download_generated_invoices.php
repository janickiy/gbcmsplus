<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m181207_141440_add_permissions_to_download_generated_invoices extends Migration
{
  use \rgk\utils\traits\PermissionTrait;

  public function up()
  {
    $this->createPermission('PaymentsPaymentsDownloadPositiveInvoice', 'Скачивание позитивного сгенерированного инвойса', 'PaymentsPaymentsController', ['root', 'admin', 'reseller']);
    $this->createPermission('PaymentsPaymentsDownloadNegativeInvoice', 'Скачивание негативного сгенерированного инвойса', 'PaymentsPaymentsController', ['root', 'admin', 'reseller']);

    $this->createPermission('PartnersPaymentsDownloadPositiveInvoice', 'Скачивание позитивного сгенерированного инвойса', 'PartnersPaymentsController', ['partner']);
    $this->createPermission('PartnersPaymentsDownloadNegativeInvoice', 'Скачивание негативного сгенерированного инвойса', 'PartnersPaymentsController', ['partner']);
  }

  public function down()
  {
    $this->removePermission('PartnersPaymentsDownloadNegativeInvoice');
    $this->removePermission('PartnersPaymentsDownloadPositiveInvoice');
    $this->removePermission('PaymentsPaymentsDownloadNegativeInvoice');
    $this->removePermission('PaymentsPaymentsDownloadPositiveInvoice');
  }
}
