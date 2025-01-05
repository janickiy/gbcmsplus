<?php

namespace mcms\support\components\events;

use Yii;

class EventPartnerClosed extends BaseEventClosed
{
  public function getOwner()
  {
    return Yii::$app->user->identity;
  }

  public function getEventName()
  {
    return Yii::_t('support.events.partner_closed');
  }
}