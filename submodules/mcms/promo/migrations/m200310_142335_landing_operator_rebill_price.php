<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
 */
class m200310_142335_landing_operator_rebill_price extends Migration
{
  use PermissionTrait;

  public function up()
  {
    $this->addColumn(
      'landing_operators',
      'use_landing_operator_rebill_price',
      $this->smallInteger(1)->unsigned()->defaultValue(0)->after('is_deleted')
    );
  }

  public function down()
  {
    $this->dropColumn('landing_operators', 'use_landing_operator_rebill_price');
  }
}
