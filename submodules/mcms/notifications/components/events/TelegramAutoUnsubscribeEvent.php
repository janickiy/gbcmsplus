<?php

namespace mcms\notifications\components\events;

use mcms\common\event\Event;
use mcms\user\models\User;
use Yii;

/**
 * Пользователь автоматически отписан от рассылки телеграм
 * Class TelegramAutoUnsubscribeEvent
 * @package mcms\notifications\components\event
 */
class TelegramAutoUnsubscribeEvent extends Event
{
  /**
   * TelegramAutoUnsubscribeEvent constructor.
   * @param User $owner
   */
  public function __construct(User $owner = null)
  {
    $this->owner = $owner;
  }

  /**
   * @inheritdoc
   */
  public static function getUrl($id = null)
  {
    return ['/notifications/settings/my-notifications/'];
  }

  /**
   * @inheritdoc
   */
  function getEventName()
  {
    return Yii::_t('notifications.events.telegram-auto-unsubscribe');
  }
}