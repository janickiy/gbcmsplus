<?php

namespace mcms\notifications\components\event\driver;


use mcms\notifications\models\BrowserNotification;
use mcms\notifications\models\Notification;
use mcms\notifications\models\NotificationBadgeCounter;
use mcms\user\models\User;
use mcms\user\models\UserParam;

class Browser extends AbstractDriver
{
  /**
   * @inheritdoc
   */
  protected function getNotifyNews(UserParam $userParams)
  {
    return $userParams->notify_browser_news;
  }

  /**
   * @inheritdoc
   */
  protected function getNotifySystem(UserParam $userParams)
  {
    return $userParams->notify_browser_system;
  }

  /**
   * @inheritdoc
   */
  protected function getNotifyCategories(UserParam $userParams)
  {
    return $userParams->getNotifyBrowserCategories();
  }

  /**
   * @inheritdoc
   */
  public function sendHandler(User $receiver)
  {
    list($from, $header, $template) = $this->translate(
      $this->notificationModel->from,
      $this->notificationModel->header,
      $this->notificationModel->template
    );

    if ($this->notificationModel->isReplace) {
      /** @var \mcms\notifications\models\Notification $notification */
      $notification = BrowserNotification::find()
        ->andWhere([
          'event' => $this->event->getEventId(),
          'from_user_id' => $this->getNotificationsDelivery()->user_id,
          'user_id' => $receiver->id,
          'is_viewed' => 0,
          'model_id' => $this->event->getModelId(),
          'is_important' => $this->notificationModel->is_important,
          'is_news' => $this->notificationModel->is_news,
        ])
        ->orderBy(['created_at' => SORT_DESC])
        ->one();

      if ($notification !== null) {
        $notification->header = $header;
        $notification->message = $template;
        $notification->notifications_delivery_id = $this->notificationsDelivery->id;
        return $notification->save();
      }
    }

    return BrowserNotification::createNotification(
      $receiver,
      $this->getModule(),
      $from,
      $header,
      $template,
      $this->event->getEventId(),
      $this->event->getModelId(),
      $this->notificationModel->is_important,
      $this->notificationModel->is_news,
      $this->event,
      $this->notificationsDelivery->id
    );
  }
}