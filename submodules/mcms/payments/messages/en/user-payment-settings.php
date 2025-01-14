<?php
return [
  'attribute-user_id' => 'User ID',
  'attribute-is_disabled' => 'Disable payments',
  'attribute-referral_percent' => 'Referral percent',
  'attribute-visible_referral_percent' => 'Visible referral percent',
  'attribute-is_auto_payout_disabled' => 'Deny payout (without administrator)',
  'attribute-early_payment_percent' => 'Early payment percent',
  'early_payment_percent_hint' => 'Only applies to payments created from partner program.
    If the field is empty, it will use the value from the module configuration of the payment',
  'attribute-wallet_type' => 'Payment method',
  'attribute-is_hold_autopay_enabled' => 'Disable automatic payment runs status change (even with enough subscriptions gathered)',
  'hint-is_auto_payments' => 'Automatic payment runs are available only to publishers with more than {subscriptionsCount} subscriptions',
  'replacement-attribute-subscription_count' => 'Subscriptions amount',
  'cant_get_user_currency_error' => 'Can\'t get user currency',
  'message-in-awaiting-changing-not-affect' => 'The user payments are awaiting. Changing the settings will not affect the payout.',
  'error-invalid-wallet-type' => 'Invalid wallet type',
  'pay_terms_weekly_net5' => 'Weekly Net5',
  'pay_terms_bi_monthly_net15' => 'Bi-Monthly Net15',
  'pay_terms_bi_monthly_net30' => 'Bi-Monthly Net30',
  'pay_terms_monthly_net7' => 'Monthly Net7',
  'pay_terms_monthly_net15' => 'Monthly Net15',
  'pay_terms_monthly_net30' => 'Monthly Net30',
  'attribute-pay_terms' => 'Pay terms',
  'attribute-is_wallets_manage_disabled' => 'Disable manage wallets',
];