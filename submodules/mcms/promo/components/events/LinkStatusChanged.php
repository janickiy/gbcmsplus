<?php

namespace mcms\promo\components\events;

use mcms\promo\models\Source;
use mcms\promo\Module;
use Yii;

class LinkStatusChanged extends AbstractSource
{
  public function trigger()
  {
    Module::getInstance()->api('badgeCounters')->invalidateCache();
    if ($this->source->isDeclined()) {
      (new LinkRejected($this->source))->trigger();
      return ;
    }

    if ($this->source->isEnabled()) {
      (new LinkActivated($this->source))->trigger();
      return ;
    }

    parent::trigger();
  }

  function getEventName()
  {
    //это событие нельзя создать из админки
  }

}