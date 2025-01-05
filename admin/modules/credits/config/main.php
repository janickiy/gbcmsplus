<?php

use admin\modules\credits\events\CreditApprovedEvent;
use admin\modules\credits\events\CreditDoneEvent;
use admin\modules\credits\events\CreditDeclinedEvent;
use admin\modules\credits\events\CreditExternalPaymentEvent;

return [
    'id' => 'credits',
    'class' => admin\modules\credits\Module::class,
    'name' => 'credits.main.credits',
    'messages' => '@admin/modules/credits/messages',
    'events' => [
        CreditApprovedEvent::class,
        CreditDeclinedEvent::class,
        CreditDoneEvent::class,
        CreditExternalPaymentEvent::class,
    ],
];
