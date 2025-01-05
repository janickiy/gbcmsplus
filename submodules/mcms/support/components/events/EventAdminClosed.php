<?php

namespace mcms\support\components\events;

use Yii;

class EventAdminClosed extends BaseEventClosed
{
  public function getOwner()
  {
    return $this->ticket->getCreatedBy()->one();
  }

  public function getEventName()
  {
    return Yii::_t('support.events.admin_closed');
  }
}