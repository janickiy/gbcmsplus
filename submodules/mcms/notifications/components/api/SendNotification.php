<?php

namespace mcms\notifications\components\api;

use mcms\common\event\Event;
use mcms\common\exceptions\ParamRequired;
use mcms\common\module\api\ApiResult;
use mcms\notifications\components\event\Handler;
use mcms\notifications\models\Notification;
use yii\base\Exception;
use yii\helpers\ArrayHelper;

class SendNotification extends ApiResult
{
  protected $notificationModel;
  protected $event;

  function init($params = [])
  {
    $this->notificationModel = ArrayHelper::getValue($params, 'notificationModel');
    $this->event = ArrayHelper::getValue($params, 'event');

    if ($this->notificationModel === null) {
      throw (new ParamRequired)->setParamField('notificationModel');
    } else if (!$this->notificationModel instanceof Notification) {
      throw new Exception('param "notificationModel" should be instance of mcms\notifications\models\Notification');
    }

    if ($this->event === null) {
      throw (new ParamRequired)->setParamField('event');
    } else if (!$this->event instanceof Event) {
      throw new Exception('param "event" should be instance of mcms\common\event\Event');
    }
  }

  public function send()
  {
    (new Handler($this->notificationModel, $this->event))->sendNotification();
  }

}