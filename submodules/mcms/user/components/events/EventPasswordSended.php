<?php

namespace mcms\user\components\events;

use Yii;
use mcms\user\models\User;

class EventPasswordSended extends AbstractUserEvents
{

  public $user;
  /**
   * EventPasswordSended constructor.
   * @param $user
   */
  public function __construct(?User $user = null)
  {
    $this->user = $user;
  }

  public function getOwner()
  {
    return $this->user;
  }

  function getEventName()
  {
    return Yii::_t('users.events.password_sended');
  }
}
