<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m190416_141524_telegram_notifications_headers extends Migration
{
  use PermissionTrait;
  /**
  */
  public function up()
  {
    // Берем заголовкии браузерных и емейл уведомлений и подставляем их заголовки в телеграмные
    $browserEmailEvents = $this->db->createCommand('SELECT * FROM notifications WHERE notification_type in (1, 2) group by event')->queryAll();

    foreach ($browserEmailEvents as $event) {
      $this->db->createCommand('UPDATE notifications SET header = :header WHERE notification_type = 4 and event = :event', [
        ':header' => $event['header'],
        ':event' => $event['event'],
      ])->execute();
    }
  }

  /**
  */
  public function down()
  {
    return true;
  }
}
