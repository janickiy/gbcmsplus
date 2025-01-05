<?php

namespace mcms\support\components\events;

use mcms\common\event\Event;
use mcms\support\models\Support;
use mcms\support\models\SupportText;
use mcms\support\Module;
use mcms\user\models\User;
use Yii;

class EventMessageReceived extends Event
{
  public $user;
  public $ticket;
  public $message;

  /**
   * партнер получил ответ на тикет
   * EventMessageReceived constructor.
   * @param Support $ticket
   * @param SupportText $message
   * @param User $owner
   */
  public function __construct(Support $ticket = null, SupportText $message = null, User $owner = null)
  {
    $this->ticket = $ticket;
    $this->message = $message;
    $this->owner = $owner;
  }

  public function getModelId()
  {
    return $this->ticket ? $this->ticket->id : null;
  }

  public static function getUrl($id = null)
  {
    return ['/partners/support/index/'];
  }

  public function trigger()
  {
    parent::trigger();
    //админ реселлер ответили в тикет
    //бейджы и уведомление надо пометить прочитанным
    Yii::$app->getModule('notifications')->api('setViewedByIdEvent', [
      'event' => [
        EventMessageSend::class,
        EventCreated::class,
      ],
      'modelId' => $this->getModelId(),
    ])->getResult();

    Module::getInstance()->api('badgeCounters')->invalidateCache();
  }


  function getEventName()
  {
    return Yii::_t('support.events.message_received');
  }
}