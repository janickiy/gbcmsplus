<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m190130_123506_remove_admin_notifications_permissions extends Migration
{
  use PermissionTrait;

  /**
   * @return bool|void
   * @throws Exception
   */
  public function up()
  {
    $this->revokeRolesPermission('NotificationsTakenNotificationsClear', ['root', 'admin', 'reseller', 'manager']);
    $this->revokeRolesPermission('NotificationsTakenNotificationsView', ['root', 'admin', 'reseller', 'manager']);
    $this->removeChildPermission('NotificationsTakenNotificationsController', 'NotificationsTakenNotificationsClear');
    $this->removeChildPermission('NotificationsTakenNotificationsController', 'NotificationsTakenNotificationsView');
    $this->removePermission('NotificationsTakenNotificationsClear');
    $this->removePermission('NotificationsTakenNotificationsView');
    $this->removeChildPermission('NotificationsModule', 'NotificationsTakenNotificationsController');
    $this->removePermission('NotificationsTakenNotificationsController');
  }

  /**
  */
  public function down()
  {
    $this->createPermission('NotificationsTakenNotificationsController', 'Контроллер TakenNotifications', 'NotificationsModule');
    $this->createPermission('NotificationsTakenNotificationsClear', 'Пометка всех уведомлений просмотренными и скрытыми', 'NotificationsTakenNotificationsController', ['root', 'admin', 'reseller', 'manager']);
    $this->createPermission('NotificationsTakenNotificationsView', 'Просмотр списка уведомлений текущему пользователю', 'NotificationsTakenNotificationsController', ['root', 'admin', 'reseller', 'manager']);
  }
}
