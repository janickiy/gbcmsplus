<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
 */
class m190716_083801_create_wnlight_wallet extends Migration
{
  use PermissionTrait;

  /**
   */
  public function up()
  {
    $this->insert('payment_systems_api', [
      'name' => 'WMLight rub',
      'code' => 'wmlight',
      'currency' => 'rub',
    ]);

    $this->insert('payment_systems_api', [
      'name' => 'WMLight usd',
      'code' => 'wmlight',
      'currency' => 'usd',
    ]);

    $this->insert('payment_systems_api', [
      'name' => 'WMLight eur',
      'code' => 'wmlight',
      'currency' => 'eur',
    ]);
  }

  /**
   */
  public function down()
  {
    $this->delete('payment_systems_api', ['code' => 'wmlight']);
  }
}
