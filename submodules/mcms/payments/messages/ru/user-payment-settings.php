<?php
return [
  'attribute-user_id' => 'ID Пользователя',
  'attribute-is_auto_payments' => 'Автовыплаты',
  'attribute-is_disabled' => 'Запретить выплаты (партнер не может заказывать выплаты)',
  'attribute-is_auto_payout_disabled' => 'Запретить автовыплаты (выплаты без участия администратора)',
  'attribute-referral_percent' => 'Реферальный процент',
  'attribute-visible_referral_percent' => 'Видимый реферальный процент',
  'attribute-early_payment_percent' => 'Процент за досрочную выплату',
  'early_payment_percent_hint' => 'Применяется только для выплат созданных из партнерской программы. 
  Если поле пустое, будет использовано значение из настроек модуля выплат',
  'attribute-is_hold_autopay_enabled' => 'Запретить изменение автовыплат (автовыплаты не выкл/вкл даже если у партнера не достаточно подписок)',
  'hint-is_auto_payments' => 'Автовыплаты доступны только партнёрам, у которых более {subscriptionsCount} подписок',
  'replacement-attribute-subscription_count' => 'Количество подписок',
  'cant_get_user_currency_error' => 'Не удалось получить валюту пользователя',
  'message-in-awaiting-changing-not-affect' => 'Пользователь имеет выплаты в ожидании. Изменение настроек не повлияет на выплаты.',
  'error-invalid-wallet-type' => 'Указан неверная платежная система',
  'pay_terms_weekly_net5' => 'Weekly Net5',
  'pay_terms_bi_monthly_net15' => 'Bi-Monthly Net15',
  'pay_terms_bi_monthly_net30' => 'Bi-Monthly Net30',
  'pay_terms_monthly_net7' => 'Monthly Net7',
  'pay_terms_monthly_net15' => 'Monthly Net15',
  'pay_terms_monthly_net30' => 'Monthly Net30',
  'attribute-pay_terms' => 'Pay terms',
  'attribute-is_wallets_manage_disabled' => 'Отключить управление кошельками',
];