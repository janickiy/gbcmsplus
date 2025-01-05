<?php
namespace mcms\promo\components\events;

use mcms\common\event\Event;
use mcms\promo\models\Stream;
use Yii;

/**
 * Class StreamChanged
 * @package mcms\promo\components\events
 */
class StreamChanged extends Event
{

  /**
   * @var null
   */
  public $stream;

  /**
   * @param Stream $stream
   */
  public function __construct(Stream $stream = null)
  {
    $this->stream = $stream;
  }

  public function getModelId()
  {
    return $this->stream->id;
  }


  public function shouldSendNotification()
  {
    return Yii::$app->authManager->checkAccess($this->stream->user_id, 'CanStreamNotificationSend');
  }

  /**
   * @return string
   */
  function getEventName()
  {
    return Yii::_t('promo.events.stream_changed');
  }
}