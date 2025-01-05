<?php

use mcms\currency\Module;

return [
  'id' => 'currency',
  'class' => Module::class,
  'name' => 'currency.main.module',
  'messages' => '@mcms/currency/messages',
  'apiClasses' => [],
  'fixtures' => [],
  'events' => [
    mcms\currency\components\events\CustomCourseBecameUnprofitable::class,
  ],
];
