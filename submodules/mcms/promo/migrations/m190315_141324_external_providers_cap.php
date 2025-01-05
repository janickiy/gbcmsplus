<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m190315_141324_external_providers_cap extends Migration
{
  use PermissionTrait;

  const TABLE1 = 'external_providers';
  const TABLE2 = 'competitive_access_provider';

  /**
  */
  public function up()
  {
    $this->createTable(self::TABLE1, [
      'id' => 'MEDIUMINT(5) UNSIGNED auto_increment primary key',
      'external_id' => 'MEDIUMINT(5) UNSIGNED',
      'name' => 'VARCHAR(100) NOT NULL',
      'url' => 'VARCHAR(255)',
      'provider_id' => 'MEDIUMINT(5) UNSIGNED NOT NULL COMMENT \'Провайдер от которого синкали запись\'',
      'status' => 'TINYINT(1) UNSIGNED NOT NULL',
      'sync_at' => 'INT UNSIGNED NOT NULL',
    ]);

    $this->createIndex(self::TABLE1 . 'code_index', self::TABLE1, ['external_id', 'provider_id'], true);

    $this->createTable(self::TABLE2, [
      'id' => 'MEDIUMINT(5) UNSIGNED auto_increment primary key',
      'external_id' => 'MEDIUMINT(5) UNSIGNED',
      'day_limit' => 'MEDIUMINT(5) UNSIGNED NOT NULL',
      'external_provider_id' => 'MEDIUMINT(5) UNSIGNED COMMENT \'Связь с таблицей external_providers\'',
      'operator_id' => 'MEDIUMINT(5) UNSIGNED',
      'service_id' => 'MEDIUMINT(5) UNSIGNED',
      'landing_id' => 'MEDIUMINT(5) UNSIGNED',
      'provider_id' => 'MEDIUMINT(5) UNSIGNED NOT NULL COMMENT \'Провайдер от которого синкали запись\'',
      'active_from' => 'INT UNSIGNED',
      'is_blocked' => 'TINYINT(1) UNSIGNED NOT NULL',
      'status' => 'TINYINT(1) UNSIGNED NOT NULL',
      'sync_at' => 'INT UNSIGNED NOT NULL',
    ]);

    $this->createIndex(self::TABLE2 . 'code_index', self::TABLE2, ['external_id', 'provider_id'], true);

    $this->createPermission('PromoCapsController', 'Реселлерские лимиты', 'PromoModule');
    $this->createPermission('PromoCapsIndex', 'Список реселлерских лимитов', 'PromoCapsController', ['root', 'admin', 'reseller']);
  }

  /**
  */
  public function down()
  {
    $this->dropIndex(self::TABLE1 . 'code_index', self::TABLE1);
    $this->dropIndex(self::TABLE2 . 'code_index', self::TABLE2);

    $this->dropTable(self::TABLE1);
    $this->dropTable(self::TABLE2);

    $this->removePermission('PromoCapsController');
    $this->removePermission('PromoCapsIndex');
  }
}
