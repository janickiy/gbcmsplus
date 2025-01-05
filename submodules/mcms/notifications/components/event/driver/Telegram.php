<?php

namespace mcms\notifications\components\event\driver;

use mcms\common\helpers\ArrayHelper;
use mcms\notifications\models\Notification;
use mcms\notifications\models\TelegramNotification;
use mcms\user\models\User;
use mcms\user\models\UserParam;
use yii\helpers\Html;

/**
 * Class Telegram
 * @package mcms\notifications\components\event\driver
 */
class Telegram extends AbstractDriver
{

  /**
   * @param \mcms\user\models\User $receiver
   * @return \mcms\notifications\models\TelegramNotification|null
   */
  public function send(User $receiver)
  {
    if ($receiver && $receiver->id) {
      // установлен ли telegram_id
      if (!$receiver->getParams()->telegram_id) return null;
    }

    return parent::send($receiver);
  }

  /**
   * @inheritdoc
   */
  protected function getNotifyNews(UserParam $userParams)
  {
    return $userParams->notify_telegram_news;
  }

  /**
   * @inheritdoc
   */
  protected function getNotifySystem(UserParam $userParams)
  {
    return $userParams->notify_telegram_system;
  }

  /**
   * @inheritdoc
   */
  protected function getNotifyCategories(UserParam $userParams)
  {
    return $userParams->getNotifyTelegramCategories();
  }

  public function sendHandler(User $receiver)
  {
    $template = $this->getTranslatedTemplate($receiver);
    return TelegramNotification::createNotification(
      $receiver,
      $this->getModule(),
      $template,
      $this->notificationModel->is_important,
      $this->notificationModel->is_news,
      $this->notificationsDelivery->id
    );
  }

  /**
   * Перевод темплейта
   * @param User $receiver
   * @return mixed
   */
  private function getTranslatedTemplate(User $receiver)
  {

    list($header, $template) = $this->translate(
      $this->notificationModel->header,
      $this->notificationModel->template
    );

    //TRICKY добавляем заголовок к сообщению c тегом b, заменяем теги br на переносы строк и вырезаем другие теги
    //потому что при отправке с параметром 'parse_mode' => 'HTML' при неизвестном теге будет ошибка.
    return Html::tag('b', ArrayHelper::getValue($header, $receiver->language ?: Notification::DEFAULT_LANG))
      . "\n" .
      strip_tags(preg_replace('/<br\s?\/?>/ius', "\n", ArrayHelper::getValue($template, $receiver->language ?: Notification::DEFAULT_LANG)));

  }

}