<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m190226_151242_user_operator_traffic_filters_off extends Migration
{
  use PermissionTrait;

  const TABLE = 'user_operator_traffic_filters_off';
  const INDEX = 'user_operator_traffic_filters_off_index';

  /**
  */
  public function up()
  {
    $this->createTable(self::TABLE, [
      'id' => 'INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY',
      'user_id' => 'MEDIUMINT(5) UNSIGNED NOT NULL',
      'operator_id' => 'MEDIUMINT(5) UNSIGNED NOT NULL',
    ]);

    $this->createIndex(self::INDEX, self::TABLE, ['user_id', 'operator_id'], true);

    $this->addForeignKey(self::INDEX . '_user_id_fk', self::TABLE, 'user_id', 'users', 'id');
    $this->addForeignKey(self::INDEX . '_operator_id_fk', self::TABLE, 'operator_id', 'operators', 'id');

    $this->createPermission('CanViewTrafficFiltersOffWidget', 'Пользователь может просматривать виджет отключения фильтров трафика', 'PromoPermissions', ['root', 'admin', 'reseller']);
    $this->createPermission('CanTrafficFiltersOff', 'Пользователь может иметь блок отключения фильтров трафика', 'PromoPermissions', ['partner']);


    $this->createPermission('PromoTrafficFiltersOffController', 'Отключение фильтров трафика по партнеру+оператору', 'PromoModule', ['root', 'admin', 'reseller']);

    $this->createPermission('PromoTrafficFiltersOffCreateModal', 'Создание', 'PromoTrafficFiltersOffController');
    $this->createPermission('PromoTrafficFiltersOffUpdateModal', 'Редактирование', 'PromoTrafficFiltersOffController');
    $this->createPermission('PromoTrafficFiltersOffDelete', 'Удаление', 'PromoTrafficFiltersOffController');
  }

  /**
  */
  public function down()
  {
    $this->removePermission('PromoTrafficFiltersOffCreateModal');
    $this->removePermission('PromoTrafficFiltersOffUpdateModal');
    $this->removePermission('PromoTrafficFiltersOffDelete');
    $this->removePermission('PromoTrafficFiltersOffController');


    $this->dropForeignKey(self::INDEX . '_operator_id_fk', self::TABLE);
    $this->dropForeignKey(self::INDEX . '_user_id_fk', self::TABLE);
    $this->dropIndex(self::INDEX, self::TABLE);
    $this->dropTable(self::TABLE);

    $this->removePermission('CanTrafficFiltersOff');
    $this->removePermission('CanViewTrafficFiltersOffWidget');
  }
}
