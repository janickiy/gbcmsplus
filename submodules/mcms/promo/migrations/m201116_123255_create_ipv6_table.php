<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
 */
class m201116_123255_create_ipv6_table extends Migration
{
  use PermissionTrait;

  public function up()
  {
    $this->createTable('operator_ipv6', [
      'id' => $this->primaryKey(10)->unsigned(),
      'operator_id' => 'MEDIUMINT(5) UNSIGNED NOT NULL',
      'ip' => $this->string()->notNull(),
      'mask' => $this->tinyInteger(1)->unsigned(),
    ]);

    $this->addForeignKey(
      'fk-operator_ipv6-operator',
      'operator_ipv6',
      'operator_id',
      'operators',
      'id',
      'CASCADE'
    );

    $this->createIndex(
      'idx-oim',
      'operator_ipv6',
      ['operator_id', 'ip', 'mask'],
      true
    );
  }

  public function down()
  {
    $this->dropForeignKey('fk-operator_ipv6-operator', 'operator_ipv6');
    $this->dropTable('operator_ipv6');
  }
}
