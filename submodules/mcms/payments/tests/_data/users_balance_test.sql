TRUNCATE TABLE user_payment_settings;
TRUNCATE TABLE subscriptions;
TRUNCATE TABLE subscription_rebills;
TRUNCATE TABLE subscriptions_day_hour_group;
TRUNCATE TABLE user_balances_grouped_by_day;
TRUNCATE TABLE currency_log;
INSERT INTO `user_payment_settings` (`user_id`, `referral_percent`, `early_payment_percent_old`, `currency`) VALUES (101, 0, 0, 'rub');
INSERT INTO `subscriptions` (`hit_id`, `trans_id`, `date`, `time`, `hour`, `source_id`, `landing_id`, `operator_id`, `platform_id`, `landing_pay_type_id`, `is_cpa`, `currency_id`, `provider_id`, `phone`) VALUES (1, 1, '2017-10-01', 1506819600, 1, 3, 1, 1, 0, 0, 1, 1, 1, '111'), (2, 2, '2017-10-01', 1506823200, 2, 3, 1, 1, 0, 0, 1, 1, 1, '222'), (3, 3, '2017-10-01', 1506826800, 3, 3, 1, 1, 0, 0, 1, 1, 1, '333'), (4, 4, '2017-10-01', 1506830400, 4, 3, 1, 1, 0, 0, 1, 1, 1, '444');
INSERT INTO `subscription_rebills` (`hit_id`, `trans_id`, `landing_id`, `platform_id`, `landing_pay_type_id`, `source_id`, `hour`, `date`, `time`, `default_profit`, `default_profit_currency`, `currency_id`, `real_profit_rub`, `real_profit_usd`, `real_profit_eur`, `reseller_profit_rub`, `reseller_profit_usd`, `reseller_profit_eur`, `profit_rub`, `profit_usd`, `profit_eur`) VALUES (1, 1, 1, 1, 1, 3, 1, '2017-10-01', 1506819600, 80, 1, 1, 80, 1.2, 1, 160, 2.4, 2, 240, 3.6, 3), (2, 2, 1, 1, 1, 3, 2, '2017-10-01', 1506823200, 80, 1, 1, 80, 1.2, 1, 160, 2.4, 2, 240, 3.6, 3), (3, 3, 1, 1, 1, 3, 3, '2017-10-01', 1506826800, 80, 1, 1, 80, 1.2, 1, 160, 2.4, 2, 240, 3.6, 3), (4, 4, 1, 1, 1, 3, 4, '2017-10-01', 1506830400, 80, 1, 1, 80, 1.2, 1, 160, 2.4, 2, 240, 3.6, 3);