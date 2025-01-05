<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m190418_080652_system_notification_for_all extends Migration
{
  use PermissionTrait;
  /**
  */
  public function up()
  {
    $this->db->createCommand()->update('user_params', [
      'notify_browser_system' => 1,
      'notify_email_system' => 1,
      'notify_telegram_system' => 1,
      'notify_push_system' => 1,
    ])->execute();
  }

  /**
  */
  public function down()
  {
    echo "m190418_080652_system_notification_for_all cannot be reverted.\n";
    return true;
  }
}
