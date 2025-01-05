<?php

return [
  'id' => 'modmanager',
  'class' => 'mcms\modmanager\Module',
  'messages' => '@mcms/modmanager/messages',
  'name' => 'app.common.module_modmanager',
  'menu' => [
    'icon' => 'fa-lg fa-fw fa fa-gear',
    'label' => 'modmanager.menu.module', 'url' => ['/modmanager/modules/index']
  ],
  'apiClasses' => [
    'moduleById' => \mcms\modmanager\components\api\ModuleById::class,
    'modulesWithEvents' => \mcms\modmanager\components\api\ModulesWithEvents::class,
  ]
];
