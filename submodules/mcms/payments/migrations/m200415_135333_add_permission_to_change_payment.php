<?php

use console\components\Migration;
use mcms\payments\Module;
use rgk\settings\components\SettingsBuilder;
use rgk\settings\models\Setting;
use rgk\utils\traits\PermissionTrait;

/**
 */
class m200415_135333_add_permission_to_change_payment extends Migration
{
  use PermissionTrait;

  const PERMISSION = 'PaymentsUsersCanChangeAmount';

  /**
   */
  public function up()
  {
    $this->createPermission(self::PERMISSION, 'Редактировать суммы выплаты', 'PaymentsPaymentsController', ['root', 'admin', 'reseller']);
  }

  /**
   */
  public function down()
  {
    $this->removePermission(self::PERMISSION);
  }
}
