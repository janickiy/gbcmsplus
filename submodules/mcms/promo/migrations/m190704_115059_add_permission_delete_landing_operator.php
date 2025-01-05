<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
 */
class m190704_115059_add_permission_delete_landing_operator extends Migration
{
  use PermissionTrait;

  /**
   */
  public function up()
  {
    $this->createPermission(
      'PromoLandingOperatorController',
      'Контроллер LandingOperator',
      'PromoModule');

    $this->createPermission(
      'PromoLandingOperatorDelete',
      'Удаление операторов лендингов',
      'PromoLandingOperatorController',
      ['root', 'admin', 'reseller', 'manager']
    );
  }

  /**
   */
  public function down()
  {
    $this->removePermission('PromoLandingOperatorDelete');
    $this->removePermission('PromoLandingOperatorController');
  }
}
