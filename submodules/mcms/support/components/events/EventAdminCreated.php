<?php

namespace mcms\support\components\events;

use Yii;
use yii\helpers\Url;

class EventAdminCreated extends EventCreated
{
  function getEventName()
  {
    return Yii::_t('support.events.admin_created');
  }

  public function getOwner()
  {
    return $this->ticket->getCreatedBy()->one();
  }

  public static function getUrl($id = null)
  {
    return ['/partners/support/index/'];
  }
}