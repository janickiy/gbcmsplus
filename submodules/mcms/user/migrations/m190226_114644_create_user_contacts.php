<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
 */
class m190226_114644_create_user_contacts extends Migration
{
  use PermissionTrait;

  /**
   */
  public function up()
  {
    $this->createTable('user_contacts', [
      'id' => $this->primaryKey(10)->unsigned(),
      'user_id' => 'MEDIUMINT(5) UNSIGNED NOT NULL',
      'type' => $this->tinyInteger(1)->unsigned()->notNull(),
      'data' => $this->string()->notNull(),
      'is_deleted' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
      'created_at' => $this->integer(10)->unsigned()->notNull(),
      'updated_at' => $this->integer(10)->unsigned()->notNull(),
    ]);

    $this->addForeignKey(
      'fk-user-user_contacts',
      'user_contacts',
      'user_id',
      'users',
      'id',
      'CASCADE'
    );

    $this->createPermission('UsersUserContacts', 'Контакты пользователей', 'UserModule', ['root', 'admin']);
    $this->createPermission('UsersUserContactsIndex', 'Просмотр контактов пользователей', 'UsersUserContacts', ['reseller', 'manager']);
    $this->createPermission('UsersUserContactsCreateModal', 'Создание контактов пользователей', 'UsersUserContacts', ['reseller', 'manager']);
    $this->createPermission('UsersUserContactsUpdateModal', 'Редактирование контактов пользователей', 'UsersUserContacts', ['reseller', 'manager']);
    $this->createPermission('UsersUserContactsDelete', 'Удаление контактов пользователей', 'UsersUserContacts', ['reseller', 'manager']);

    // переносим старые контакты в новую табличку
    $this->db->createCommand("
      INSERT INTO user_contacts
      (user_id, type, data, is_deleted, created_at, updated_at)
        SELECT
          up.user_id,
          0                AS type,
          up.skype,
          0                AS is_deleted,
          UNIX_TIMESTAMP() AS created_at,
          UNIX_TIMESTAMP() AS updated_at
        FROM user_params up
        WHERE up.skype IS NOT NULL AND up.skype <> ''
    ")->execute();
  }

  /**
   */
  public function down()
  {
    // возвращаем контакты на старое место
    $this->db->createCommand("
      UPDATE user_params up
        INNER JOIN user_contacts uc ON uc.user_id = up.user_id
      SET up.skype = uc.data
      WHERE uc.type = 0;
    ")->execute();

    $this->removePermission('UsersUserContactsDelete');
    $this->removePermission('UsersUserContactsUpdateModal');
    $this->removePermission('UsersUserContactsCreateModal');
    $this->removePermission('UsersUserContactsIndex');
    $this->removePermission('UsersUserContacts');

    $this->dropForeignKey('fk-user-user_contacts', 'user_contacts');
    $this->dropTable('user_contacts');
  }
}
