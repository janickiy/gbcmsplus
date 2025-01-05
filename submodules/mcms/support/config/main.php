<?php
return [
  'id' => 'support',
  'class' => 'mcms\support\Module',
  'name' => 'app.common.module_support',
  'menu' => [
    'icon' => 'fa-lg fa-fw icon-support',
    'label' => 'support.menu.module',
    'events' => [
      mcms\support\components\events\EventCreated::class,
      mcms\support\components\events\EventMessageSend::class,
    ],
    'items' => [
      [
        'label' => 'support.menu.settings_categories',
        'url' => ['/support/categories/list']
      ],
      [
        'label' => 'support.menu.tickets',
        'url' => ['/support/tickets/list'],
        'events' => [
          mcms\support\components\events\EventCreated::class,
          mcms\support\components\events\EventMessageSend::class,
        ],
      ],
    ]
  ],
  'messages' => '@mcms/support/messages',
  'events' => [
    \mcms\support\components\events\EventCreated::class,
    \mcms\support\components\events\EventAdminCreated::class,
    \mcms\support\components\events\EventDelegated::class,
    \mcms\support\components\events\EventAdminClosed::class,
    \mcms\support\components\events\EventPartnerClosed::class,
    \mcms\support\components\events\EventMessageReceived::class,
    \mcms\support\components\events\EventMessageSend::class,
    \mcms\support\components\events\EventStatusChanged::class
  ],
  'apiClasses' => [
    'getTicket' => \mcms\support\components\api\Ticket::class,
    'getTicketList' => \mcms\support\components\api\TicketList::class,
    'getTicketCategories' => \mcms\support\components\api\TicketCategoryList::class,
    'createTicket' => mcms\support\components\api\TicketCreate::class,
    'closeTicket' => \mcms\support\components\api\TicketClose::class,
    'readTicket' => \mcms\support\components\api\TicketRead::class,
    'sendTicketMessage' => \mcms\support\components\api\TicketSendMessage::class,
    'editTicketMessage' => \mcms\support\components\api\TicketEditMessage::class,
    'badgeCounters' => \mcms\support\components\api\BadgeCounters::class,
  ]

];