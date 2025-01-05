<?php

use mcms\common\helpers\Link;

/* @var mcms\promo\Module $promoModule */
$promoModule = Yii::$app->getModule('promo');

$arDetailStatisticUrls = ['/statistic/detail/subscriptions', '/statistic/detail/ik', '/statistic/detail/sells', '/statistic/detail/complains', '/statistic/detail/hit'];
$detailStatisticUrl = null;
foreach ($arDetailStatisticUrls as $url) {
    if (Link::hasAccess($url)) {
        $detailStatisticUrl = $url;
        break;
    }
}

return [
    [
        'label' => Yii::_t('pages.menu.statistic'),
        'iconCls' => 'fa-lg fa-fw icon-statistic',
        'items' => [
            ['label' => Yii::_t('statistic.new_statistic_refactored.title') .
                ' <sup style="color: #6e3671">beta</sup>', 'url' => ['/statistic/new/index']],
            ['label' => Yii::_t('statistic.main.by_dates'), 'url' => ['/statistic/default/index']],
            ['label' => Yii::_t('statistic.main.detail'), 'url' => [$detailStatisticUrl]],
            ['label' => Yii::_t('statistic.main.graphical'), 'url' => ['/statistic/default/graphical']],
            ['label' => Yii::_t('statistic.main.analytics'), 'url' => ['/statistic/analytics/index']],
            ['label' => Yii::_t('statistic.main.by_banners'), 'url' => ['/statistic/banners/index']],
            ['label' => Yii::_t('statistic.main.by_referrals'), 'url' => ['/statistic/referrals/index']],
            ['label' => Yii::_t('statistic.main.postbacks'), 'url' => ['/statistic/postback/index']],
            ['label' => Yii::_t('statistic.main.reseller_income'), 'url' => ['/statistic/reseller-profit-statistics/index']],
        ]
    ],
    [
        'label' => Yii::_t('pages.menu.users'),
        'iconCls' => 'fa-lg fa-fw icon-user',
        'items' => [
            [
                'label' => Yii::_t('users.menu.list'),
                'url' => ['/users/users/list'],
                'events' => [
                    \mcms\user\components\events\EventRegistered::class,
                    \mcms\user\components\events\EventRegisteredHandActivation::class,
                ]
            ],
            [
                'label' => Yii::_t('support.menu.tickets'),
                'url' => ['/support/tickets/list'],
                'events' => [
                    mcms\support\components\events\EventCreated::class,
                    mcms\support\components\events\EventMessageSend::class,
                ],
            ],
            [
                'label' => Yii::_t('pages.menu.partner_programs'),
                'url' => ['/promo/partner-programs/index']
            ],
            ['label' => Yii::_t('notifications.menu.delivery'), 'url' => ['/notifications/delivery/index']],
            ['label' => Yii::_t('promo.buyout_conditions.title'), 'url' => ['/promo/buyout-conditions/index']],
            [
                'label' => Yii::_t('pages.menu.personal_profits'),
                'url' => ['/promo/personal-profits/index']
            ],
            ['label' => Yii::_t('promo.menu.correct_conditions'), 'url' => ['/promo/subscription-correct-conditions/index']],
            ['label' => Yii::_t('users.menu.invitations'), 'url' => ['/users/users-invitations/index']],
            ['label' => Yii::_t('users.menu.login_attempts'), 'url' => ['/users/login-attempts/index']],
        ]
    ],
    [
        'label' => Yii::_t('pages.menu.webmaster'),
        'iconCls' => 'fa-lg fa-fw icon-webmaster',
        'items' => [
            [
                'label' => Yii::_t('pages.menu.sources'),
                'url' => ['/promo/webmaster-sources/index'],
                'events' => [
                    \mcms\promo\components\events\SourceCreatedModeration::class,
                ],
            ],
            ['label' => Yii::_t('promo.menu.landing_sets'), 'url' => ['/promo/landing-sets/index']],
            ['label' => Yii::_t('promo.menu.banners'), 'url' => ['/promo/banners/index']],
            ['label' => Yii::_t('promo.rebill-conditions.main'), 'url' => ['/promo/rebill-conditions/index']],
        ]
    ],
    [
        'label' => Yii::_t('pages.menu.affiliate'),
        'iconCls' => 'fa-lg fa-fw icon-users',
        'items' => [
            [
                'label' => Yii::_t('pages.menu.links'),
                'url' => ['/promo/arbitrary-sources/index'],
                'events' => [
                    \mcms\promo\components\events\LinkCreatedModeration::class,
                ],
            ],
            ['label' => Yii::_t('pages.menu.smart_links'), 'url' => ['/promo/smart-links/index']],
            ['label' => Yii::_t('promo.streams.main'), 'url' => ['/promo/streams/index']],
            ['label' => Yii::_t('promo.menu.domains'), 'url' => ['/promo/domains/index']],
            [
                'label' => Yii::_t('promo.menu.landing_unblock_requests'),
                'url' => ['/promo/landing-unblock-requests/index'],
                'events' => [
                    \mcms\promo\components\events\LandingUnblockRequestCreated::class,
                ],
            ],
            ['label' => Yii::_t('promo.traffic_block.menu'), 'url' => ['/promo/traffic-block/list']],
        ]
    ],
    [
        'label' => Yii::_t('pages.menu.finance'),
        'iconCls' => 'fa-lg fa-fw icon-payments',
        'items' => [
            ['label' => Yii::_t('payments.menu.reseller-profit'), 'url' => ['/statistic/reseller-profit/index']],
            [
                'label' => Yii::_t('payments.menu.payments'),
                'url' => ['/payments/payments/index'],
                'events' => [
                    \mcms\payments\components\events\EarlyPaymentCreated::class,
                ]
            ],
            ['label' => Yii::_t('payments.menu.companies'), 'url' => ['/payments/companies/index']],
        ]
    ],
    [
        'label' => Yii::_t('pages.menu.landings'),
        'iconCls' => 'fa-lg fa-fw icon-landing',
        'items' => [
            ['label' => Yii::_t('promo.menu.landings'), 'url' => ['/promo/landings/index']],
            ['label' => Yii::_t('promo.preland-defaults.main'), 'url' => ['/promo/preland-defaults/index']],
            ['label' => Yii::_t('promo.landing-request-filters.main'), 'url' => ['/promo/landing-request-filters/index']],
        ]
    ],
    [
        'label' => Yii::_t('pages.menu.parameters'),
        'iconCls' => 'fa-lg fa-fw icon-params',
        'items' => [
            ['label' => Yii::_t('pages.menu.mainpage'), 'url' => ['/pages/pages/index']],
            ['label' => Yii::_t('pages.menu.partner_cabinet_style'), 'url' => ['/pages/partner-cabinet-styles/index']],
            [
                'label' => Yii::_t('pages.faq.faq'),
                'url' => ['/pages/faq/index']
            ],
            ['label' => Yii::_t('pages.menu.parameters_notifications'), 'url' => ['/notifications/settings/list']],
            ['label' => Yii::_t('alerts.main.rule-list'), 'url' => ['/alerts/default/index']],
            ['label' => Yii::_t('promo.subscription_limits.menu'), 'url' => ['/promo/subscription-limits/index']],
            ['label' => Yii::_t('promo.caps.menu'), 'url' => ['/promo/caps/index']],
            ['label' => Yii::_t('statistic.postback_data_test.menu'), 'url' => ['/statistic/postback-data-test/index']],
            ['label' => Yii::_t('promo.menu.references'), 'items' => [
                ['label' => Yii::_t('currency.main.menu'), 'url' => ['/currency/default/index']],
                ['label' => Yii::_t('promo.menu.countries'), 'url' => ['/promo/countries/index']],
                ['label' => Yii::_t('promo.menu.regions'), 'url' => ['/promo/regions/index']],
                ['label' => Yii::_t('promo.menu.cities'), 'url' => ['/promo/cities/index']],
                ['label' => Yii::_t('promo.menu.operators'), 'url' => ['/promo/operators/index']],
                ['label' => Yii::_t('promo.menu.providers'), 'url' => ['/promo/providers/index']],
                ['label' => Yii::_t('promo.menu.trafficback_providers'), 'url' => ['/promo/trafficback-providers/index'], 'visible' => Yii::$app->user->identity && Yii::$app->user->identity->canManageTbProviders()],
                ['label' => Yii::_t('promo.menu.offer_categories'), 'url' => ['/promo/offer-categories/index']],
                ['label' => Yii::_t('promo.menu.landing_categories'), 'url' => ['/promo/landing-categories/index']],
                ['label' => Yii::_t('promo.platforms.main_short'), 'url' => ['/promo/platforms/index']],
                ['label' => Yii::_t('promo.traffic-types.main_short'), 'url' => ['/promo/traffic-types/index']],
                ['label' => Yii::_t('promo.landing-pay-types.main_short'), 'url' => ['/promo/landing-pay-types/index']],
                ['label' => Yii::_t('promo.landing-subscription-types.main_short'), 'url' => ['/promo/landing-subscription-types/index']],
                ['label' => Yii::_t('promo.ads-networks.main_short'), 'url' => ['/promo/ads-networks/index']],
                ['label' => Yii::_t('promo.ads-types.main'), 'url' => ['/promo/ads-types/index']],
                ['label' => Yii::_t('support.menu.settings_categories'), 'url' => ['/support/categories/list']],
            ]],
        ]
    ],
    [
        'label' => Yii::_t('pages.menu.administration'),
        'iconCls' => 'fa-lg fa-fw fa fa-gear',
        'items' => [
            ['label' => Yii::_t('users.menu.control'), 'url' => ['/users/admin/']],
            ['label' => Yii::_t('logs.menu.view'), 'url' => ['/logs/default/index']],
            ['label' => Yii::_t('statistic.main.postback-data'), 'url' => ['/statistic/postback-data/index']],
        ]
    ],
];
