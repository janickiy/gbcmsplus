<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m190321_144212_domains_check extends Migration
{
  use PermissionTrait;
  /**
  */
  public function up()
  {
    $this->createPermission('PartnersDomainsCheck', 'Проверка припаркованости домена', 'PartnersDomainsController', ['partner']);
  }

  /**
  */
  public function down()
  {
    $this->removePermission('PartnersDomainsCheck');
  }
}
