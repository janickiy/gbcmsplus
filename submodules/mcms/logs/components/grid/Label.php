<?php

namespace mcms\logs\components\grid;

use mcms\common\event\Event;
use yii\grid\DataColumn;

class Label extends DataColumn
{
  public function init()
  {
    parent::init();
    $this->value = function($model) {
      /** @var \mcms\logs\models\Logs $model*/
      if (!class_exists($model['EventLabel'])) {
        return $model['EventLabel'];
      }
      $eventInstance = \Yii::$container->get($model['EventLabel']);
      if (!$eventInstance instanceof Event) return null;

      return $eventInstance->getEventName();
    };
  }
}