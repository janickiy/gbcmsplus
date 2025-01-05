<?php

namespace mcms\notifications\components\event\driver;

use mcms\common\helpers\ArrayHelper;
use mcms\notifications\models\Notification;
use mcms\notifications\models\PushNotification;
use mcms\notifications\models\UserPushToken;
use mcms\user\models\User;
use mcms\user\models\UserParam;

/**
 * Class Push
 * @package mcms\notifications\components\event\driver
 */
class Push extends AbstractDriver
{

  /**
   * @param \mcms\user\models\User $receiver
   * @return \mcms\notifications\models\PushNotification|null
   */
  public function send(User $receiver)
  {
    if ($receiver && $receiver->id) {
      // установлен ли токен для пуш уведомлений данному пользователю
      if (!UserPushToken::isUserHaveToken($receiver->id)) return null;
    }

    return parent::send($receiver);
  }


  /**
   * @inheritdoc
   */
  protected function getNotifyNews(UserParam $userParams)
  {
    return $userParams->notify_push_news;
  }

  /**
   * @inheritdoc
   */
  protected function getNotifySystem(UserParam $userParams)
  {
    return $userParams->notify_push_system;
  }

  /**
   * @inheritdoc
   */
  protected function getNotifyCategories(UserParam $userParams)
  {
    return $userParams->getNotifyPushCategories();
  }

  public function sendHandler(User $receiver)
  {
    list($header, $template) = $this->getTranslatedData($receiver);
    return PushNotification::createNotification(
      $receiver,
      $this->getModule(),
      $header,
      $template,
      $this->notificationModel->is_important,
      $this->notificationModel->is_news,
      $this->notificationsDelivery->id,
      $this->event->getEventId(),
      $this->event->getModelId()
    );
  }

  /**
   * Переводы
   * @param User $receiver
   * @return array
   */
  private function getTranslatedData(User $receiver)
  {
    list($header, $template) = $this->translate(
      $this->notificationModel->header,
      $this->notificationModel->template
    );

    $lang =  $receiver->language ?: Notification::DEFAULT_LANG;

    return [
      ArrayHelper::getValue($header, $lang),
      ArrayHelper::getValue($template, $lang),
    ];
  }

}