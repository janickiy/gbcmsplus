<?php

use console\components\Migration;
use mcms\payments\Module;
use rgk\settings\components\SettingsBuilder;
use rgk\settings\models\Setting;
use rgk\utils\traits\PermissionTrait;

/**
 */
class m200917_165332_add_processed_by_to_payments extends Migration
{
  use PermissionTrait;

  const PERMISSION = 'canProcessAllPayments';

  /**
   */
  public function up()
  {
    $this->addColumn('user_payments', 'processed_by', 'mediumint(5) unsigned');
    $this->createPermission(self::PERMISSION, 'Обрабатывать все выплаты', 'PaymentsPaymentsController', ['root', 'admin', 'reseller']);
  }

  /**
   */
  public function down()
  {
    $this->removePermission(self::PERMISSION);
    $this->dropColumn('user_payments', 'processed_by');
  }
}
