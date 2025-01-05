<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m201109_130627_create_landing_copy_permission extends Migration
{
  use PermissionTrait;
  /**
  */
  public function up()
  {
    $this->createPermission('PromoLandingsCopyLanding', 'Копирование лендинга', 'PromoLandingsController', ['root', 'admin']);
  }

  /**
  */
  public function down()
  {
    $this->removePermission('PromoLandingsCopyLanding');
  }
}
