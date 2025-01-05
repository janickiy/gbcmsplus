<?php

use mcms\notifications\Module;

return [
  'id' => 'notifications',
  'preload' => true,
  'class' => 'mcms\notifications\Module',
  'name' => 'app.common.module_notifications',
  'menu' => [
    'icon' => 'fa-lg fa-fw glyphicon glyphicon-bullhorn',
    'label' => 'notifications.menu.notifications',
    'items' => [
      ['label' => 'notifications.menu.control', 'url' => ['/notifications/settings/list']],
      ['label' => 'notifications.menu.send_log', 'url' => ['/notifications/notifications/browser']],
      ['label' => 'notifications.menu.delivery', 'url' => ['/notifications/delivery/index']],
      ['label' => 'alerts.main.rule-list', 'url' => ['/alerts/default/index']],
    ]
  ],
  'events' => [
    \mcms\notifications\components\events\TelegramAutoUnsubscribeEvent::class,
  ],
  'messages' => '@mcms/notifications/messages',
  'apiClasses' => [
    'getBrowserNotificationList' => \mcms\notifications\components\api\BrowserNotificationList::class,
    'setBrowserNotificationAsHidden' => \mcms\notifications\components\api\BrowserNotificationSetHidden::class,
    'setBrowserNotificationAsViewed' => \mcms\notifications\components\api\BrowserNotificationSetViewed::class,
    'notifyHeaderWidget' => \mcms\notifications\components\api\NotifyHeaderWidget::class,
    'getBrowserNotificationCount' => \mcms\notifications\components\api\BrowserNotificationCount::class,
    'sendNotification' => \mcms\notifications\components\api\SendNotification::class,
    'setViewedByIdEvent' => \mcms\notifications\components\api\SetViewedByModelIdEvent::class,
  ],
  'pushApiKey' => 'AAAAofTVY8U:APA91bG7uHX3zPkW90JfBB2PflvuF_Jdo7kvKXa6TfFaTu4-eiR0N9ka3BBoAmJRGvTDtyyQNSd8sHt5vUsbI27-Koqc3hhJmGvdk75ylucaraMC9EdvufxRXN1U2GM4cNd2YpiMDiTg',
];