<?php

namespace mcms\notifications\components\storage;

use mcms\common\event\Event;

interface NotificationInterface
{
  /**
   * @param $event
   * @return \mcms\notifications\models\Notification[]
   */
  public function findNotifications(Event $event);
}