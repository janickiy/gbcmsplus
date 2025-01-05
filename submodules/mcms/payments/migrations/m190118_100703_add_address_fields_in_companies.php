<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
 */
class m190118_100703_add_address_fields_in_companies extends Migration
{
  use PermissionTrait;
  /**
   */
  public function up()
  {
    $this->addColumn('partner_companies', 'city', $this->string());
    $this->addColumn('partner_companies', 'post_code', $this->string());

    $this->addColumn('companies', 'city', $this->string());
    $this->addColumn('companies', 'post_code', $this->string());
  }

  /**
   */
  public function down()
  {
    $this->dropColumn('partner_companies', 'city');
    $this->dropColumn('partner_companies', 'post_code');

    $this->dropColumn('companies', 'city');
    $this->dropColumn('companies', 'post_code');
  }
}
