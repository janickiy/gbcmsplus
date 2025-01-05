<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m190313_120424_users_operator_landings extends Migration
{
  use PermissionTrait;

  const TABLE = 'users_operator_landings';
  /**
  */
  public function up()
  {
    $this->createTable(self::TABLE, [
      'user_id' => 'MEDIUMINT(5) UNSIGNED NOT NULL',
      'operator_id' => 'MEDIUMINT(5) UNSIGNED NOT NULL',
      'landing_id' => 'MEDIUMINT(5) UNSIGNED NOT NULL',
      'updated_at' => 'INT(10) UNSIGNED NOT NULL',
    ]);

    $this->createIndex(self::TABLE . 'user_id_operator_id_landing_id_index', self::TABLE, ['user_id', 'operator_id', 'landing_id'], true);
    $this->createIndex(self::TABLE . 'user_id_index', self::TABLE, 'user_id');
    $this->createIndex(self::TABLE . 'operator_id_index', self::TABLE, 'operator_id');
    $this->createIndex(self::TABLE . 'landing_id_index', self::TABLE, 'landing_id');
    $this->createIndex(self::TABLE . 'updated_at_index', self::TABLE, 'updated_at');

    $this->execute('
      INSERT INTO ' . self::TABLE . ' (user_id, operator_id, landing_id, updated_at)
      SELECT s.user_id, sol.operator_id, sol.landing_id, UNIX_TIMESTAMP()
      FROM sources_operator_landings sol
      LEFT JOIN sources s ON s.id=sol.source_id
      GROUP BY s.user_id, sol.operator_id, sol.landing_id
      ORDER BY NULL;
    ');
  }

  /**
  */
  public function down()
  {
    $this->dropIndex(self::TABLE . 'user_id_operator_id_landing_id_index', self::TABLE);
    $this->dropIndex(self::TABLE . 'user_id_index', self::TABLE);
    $this->dropIndex(self::TABLE . 'operator_id_index', self::TABLE);
    $this->dropIndex(self::TABLE . 'landing_id_index', self::TABLE);
    $this->dropIndex(self::TABLE . 'updated_at_index', self::TABLE);
    $this->dropTable(self::TABLE);
  }
}
