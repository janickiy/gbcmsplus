<?php

use mcms\payments\models\UserPayment;
use mcms\payments\Module;

$params = require(__DIR__ . '/params.php');

return [
  'id' => 'payments',
  'class' => 'mcms\payments\Module',
  'name' => 'payments.menu.module',
  'menu' => [
    'icon' => 'fa-lg fa-fw icon-payments',
    'label' => 'payments.menu.module',
    'events' => [
      \mcms\payments\components\events\EarlyPaymentCreated::class,
    ],
    'items' => [
      ['label' => 'payments.menu.payments-all', 'url' => [
        '/payments/payments/index',
        'createdAtFrom' => date('Y-m-d', strtotime('-1month'))
      ]],
      [
        'label' => 'payments.menu.payments-awaiting',
        'events' => [
          \mcms\payments\components\events\EarlyPaymentCreated::class,
        ],
        'url' => [
          '/payments/payments/index',
          'status' => [UserPayment::STATUS_AWAITING, UserPayment::STATUS_DELAYED],
        ]
      ],
      ['label' => 'payments.menu.payments-archive', 'url' => ['/payments/payments/index', 'payedAtTo' => date('Y-m-d', strtotime('-1month'))]],

      [
        'label' => 'payments.menu.reseller-partner-payments',
        'url' => ['/payments/payments/index'],
        'events' => [
          \mcms\payments\components\events\EarlyPaymentCreated::class,
        ],
        'isActionCheck' => 1
      ],
      ['label' => 'payments.menu.reseller-settings', 'url' => ['/payments/payments/reseller-settings'], 'isActionCheck' => 1],
      ['label' => 'payments.menu.reseller-profit', 'url' => ['/payments/reseller-checkout/index']],
      ['label' => 'payments.menu.wallet-list', 'url' => ['/payments/wallet/index']],
      ['label' => 'payments.menu.payment-systems-api', 'url' => ['/payments/payment-systems-api/list']],
    ]
  ],
  'messages' => '@mcms/payments/messages',
  'params' => $params,
  'events' => [
    \mcms\payments\components\events\EarlyPaymentIndividualPercentChanged::class,

    \mcms\payments\components\events\EarlyPaymentCreated::class,
    \mcms\payments\components\events\EarlyPaymentAdminCreated::class,
    \mcms\payments\components\events\RegularPaymentCreated::class,

    \mcms\payments\components\events\PaymentSettingAutopayDisabled::class,
    \mcms\payments\components\events\PaymentSettingAutopayUpdated::class,
    \mcms\payments\components\events\PaymentsExported::class,
    \mcms\payments\components\events\PaymentUpdated::class,
    \mcms\payments\components\events\ReferralIndividualPercentChanged::class,
    \mcms\payments\components\events\UserCurrencyChanged::class,
    \mcms\payments\components\events\UserBalanceInvoiceMulct::class,
    \mcms\payments\components\events\UserBalanceInvoiceCompensation::class,
    \mcms\payments\components\events\EarlyPaymentCreated::class,
    \mcms\payments\components\events\PaymentStatusUpdated::class,
    \mcms\payments\components\events\UserBalanceConvert::class,

  ],
  'apiClasses' => [
    'userSettings' => \mcms\payments\components\api\UserSettings::class,
    'userSettingsData' => \mcms\payments\components\api\UserSettingsData::class,
    'partnerSettings' => \mcms\payments\components\api\PartnerSettings::class,
    'buyDomain' => \mcms\payments\components\api\BuyDomain::class,
    'setUserCurrency' => \mcms\payments\components\api\SetUserCurrency::class,
    'getUserCurrency' => \mcms\payments\components\api\GetUserCurrency::class,
    'userInvoices' => \mcms\payments\components\api\UserInvoices::class,
    'userPayments' => \mcms\payments\components\api\UserPayments::class,
    'userBalance' => \mcms\payments\components\api\UserBalance::class,
    'requestEarlyPayment' => \mcms\payments\components\api\RequestEarlyPayment::class,
    'userPaymentsSummary' => \mcms\payments\components\api\UserPaymentsSummary::class,
    'getGroupedBalanceProfitTypes' => \mcms\payments\components\api\GetGroupedBalanceProfitTypes::class,
    'referralsGroupedBalance' => \mcms\payments\components\api\ReferralsGroupedBalance::class,
    'exchangerCourses' => \mcms\payments\components\api\ExchangerCourses::class,
    'exchangerPartnerCourses' => \mcms\payments\components\api\ExchangerPartnerCourses::class,
    'walletTypes' => \mcms\payments\components\api\WalletTypes::class,
    'badgeCounters' => \mcms\payments\components\api\BadgeCounters::class,
    'userWallet' => \mcms\payments\components\api\UserWallet::class,
    'userWalletSave' => \mcms\payments\components\api\UserWalletSave::class,
    'userBalanceTransfer' => \mcms\payments\components\api\UserBalanceTransfer::class,
    'userBalanceConvertHandler' => \mcms\payments\components\api\UserBalanceConvertHandler::class,
  ],
  'fixtures' => require(__DIR__ . '/fixtures.php')
];
