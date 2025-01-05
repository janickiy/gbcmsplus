<?php

namespace mcms\logs\components\event;

use Yii;
use mcms\common\event\EventObject;
use mcms\logs\models\Logs;
use yii\db\ActiveRecord;

class Catcher
{
  public function catchEvent(EventObject $eventObject)
  {
    $eventData = [];
    foreach ($eventObject->event->getReplacements() as $key => $el) {
      if (!$el instanceof ActiveRecord) $eventData[$key] = $el;
    }

    if (!Yii::$app->getModule('logs')->isLogEnabled()) return;

    $log = new Logs([
      'EventLabel' => $eventObject->event->getEventId(),
      'EventData' => json_encode($eventData, JSON_UNESCAPED_UNICODE)
    ]);

    $log->save();
  }
}