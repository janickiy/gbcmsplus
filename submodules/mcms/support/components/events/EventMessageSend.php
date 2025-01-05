<?php

namespace mcms\support\components\events;

use mcms\common\event\Event;
use mcms\support\models\Support;
use mcms\support\models\SupportText;
use mcms\support\Module;
use Yii;

class EventMessageSend extends Event
{
  public $ticket;
  public $message;

  /**
   * партнер ответил на тикет
   * EventMessageReceived constructor.
   * @param Support $ticket
   * @param SupportText $message
   */
  public function __construct(Support $ticket = null, SupportText $message = null)
  {
    $this->ticket = $ticket;
    $this->message = $message;
  }

  public function getModelId()
  {
    return $this->ticket ? $this->ticket->id : null;
  }

  public static function getUrl($id = null)
  {
    return ['/support/tickets/view/', 'id' => $id];
  }

  public function incrementBadgeCounter()
  {
    return $this->ticket->hasUnreadMessages();
  }

  public function trigger()
  {
    parent::trigger();
    //партнер отвечает, его уведомления по этому тикету делаем прочитанным
    Yii::$app->getModule('notifications')->api('setViewedByIdEvent', [
      'event' => EventMessageReceived::class,
      'modelId' => $this->getModelId(),
      'onlyOwner' => true,
    ])->getResult();

    Module::getInstance()->api('badgeCounters')->invalidateCache();
  }


  function getEventName()
  {
    return Yii::_t('support.events.message_send');
  }
}