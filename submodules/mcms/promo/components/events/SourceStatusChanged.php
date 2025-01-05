<?php
namespace mcms\promo\components\events;

use mcms\promo\Module;
use Yii;

/**
 * Class SourceStatusChanged
 * @package mcms\promo\components\events
 */
class SourceStatusChanged extends AbstractSource
{
  public function trigger()
  {
    Module::getInstance()->api('badgeCounters')->invalidateCache();
    if ($this->source->isDeclined()) {
      (new SourceRejected($this->source))->trigger();
      return;
    }

    if ($this->source->isEnabled()) {
      (new SourceActivated($this->source))->trigger();
      return;
    }

    parent::trigger();
  }

  public function getOwner()
  {
    return $this->source->user;
  }

  /**
   * @return string
   */
  function getEventName()
  {
    return Yii::_t('promo.events.source_status_changed');
  }
}