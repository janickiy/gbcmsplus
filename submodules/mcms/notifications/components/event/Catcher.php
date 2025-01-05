<?php

namespace mcms\notifications\components\event;

use mcms\common\event\Event;
use mcms\notifications\components\storage\NotificationInterface;
use Yii;

class Catcher
{
  private $notificationStorage;

  /**
   * Catcher constructor.
   * @param NotificationInterface $notificationStorage
   */
  public function __construct(NotificationInterface $notificationStorage)
  {
    $this->notificationStorage = $notificationStorage;
  }

  public function catchEvent(Event $event)
  {
    $notifications = $this->notificationStorage->findNotifications($event);
    if (!count($notifications)) return;

    foreach ($notifications as $notification) {
      $eventHandler = new Handler($notification, $event);
      $eventHandler->sendNotification();
    }
  }
}