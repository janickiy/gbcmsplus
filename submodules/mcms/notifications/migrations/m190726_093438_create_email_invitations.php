<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m190726_093438_create_email_invitations extends Migration
{
  use PermissionTrait;
  /**
  */
  public function up()
  {
    $this->createTable('users_invitations_emails', [
      'id' => $this->primaryKey()->unsigned(),
      'from' => $this->string()->notNull(),
      'header' => $this->string()->notNull(),
      'template' => $this->text(),
      'is_complete' => $this->tinyInteger(1)->notNull()->unsigned()->defaultValue(0),
      'created_at' => $this->integer(10)->unsigned()->notNull(),
      'updated_at' => $this->integer(10)->unsigned()->notNull(),
    ]);

    $this->createTable('users_invitations_emails_sent', [
      'id' => $this->primaryKey()->unsigned(),
      'invitation_email_id' => $this->integer(10)->unsigned(),
      'invitation_id' => $this->integer(10)->unsigned(),
      'from' => $this->string()->notNull(),
      'to' => $this->string()->notNull(),
      'header' => $this->string()->notNull(),
      'message' => $this->text(),
      'is_sent' => $this->tinyInteger(1)->notNull()->unsigned()->defaultValue(0),
      'attempts' => $this->tinyInteger(1)->notNull()->unsigned()->defaultValue(0),
      'error' => $this->string(),
      'created_at' => $this->integer(10)->unsigned()->notNull(),
      'updated_at' => $this->integer(10)->unsigned()->notNull(),
    ]);

    $this->addForeignKey(
      'fk-uies-uie',
      'users_invitations_emails_sent',
      'invitation_email_id',
      'users_invitations_emails',
      'id',
      'SET NULL'
    );

    $this->addForeignKey(
      'fk-uies-users_invitations',
      'users_invitations_emails_sent',
      'invitation_id',
      'users_invitations',
      'id',
      'SET NULL'
    );

    $this->createPermission('NotificationsUsersInvitations', 'Уведомления приглашений пользователей', 'NotificationsModule', ['root', 'admin']);
    $this->createPermission('NotificationsUsersInvitationsIndex', 'Просмотр уведомлений приглашений пользователей', 'NotificationsUsersInvitations', ['reseller', 'manager']);
    $this->createPermission('NotificationsUsersInvitationsCreateModal', 'Создание уведомлений приглашений пользователей', 'NotificationsUsersInvitations', ['reseller', 'manager']);
    $this->createPermission('NotificationsUsersInvitationsUpdateModal', 'Редактирование уведомлений приглашений пользователей', 'NotificationsUsersInvitations', ['reseller', 'manager']);
    $this->createPermission('NotificationsUsersInvitationsDelete', 'Удаление уведомлений приглашений пользователей', 'NotificationsUsersInvitations', ['reseller', 'manager']);
    $this->createPermission('NotificationsUsersInvitationsSent', 'Просмотр списка отправленных', 'NotificationsUsersInvitations', ['reseller', 'manager']);
    $this->createPermission('NotificationsUsersInvitationsSentView', 'Просмотр одного отправленного', 'NotificationsUsersInvitations', ['reseller', 'manager']);
  }

  /**
  */
  public function down()
  {
    $this->removePermission('NotificationsUsersInvitationsSentView');
    $this->removePermission('NotificationsUsersInvitationsSent');
    $this->removePermission('NotificationsUsersInvitationsDelete');
    $this->removePermission('NotificationsUsersInvitationsUpdateModal');
    $this->removePermission('NotificationsUsersInvitationsCreateModal');
    $this->removePermission('NotificationsUsersInvitationsIndex');
    $this->removePermission('NotificationsUsersInvitations');

    $this->dropTable('users_invitations_emails_sent');
    $this->dropTable('users_invitations_emails');
  }
}
