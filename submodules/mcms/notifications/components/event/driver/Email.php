<?php

namespace mcms\notifications\components\event\driver;

use mcms\notifications\models\EmailNotification;
use mcms\notifications\models\Notification;
use mcms\user\models\User;
use mcms\user\models\UserParam;
use yii\helpers\ArrayHelper;

/**
 * Уведомление на email
 */
class Email extends AbstractDriver
{

  /**
   * @inheritdoc
   * TRICKY Что бы отправить уведомление на кастомный email, можно передать несуществующего пользователя-заглушку с нужным email-адресом
   * Пример: @see \mcms\notifications\components\event\Handler::sendAdditionalEmails
   */
  public function send(User $receiver)
  {
    if ($receiver && $receiver->id) {
      $userParams = $receiver->getParams();
      if ($userParams->notify_email) $receiver->email = $userParams->notify_email;
    }

    return parent::send($receiver);
  }

  /**
   * При отправке уведомлений на дополнительные email адреса используется этот драйвер.
   * Так как драйвер для отправки уведомления требует $user и сам оттуда извлекает email, а нам нужно отправить
   * именно на email, который никак не относится к пользователям, для этого КОГДА-ТО был сделан костыль.
   * Суть костыля в том, что вместо настоящего пользователя передается заглушка в виде (new User(['email' => 'custom@email.ru'));
   * По умолчанию в драйверах этот костыль запрещен с помощью этой проверки. Что бы разрешить использования костыля,
   * метод проверки здесь переопределен
   * @inheritdoc
   */
  protected function checkUserIsset(User $user)
  {
    return true;
  }

  /**
   * @inheritdoc
   */
  protected function getNotifyNews(UserParam $userParams)
  {
    return $userParams->notify_email_news;
  }

  /**
   * @inheritdoc
   */
  protected function getNotifySystem(UserParam $userParams)
  {
    return $userParams->notify_email_system;
  }

  /**
   * @inheritdoc
   */
  protected function getNotifyCategories(UserParam $userParams)
  {
    return $userParams->getNotifyEmailCategories();
  }

  public function sendHandler(User $receiver)
  {
    list($from, $header, $template) = $this->getTranslatedData($receiver);
    return EmailNotification::createNotification(
      $receiver,
      $this->getModule(),
      $from,
      $header,
      $template,
      $this->notificationModel->is_important,
      $this->notificationModel->is_news,
      $this->notificationsDelivery->id
    );
  }

  private function getTranslatedData(User $receiver)
  {
    list($from, $header, $template) = $this->translate(
      $this->notificationModel->from,
      $this->notificationModel->header,
      $this->notificationModel->template
    );

    return [
      ArrayHelper::getValue($from, $receiver->language),
      ArrayHelper::getValue($header, $receiver->language),
      ArrayHelper::getValue($template, $receiver->language),
    ];
  }

}