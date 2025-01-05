<?php

use mcms\common\helpers\ArrayHelper;
use mcms\common\rbac\AuthItemsManager;

use mcms\user\models\User;

/** @var \mcms\payments\Module $modulePayments */
$modulePayments = Yii::$app->getModule('payments');
/** @var string $currentPage текущая страница */
try {
    $currentPage = ArrayHelper::getValue(Yii::$app->request->resolve(), 0);
} catch (\yii\web\NotFoundHttpException $e) {
    $currentPage = ''; // иначе при отображении 404 ошибки возникает ещё раз 404 ошибка, и из-за этого уже получается 500
};

$canEditUser = false;
// TODO Ищет пользователя даже если мы открываем регион, слишком жестко ))
if (Yii::$app->request->get('id')) {
    $user = User::findOne((int)Yii::$app->request->get('id'));
    $role = $user ? $user->getRole()->one() : null;

    $canEditUser = $role ? Yii::$app->user->can(
        (new AuthItemsManager)->getRolePermissionName($role->name)
    ) : false;
}

$userTabs = [
    [
        'label' => Yii::_t('users.main.view'),
        'url' => ['/users/users/view', 'id' => Yii::$app->request->get('id')],
    ],
    [
        'label' => Yii::_t('users.main.edit'),
        'url' => ['/users/users/update', 'id' => Yii::$app->request->get('id')],
        'visible' => $canEditUser
    ],
    [
        'label' => Yii::_t('payments.users.balance'),
        'url' => ['/payments/users/view', 'id' => Yii::$app->request->get('id')],
        'visible' => (
            $currentPage == 'users/users/view' ||
            $currentPage == 'users/users/update' ||
            $currentPage == 'payments/users/profit' ||
            $currentPage == 'payments/users/view'
        )
            ? $modulePayments::canUserHaveBalance(Yii::$app->request->get('id'))
            : false
    ],
];

$partnerPrograms = [
    [
        'label' => Yii::_t('promo.menu.partner_programs'),
        'url' => ['/promo/partner-programs/index'],
    ],
];

$userList = [
    [
        'label' => Yii::_t('users.menu.list'),
        'url' => ['/users/users/list'],
    ],
    [
        'label' => Yii::_t('promo.landing_operator_price.title'),
        'url' => ['/promo/landings/payouts'],
    ],
];

$userInvitations = [
    [
        'label' => Yii::_t('users.menu.invitations'),
        'url' => ['/users/users-invitations/index'],
    ],
    [
        'label' => Yii::_t('notifications.menu.invitations_emails'),
        'url' => ['/notifications/users-invitations/index'],
    ],
    [
        'label' => Yii::_t('notifications.menu.invitations_emails_sent'),
        'url' => ['/notifications/users-invitations/sent'],
    ],
];

$banners = [
    [
        'label' => Yii::_t('promo.banners.banner_list'),
        'url' => ['/promo/banners/index'],
    ],
    [
        'label' => Yii::_t('promo.banner-templates.list'),
        'url' => ['/promo/banner-templates/index'],
    ],
];

$resellerPayout = [
    [
        'label' => Yii::_t('payments.menu.reseller_settlement_statistic'),
        'url' => ['/statistic/reseller-profit/index'],
    ],
    [
        'label' => Yii::_t('statistic.reseller_profit.hold_rules'),
        'url' => ['/statistic/reseller-hold-rules/index'],
    ],
    [
        'label' => Yii::_t('payments.menu.reseller_balances_and_settlement'),
        'url' => ['/payments/reseller-checkout/index'],
    ],
    [
        'label' => Yii::_t('payments.menu.reseller_invoices'),
        'url' => ['/payments/reseller-invoices/index'],
    ],
    [
        'label' => Yii::_t('payments.menu.reseller-payout-settings'),
        'url' => ['/payments/payments/reseller-settings'],
        'visible' => (
            $currentPage == 'statistic/reseller-profit/index' ||
            $currentPage == 'payments/reseller-checkout/index' ||
            $currentPage == 'payments/reseller-invoices/index' ||
            $currentPage == 'payments/payments/reseller-settings' ||
            $currentPage == 'credits/credits/index' ||
            $currentPage == 'credits/credits/view' ||
            $currentPage == 'loyalty/bonuses/index' ||
            $currentPage == 'statistic/reseller-hold-rules/index'
        )
            ? $modulePayments::canUserHaveBalance(Yii::$app->user->id)
            : false,
    ],
    [
        'label' => Yii::_t('credits.main.credits'),
        'url' => ['/credits/credits/index'],
    ],
    [
        'label' => Yii::_t('credits.main.credits'),
        'url' => ['/credits/credits/view'],
        'visible' => false,
    ],
    [
        'label' => Yii::_t('loyalty.main.loyalty'),
        'url' => ['/loyalty/bonuses/index'],
    ],
];

$payouts = [
    [
        'label' => Yii::_t('payments.menu.reseller-partner-payments'),
        'url' => ['/payments/payments/index'],
    ],
    [
        'label' => Yii::_t('payments.menu.partner_invoices'),
        'url' => ['/payments/users/balance-invoice'],
    ],
    [
        'label' => Yii::_t('payments.menu.wallet-list'),
        'url' => ['/payments/wallet/index'],
    ],
    [
        'label' => Yii::_t('payments.menu.payment-systems-api'),
        'url' => ['/payments/payment-systems-api/list'],
    ],
    [
        'label' => Yii::_t('payments.menu.payment-systems-api'),
        'url' => ['/payments/payment-systems-api/update'],
        'visible' => false
    ],
    [
        'label' => Yii::_t('holds.main.tabs_hold_rules'),
        'url' => ['/holds/partner-hold-rules/index'],
    ],
    [
        'label' => Yii::_t('payments.menu.exchange_course'),
        'url' => ['/payments/payments/course'],
    ],

];

$faq = [
    [
        'label' => Yii::_t('pages.faq.faq'),
        'url' => ['/pages/faq/index'],
    ],
    [
        'label' => Yii::_t('pages.faq.categories'),
        'url' => ['/pages/faq-categories/index'],
    ],
];

$landings = [
    [
        'label' => Yii::_t('promo.landings.view'),
        'url' => ['/promo/landings/view', 'id' => Yii::$app->request->get('id')],
    ],
    [
        'label' => Yii::_t('promo.menu.landing_update'),
        'url' => ['/promo/landings/update', 'id' => Yii::$app->request->get('id')],
    ],
];

$arbitrarySources = [
    [
        'label' => Yii::_t('promo.arbitrary_sources.view'),
        'url' => ['/promo/arbitrary-sources/view', 'id' => Yii::$app->request->get('id')],
    ],
    [
        'label' => Yii::_t('promo.menu.arbitrary_source_update'),
        'url' => ['/promo/arbitrary-sources/update', 'id' => Yii::$app->request->get('id')],
    ],
];

$smartLinks = [
    [
        'label' => Yii::_t('promo.smart_links.view'),
        'url' => ['/promo/smart-links/view', 'id' => Yii::$app->request->get('id')],
    ],
    [
        'label' => Yii::_t('promo.smart_links.update'),
        'url' => ['/promo/smart-links/update', 'id' => Yii::$app->request->get('id')],
    ],
];

$webmasterSources = [
    [
        'label' => Yii::_t('promo.webmaster_sources.view'),
        'url' => ['/promo/webmaster-sources/view', 'id' => Yii::$app->request->get('id')],
    ],
    [
        'label' => Yii::_t('promo.menu.webmaster_source_update'),
        'url' => ['/promo/webmaster-sources/update', 'id' => Yii::$app->request->get('id')],
    ],
];

$notifications = [
    [
        'label' => Yii::_t('notifications.menu.delivery'),
        'url' => ['/notifications/delivery/index'],
    ],
    [
        'label' => Yii::_t('notifications.main.browser_notifications'),
        'url' => ['/notifications/notifications/browser'],
    ],
    [
        'label' => Yii::_t('notifications.main.email_notifications'),
        'url' => ['/notifications/notifications/email'],
    ],
    [
        'label' => Yii::_t('notifications.main.telegram_notifications'),
        'url' => ['/notifications/notifications/telegram'],
    ],
    [
        'label' => Yii::_t('notifications.main.push_notifications'),
        'url' => ['/notifications/notifications/push'],
    ],
];

$viewEditPayouts = [
    [
        'label' => Yii::_t('payments.payments.view'),
        'url' => ['/payments/payments/view', 'id' => Yii::$app->request->get('id')],
    ],
    [
        'label' => Yii::_t('payments.menu.update_payment'),
        'url' => ['/payments/payments/update', 'id' => Yii::$app->request->get('id')],
    ],
];

$notificationSettings = [
    [
        'label' => Yii::_t('notifications.menu.notifications'),
        'url' => ['/notifications/settings/list'],
    ],
    [
        'label' => Yii::_t('notifications.menu.my-notifications'),
        'url' => ['/notifications/settings/my-notifications'],
    ]
];

$companiesTabs = [
    [
        'label' => Yii::_t('payments.menu.partner-companies'),
        'url' => ['/payments/partner-companies/index'],
    ],
    [
        'label' => Yii::_t('payments.menu.companies'),
        'url' => ['/payments/companies/index'],
    ]
];

$analyticsTabs = [
    [
        'label' => Yii::_t('statistic.analytics.common'),
        'url' => ['/statistic/analytics/index'],
    ],
    [
        'label' => Yii::_t('statistic.analytics.analytics-ltv'),
        'url' => ['/statistic/analytics/ltv'],
    ],
    [
        'label' => Yii::_t('statistic.analytics.analytics-by-date'),
        'url' => ['/statistic/analytics/by-date'],
    ],
];

return [
    'users/users/list' => [
        'tabs' => $userList,
        'parent' => 'users/users/list',
    ],
    'promo/landings/payouts' => [
        'tabs' => $userList,
        'parent' => 'users/users/list',
    ],
    'users/users/view' => [
        'tabs' => $userTabs,
        'parent' => 'users/users/list',
    ],
    'users/users-invitations/index' => [
        'tabs' => $userInvitations,
        'parent' => 'users/users-invitations/index',
    ],
    'users/users/update' => [
        'tabs' => $userTabs,
        'parent' => 'users/users/list',
    ],
    'payments/users/view' => [
        'tabs' => $userTabs,
        'parent' => 'users/users/list',
    ],
    'promo/partner-programs/index' => [
        'tabs' => $partnerPrograms,
        'parent' => 'promo/partner-programs/index',
    ],
    'promo/personal-profits/index' => [
        'tabs' => $partnerPrograms,
        'parent' => 'promo/partner-programs/index',
    ],
    'payments/reseller-checkout/index' => [
        'tabs' => $resellerPayout,
        'parent' => 'statistic/reseller-profit/index',
    ],
    'statistic/reseller-profit/index' => [
        'tabs' => $resellerPayout,
        'parent' => 'statistic/reseller-profit/index',
    ],
    'payments/payments/reseller-settings' => [
        'tabs' => $resellerPayout,
        'parent' => 'statistic/reseller-profit/index',
    ],
    'payments/reseller-invoices/index' => [
        'tabs' => $resellerPayout,
        'parent' => 'statistic/reseller-profit/index',
    ],
    'credits/credits/index' => [
        'tabs' => $resellerPayout,
        'parent' => 'statistic/reseller-profit/index',
    ],
    'credits/credits/view' => [
        'tabs' => $resellerPayout,
        'parent' => 'statistic/reseller-profit/index',
    ],
    'loyalty/bonuses/index' => [
        'tabs' => $resellerPayout,
        'parent' => 'statistic/reseller-profit/index',
    ],
    'statistic/reseller-hold-rules/index' => [
        'tabs' => $resellerPayout,
        'parent' => 'statistic/reseller-profit/index',
    ],
    'payments/payments/index' => [
        'tabs' => $payouts,
        'parent' => 'payments/payments/index',
    ],
    'payments/payment-systems-api/list' => [
        'tabs' => $payouts,
        'parent' => 'payments/payments/index',
    ],
    'payments/wallet/index' => [
        'tabs' => $payouts,
        'parent' => 'payments/payments/index',
    ],
    'payments/partner-companies/index' => [
        'tabs' => $companiesTabs,
        'parent' => 'payments/companies/index',
    ],
    'payments/companies/index' => [
        'tabs' => $companiesTabs,
        'parent' => 'payments/companies/index',
    ],
    'holds/partner-hold-rules/index' => [
        'tabs' => $payouts,
        'parent' => 'payments/payments/index',
    ],
    'holds/partner-hold-rules/update' => [
        'tabs' => $payouts,
        'parent' => 'payments/payments/index',
    ],
    'payments/payments/course' => [
        'tabs' => $payouts,
        'parent' => 'payments/payments/index',
    ],
    'payments/payments/view' => [
        'tabs' => $viewEditPayouts,
        'parent' => 'payments/payments/index',
    ],
    'payments/payments/update' => [
        'tabs' => $viewEditPayouts,
        'parent' => 'payments/payments/index',
    ],
    'payments/users/balance-invoice' => [
        'tabs' => $payouts,
        'parent' => 'payments/payments/index',
    ],
    'pages/faq/index' => [
        'tabs' => $faq,
        'parent' => 'pages/faq/index',
    ],
    'pages/faq-categories/index' => [
        'tabs' => $faq,
        'parent' => 'pages/faq/index',
    ],
    'support/tickets/view' => [
        'parent' => 'support/tickets/list'
    ],
    'promo/partner-programs/update' => [
        'parent' => 'promo/partner-programs/index',
    ],
    'promo/landing-sets/update' => [
        'parent' => 'promo/landing-sets/index',
    ],
    'promo/banners/index' => [
        'tabs' => $banners,
        'parent' => 'promo/banners/index',
    ],
    'promo/banner-templates/index' => [
        'tabs' => $banners,
        'parent' => 'promo/banners/index',
    ],
    'promo/banners/update' => [
        'parent' => 'promo/banners/index',
    ],
    'promo/banners/create' => [
        'parent' => 'promo/banners/index',
    ],
    'payments/payments/create' => [
        'parent' => 'payments/payments/index',
    ],
    'pages/pages/create' => [
        'parent' => 'pages/pages/index',
    ],
    'pages/categories/create' => [
        'parent' => 'pages/pages/index',
    ],
    'pages/categories/index' => [
        'parent' => 'pages/pages/index',
    ],
    'pages/pages/update' => [
        'parent' => 'pages/pages/index',
    ],
    'notifications/settings/view' => [
        'parent' => 'notifications/settings/list',
    ],
    'notifications/settings/update' => [
        'parent' => 'notifications/settings/list',
    ],
    'notifications/settings/my-notifications' => [
        'parent' => 'notifications/settings/list',
        'tabs' => $notificationSettings,
    ],
    'notifications/settings/list' => [
        'parent' => 'notifications/settings/list',
        'tabs' => $notificationSettings,
    ],
    'promo/streams/view' => [
        'parent' => 'promo/streams/index',
    ],
    'promo/landings/view' => [
        'tabs' => $landings,
        'parent' => 'promo/landings/index'
    ],
    'promo/landings/update' => [
        'tabs' => $landings,
        'parent' => 'promo/landings/index'
    ],
    'promo/arbitrary-sources/view' => [
        'tabs' => $arbitrarySources,
        'parent' => 'promo/arbitrary-sources/index'
    ],
    'promo/arbitrary-sources/update' => [
        'tabs' => $arbitrarySources,
        'parent' => 'promo/arbitrary-sources/index'
    ],
    'promo/smart-links/update' => [
        'tabs' => $smartLinks,
        'parent' => 'promo/smart-links/index'
    ],
    'promo/smart-links/view' => [
        'tabs' => $smartLinks,
        'parent' => 'promo/smart-links/index'
    ],
    'promo/webmaster-sources/view' => [
        'tabs' => $webmasterSources,
        'parent' => 'promo/webmaster-sources/index'
    ],
    'promo/webmaster-sources/update' => [
        'tabs' => $webmasterSources,
        'parent' => 'promo/webmaster-sources/index'
    ],
    'promo/landing-categories/view' => [
        'parent' => 'promo/landing-categories/index',
    ],
    'promo/offer-categories/view' => [
        'parent' => 'promo/offer-categories/index',
    ],
    'promo/operators/view' => [
        'parent' => 'promo/operators/index',
    ],
    'promo/domains/view' => [
        'parent' => 'promo/domains/index'
    ],
    'statistic/detail/ik' => [
        'parent' => 'statistic/detail/subscriptions',
    ],
    'statistic/detail/sells' => [
        'parent' => 'statistic/detail/subscriptions',
    ],
    'statistic/detail/complains' => [
        'parent' => 'statistic/detail/subscriptions',
    ],
    'statistic/detail/hit' => [
        'parent' => 'statistic/detail/subscriptions',
    ],
    'statistic/analytics/index' => [
        'parent' => null,
        'tabs' => $analyticsTabs,
    ],
    'statistic/analytics/by-date' => [
        'parent' => 'statistic/analytics/index',
        'tabs' => $analyticsTabs,
    ],
    'statistic/analytics/ltv' => [
        'parent' => 'statistic/analytics/index',
        'tabs' => $analyticsTabs,
    ],
    'promo/banner-templates/create' => [
        'parent' => 'promo/banners/index',
    ],
    'notifications/notifications/browser' => [
        'tabs' => $notifications,
        'parent' => 'notifications/delivery/index',
    ],
    'notifications/notifications/email' => [
        'tabs' => $notifications,
        'parent' => 'notifications/delivery/index',
    ],
    'notifications/notifications/telegram' => [
        'tabs' => $notifications,
        'parent' => 'notifications/delivery/index',
    ],
    'notifications/notifications/push' => [
        'tabs' => $notifications,
        'parent' => 'notifications/delivery/index',
    ],
    'notifications/delivery/index' => [
        'tabs' => $notifications,
        'parent' => 'notifications/delivery/index',
    ],
    'notifications/users-invitations/index' => [
        'tabs' => $userInvitations,
        'parent' => 'users/users-invitations/index',
    ],
    'notifications/users-invitations/sent' => [
        'tabs' => $userInvitations,
        'parent' => 'users/users-invitations/index',
    ],
    'alerts/default/update' => [
        'parent' => 'alerts/default/index',
    ],
    'users/admin/assignment/index' => [
        'parent' => 'users/admin/',
    ],
    'users/admin/role/index' => [
        'parent' => 'users/admin/',
    ],
    'users/admin/permission/index' => [
        'parent' => 'users/admin/',
    ],
    'users/admin/route/index' => [
        'parent' => 'users/admin/',
    ],
    'users/admin/rule/index' => [
        'parent' => 'users/admin/',
    ],
    'users/admin/tree/index' => [
        'parent' => 'users/admin/',
    ],
    'promo/countries/view' => [
        'parent' => 'promo/countries/index',
    ],
    'promo/currencies/view' => [
        'parent' => 'promo/currencies/index',
    ],
    'promo/providers/view' => [
        'parent' => 'promo/providers/index',
    ],
    'payments/users/profit' => [
        'tabs' => $userTabs,
        'parent' => 'users/users/list',
    ],
    'promo/regions/view' => [
        'parent' => 'promo/regions/index',
    ],
    'payments/payment-systems-api/update' => [
        'tabs' => $payouts,
        'parent' => 'payments/payments/index',
    ],
];