<?php

return [
  'id' => 'alerts',
  'class' => admin\modules\alerts\Module::class,
  'name' => 'alerts.main.module-name',
  'messages' => '@admin/modules/alerts/messages',
  'events' => [
    \admin\modules\alerts\components\events\AlertEvent::class,
    \admin\modules\alerts\components\events\InfoEvent::class,
    \admin\modules\alerts\components\events\WarningEvent::class,
  ],
];