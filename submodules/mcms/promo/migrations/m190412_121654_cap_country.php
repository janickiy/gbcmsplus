<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m190412_121654_cap_country extends Migration
{
  use PermissionTrait;

  const TABLE = 'external_providers';
  const TABLE_SERVICES = 'services';
  const TABLE_CAP = 'competitive_access_provider';

  /**
  */
  public function up()
  {
    $this->delete(self::TABLE);
    $this->delete(self::TABLE_CAP);

    $this->addColumn(self::TABLE, 'country_id', 'mediumint(5) unsigned default null after url');
    $this->addColumn(self::TABLE, 'local_name', 'varchar(100) default null after name');

    $this->assignRolesPermission('PromoCapsIndex', ['manager']);
    $this->createPermission('PromoCapsUpdateExternalProvider', 'Список реселлерских лимитов', 'PromoCapsController', ['root', 'admin', 'reseller', 'manager']);

    $this->createTable(self::TABLE_SERVICES, [
      'id'          => 'MEDIUMINT(5) UNSIGNED auto_increment primary key',
      'external_id' => 'MEDIUMINT(5) UNSIGNED',
      'name'        => 'VARCHAR(255) NOT NULL',
      'url'         => 'VARCHAR(255) NOT NULL',
      'provider_id' => 'MEDIUMINT(5) UNSIGNED NOT NULL',
      'status'      => 'TINYINT(1) UNSIGNED NOT NULL',
      'sync_at'     => 'INT UNSIGNED NOT NULL',
    ]);

  }

  /**
  */
  public function down()
  {
    $this->dropTable(self::TABLE_SERVICES);
    $this->removePermission('PromoCapsUpdateExternalProvider');
    $this->revokeRolesPermission('PromoCapsIndex', ['manager']);

    $this->dropColumn(self::TABLE, 'country_id');
    $this->dropColumn(self::TABLE, 'local_name');
  }
}
