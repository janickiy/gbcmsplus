<?php
namespace mcms\support\components\events;

use mcms\common\event\Event;
use mcms\support\models\Support;
use mcms\support\Module;
use Yii;
use yii\helpers\Url;

class EventCreated extends Event
{
  public $ticket;
  /**
   * EventCreated constructor.
   * @param $ticket
   */
  public function __construct(Support $ticket = null)
  {
    $this->ticket = $ticket;
  }

  public function getModelId()
  {
    return $this->ticket ? $this->ticket->id : null;
  }

  public function incrementBadgeCounter()
  {
    return $this->ticket->hasUnreadMessages() && $this->ticket->isOpened();
  }

  function getEventName()
  {
    return Yii::_t('support.events.created');
  }

  public static function getUrl($id = null)
  {
    return ['/support/tickets/view/', 'id' => $id];
  }

  public function trigger()
  {
    parent::trigger();
    Module::getInstance()->api('badgeCounters')->invalidateCache();
  }


}