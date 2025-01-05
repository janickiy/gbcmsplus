<?php

return [
  'id'       => 'logs',
  'preload'  => true,
  'class'    => 'mcms\logs\Module',
  'name'     => 'app.common.module_logs',
  'events'   => [
  ],
  'menu'     => [
    'icon' => 'fa-lg fa-fw fa fa-book',
    'label' => 'logs.menu.main',
    'items' => [
      ['label' => 'logs.menu.view', 'url' => ['/logs/default/index']]
    ]
  ],
  'messages' => '@mcms/logs/messages',
];
