<?php

use admin\dashboard\gadgets\active_partners\GadgetActivePartners;
use admin\dashboard\gadgets\gross_revenue\GadgetGrossRevenue;
use admin\dashboard\gadgets\net_revenue\GadgetNetRevenue;
use admin\dashboard\gadgets\rs_subs\GadgetRsSubs;
use admin\dashboard\gadgets\cpa_subs\GadgetCpaSubs;

return [
    'grossRevenue' => [
        'class' => GadgetGrossRevenue::class,
    ],
    'netRevenue' => [
        'class' => GadgetNetRevenue::class,
    ],
    'rsSubs' => [
        'class' => GadgetRsSubs::class,
    ],
    'cpaSubs' => [
        'class' => GadgetCpaSubs::class,
    ],
    'activePartners' => [
        'class' => GadgetActivePartners::class,
    ],
];