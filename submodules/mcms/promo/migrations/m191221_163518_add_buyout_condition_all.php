<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m191221_163518_add_buyout_condition_all extends Migration
{
  use PermissionTrait;
  /**
  */
  public function up()
  {
    $this->addColumn(
      'buyout_conditions',
      'is_buyout_all',
      $this->tinyInteger(1)->unsigned()->after('is_buyout_only_unique_phone')
    );
  }

  /**
   */
  public function down()
  {
    $this->dropColumn('buyout_conditions', 'is_buyout_all');
  }
}
