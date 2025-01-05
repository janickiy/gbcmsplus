<?php

namespace mcms\notifications\components\storage;

use mcms\common\event\Event;

class Notification implements NotificationInterface
{

  /** @var \mcms\notifications\models\Notification */
  private $notification;

  /**
   * @param \mcms\notifications\models\Notification $notification
   */
  public function __construct(\mcms\notifications\models\Notification $notification)
  {
    $this->notification = $notification;
  }

  public function findNotifications(Event $event)
  {
    return $this
      ->notification
      ->find()
      ->where(
        'event = :event AND notification_type > 0 AND is_disabled = 0',
        [
          'event' => $event->class,
        ])
      ->all();
  }

}