<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m191030_101318_add_delete_landing_permission extends Migration
{
  use PermissionTrait;
  /**
  */
  public function up()
  {
    $this->createPermission('PromoLandingsDelete', 'Удаление лендинга', 'PromoLandingsController', ['root', 'admin', 'reseller', 'manager']);
  }

  /**
  */
  public function down()
  {
    $this->removePermission('PromoLandingsDelete');
  }
}
