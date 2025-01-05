<?php

namespace mcms\pages\components\events;

use mcms\common\event\Event;
use mcms\pages\models\Faq;
use Yii;
use yii\helpers\Url;

class FaqUpdateEvent extends Event
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

  public static function getUrl($id = null)
  {
    return ['/partners/faq/index/'];
  }

  function getEventName()
  {
    return Yii::_t('pages.events.faq_updated');
  }

}