<?php

namespace mcms\notifications\components\event;

use mcms\common\event\Event;
use mcms\user\models\User;
use Yii;

/**
 * Этот класс не следует добавлять в конфиг, так как он для внутреннего использования
 * Class NotificationCreationFormEvent
 * @package mcms\notifications\components\event
 */
class NotificationCreationFormEvent extends Event
{
  /**
   * NotificationCreationFormEvent constructor.
   * @param User $owner
   */
  public function __construct(User $owner = null)
  {
    $this->owner = $owner;
  }

  public function getReplacementsHelp()
  {
    $help = parent::getReplacementsHelp();
    unset($help['{owner.password_reset_token}']);
    return $help;
  }

  function getEventName()
  {
    return Yii::_t('notifications.events.creation_form_event');
  }
}