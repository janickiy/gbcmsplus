<?php

namespace mcms\notifications\commands;

use mcms\common\event\Event;
use mcms\notifications\components\event\Handler;
use mcms\notifications\models\Notification;
use mcms\notifications\models\NotificationForRoles;
use Yii;
use yii\console\Controller;
use yii\helpers\Console;


class SendToRolesController extends Controller
{

  /**
   * Консольная команда для отправки нотификаций по ролям
   */
  public function actionIndex()
  {

    $notificationsForRoles = NotificationForRoles::find()->where(['is_send' => 0]);
    foreach ($notificationsForRoles->each() as $notification ) { /* @var NotificationForRoles $notification */

      $notificationModel = new Notification();
      $notificationModel->setAttributes($notification->attributes, false);
      $notificationModel->isReplace = $notification->is_replace;
      $notificationModel->setRoles($notification->roles);
      $event = $notification->getEventObjectInstance() ;
      $event = $event instanceof Event ? $event : new $notificationModel->event;

      try {
        (new Handler($notificationModel, $event))->sendToSelectedRoles($notification->user_id);
        $notification->is_send = 1;
        $notification->save();
      } catch (\Exception $e) {
        $this->stdout(sprintf("%s %s(%s)\n%s\n",
          $e->getMessage(),
          $e->getFile(),
          $e->getLine(),
          $e->getTraceAsString()
        ), Console::FG_RED);
      }
    }

  }

}