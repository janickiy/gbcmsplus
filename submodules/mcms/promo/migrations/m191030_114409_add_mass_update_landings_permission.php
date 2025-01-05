<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m191030_114409_add_mass_update_landings_permission extends Migration
{
  use PermissionTrait;
  /**
  */
  public function up()
  {
    $this->createPermission('PromoLandingsMassUpdate', 'Массовое редактирование лендингов', 'PromoLandingsController', ['root', 'admin', 'reseller', 'manager']);
  }

  /**
   */
  public function down()
  {
    $this->removePermission('PromoLandingsMassUpdate');
  }
}
