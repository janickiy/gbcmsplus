<?php

namespace mcms\promo\components\events;

use mcms\promo\Module;
use Yii;

class SourceCreatedModeration extends AbstractSource
{

  public function incrementBadgeCounter()
  {
    return $this->source->isStatusModeration();
  }

  function getEventName()
  {
    return Yii::_t('promo.events.source_created_moderation');
  }

  public function trigger()
  {
    parent::trigger();
    Module::getInstance()->api('badgeCounters')->invalidateCache();
  }


}