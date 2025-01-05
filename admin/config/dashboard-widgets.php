<?php

use admin\dashboard\widgets\base\BaseWidget;
use admin\dashboard\widgets\clicks_subscriptions\ClicksSubscriptionsWidget;
use admin\dashboard\widgets\profit\ProfitWidget;
use admin\dashboard\widgets\last_payouts\LastPayoutsWidget;
use admin\dashboard\widgets\top_publishers\TopPublishersWidget;
use admin\dashboard\widgets\top_countries\TopCountriesWidget;
use admin\dashboard\widgets\top_lp\TopLpWidget;

return [
    'clicks_subscriptions' => [
        'position' => BaseWidget::POSITION_LEFT,
        'class' => ClicksSubscriptionsWidget::class,
    ],
    'profit' => [
        'position' => BaseWidget::POSITION_LEFT,
        'class' => ProfitWidget::class,
    ],
    'top_publishers' => [
        'position' => BaseWidget::POSITION_RIGHT,
        'class' => TopPublishersWidget::class,
    ],
    'top_countries' => [
        'position' => BaseWidget::POSITION_RIGHT,
        'class' => TopCountriesWidget::class,
    ],
    'top_lp' => [
        'position' => BaseWidget::POSITION_RIGHT,
        'class' => TopLpWidget::class,
    ],
];
