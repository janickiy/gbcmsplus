<?php

namespace mcms\promo\components\events;

use Yii;

class LinkRejected extends AbstractSource
{
  function getEventName()
  {
    return Yii::_t('promo.events.link_rejected');
  }

  public static function getUrl($id = null)
  {
    return ['/partners/links/index/'];
  }

  public function getOwner()
  {
    return $this->source->user;
  }


}