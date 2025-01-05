<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
 */
class m190704_142543_restore_reseller_profit extends Migration
{
  use PermissionTrait;

  /**
   */
  public function up()
  {
    $this->createPermission('StatisticResellerProfitStatisticsController', '', 'StatisticModule');
    $this->createPermission('StatisticResellerProfitStatisticsIndex', '', 'StatisticResellerProfitStatisticsController', ['admin', 'root', 'reseller']);

    $this->createPermission('StatisticResellerProfitController', 'Контроллер ResellerProfit', 'StatisticModule');
    $this->createPermission('StatisticResellerProfitUnholdPlan', 'План расхолда средств', 'StatisticResellerProfitController', ['admin', 'reseller', 'root']);
    $this->createPermission('StatisticResellerProfitIndex', 'Статистика профита реселлера', 'StatisticResellerProfitController', ['admin', 'reseller', 'root']);

    $this->createPermission('PaymentsResellerInvoicesController', 'Контроллер ResellerInvoices', 'PaymentsModule');
    $this->createPermission('PaymentsResellerInvoicesConvert', 'Конвертация валют', 'PaymentsResellerInvoicesController', ['admin', 'reseller', 'root']);
    $this->createPermission('PaymentsResellerInvoicesConvertModal', 'Модалка конвертации баланса реселлера', 'PaymentsResellerInvoicesController', ['admin', 'reseller', 'root']);
    $this->createPermission('PaymentsResellerInvoicesDownloadFile', 'Просмотр файлов инвойсов реселлера', 'PaymentsResellerInvoicesController', ['admin', 'reseller', 'root']);
    $this->createPermission('PaymentsResellerInvoicesIndex', 'Просмотр инвойсов реселлера', 'PaymentsResellerInvoicesController', ['admin', 'reseller', 'root']);

    $this->createPermission('PaymentsResellerCheckoutController', 'Контроллер ResellerCheckout', 'PaymentsModule', ['admin', 'reseller', 'root']);
    $this->createPermission('PaymentsResellerCheckoutIndex', 'Просмотр расчетов реселлера', 'PaymentsResellerCheckoutController');
    $this->createPermission('PaymentsResellerCheckoutViewModal', 'Просмотр детальной информации по выплате', 'PaymentsResellerCheckoutController');
  }

  /**
   */
  public function down()
  {
    $this->revokeRolesPermission('PaymentsResellerCheckoutController', ['admin', 'reseller', 'root']);
    $this->removeChildPermission('PaymentsResellerCheckoutController', 'PaymentsResellerCheckoutViewModal');
    $this->removeChildPermission('PaymentsResellerCheckoutController', 'PaymentsResellerCheckoutIndex');
    $this->removeChildPermission('PaymentsModule', 'PaymentsResellerCheckoutController');

    $this->removePermission('PaymentsResellerCheckoutViewModal');
    $this->removePermission('PaymentsResellerCheckoutIndex');
    $this->removePermission('PaymentsResellerCheckoutController');

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

    $this->revokeRolesPermission('StatisticResellerProfitUnholdPlan', ['admin', 'reseller', 'root']);
    $this->revokeRolesPermission('StatisticResellerProfitIndex', ['admin', 'reseller', 'root']);
    $this->removeChildPermission('StatisticResellerProfitController', 'StatisticResellerProfitIndex');
    $this->removeChildPermission('StatisticResellerProfitController', 'StatisticResellerProfitUnholdPlan');
    $this->removeChildPermission('StatisticModule', 'StatisticResellerProfitController');

    $this->removePermission('StatisticResellerProfitUnholdPlan');
    $this->removePermission('StatisticResellerProfitIndex');
    $this->removePermission('StatisticResellerProfitController');

    $this->revokeRolesPermission('StatisticResellerProfitStatisticsIndex', ['admin', 'root', 'reseller']);
    $this->removeChildPermission('StatisticResellerProfitStatisticsController', 'StatisticResellerProfitStatisticsIndex');
    $this->removePermission('StatisticResellerProfitStatisticsIndex');
    $this->removeChildPermission('StatisticModule', 'StatisticResellerProfitStatisticsController');
    $this->removePermission('StatisticResellerProfitStatisticsController');
  }
}
