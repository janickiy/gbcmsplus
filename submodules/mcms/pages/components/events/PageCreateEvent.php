<?php

namespace mcms\pages\components\events;

use mcms\common\event\Event;
use mcms\pages\models\Page;
use Yii;

class PageCreateEvent extends Event
{

  public $page;

  /**
   * PageCreateEvent constructor.
   * @param $page
   */
  public function __construct(Page $page)
  {
    $this->page = $page;
  }

  function getEventName()
  {
    return Yii::_t('pages.events.page_created');
  }

}