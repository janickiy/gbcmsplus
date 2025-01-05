<?php

namespace mcms\pages\components\events;

use mcms\common\event\Event;
use mcms\pages\models\Faq;
use Yii;

class FaqCreateEvent extends Event
{

  public $faq;

  /**
   * FaqCreateEvent constructor.
   * @param $faq
   */
  public function __construct(Faq $faq = null)
  {
    $this->faq = $faq;
  }

  public function shouldSendNotification()
  {
    return $this->faq->isVisible();
  }

  function getEventName()
  {
    return Yii::_t('pages.events.faq_created');
  }

}