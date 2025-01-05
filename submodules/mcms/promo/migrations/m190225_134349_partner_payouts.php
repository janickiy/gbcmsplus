<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m190225_134349_partner_payouts extends Migration
{
  use PermissionTrait;
  /**
  */
  public function up()
  {
    $this->createPermission('PromoLandingsPayouts', 'Отчисления партнерам', 'PromoLandingsController', ['root', 'admin', 'reseller']);
  }

  /**
  */
  public function down()
  {
    $this->removePermission('PromoLandingsPayouts');
  }
}
