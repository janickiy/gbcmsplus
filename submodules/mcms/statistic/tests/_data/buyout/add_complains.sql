set foreign_key_checks=0;
DELETE FROM search_subscriptions;
DELETE FROM subscriptions;
DELETE FROM subscription_offs;
DELETE FROM subscription_rebills;
DELETE FROM sold_subscriptions;
DELETE FROM complains;
DELETE FROM buyout_conditions;
set foreign_key_checks=1;

INSERT INTO buyout_conditions (name, operator_id, user_id, landing_id, type, buyout_minutes, is_buyout_only_after_1st_rebill, is_buyout_only_unique_phone, created_by, created_at, updated_at) VALUES ('test condition', 1, 0, 0, 3, null, null, 1, 1, 111, 111);

# Моментальная отписка
INSERT INTO search_subscriptions (hit_id, time_on, time_off, time_rebill, last_time, count_rebills, sum_real_profit_rub, sum_real_profit_eur, sum_real_profit_usd, sum_reseller_profit_rub, sum_reseller_profit_eur, sum_reseller_profit_usd, sum_profit_rub, sum_profit_eur, sum_profit_usd, phone, is_cpa, currency_id, landing_id, source_id, operator_id, platform_id, landing_pay_type_id, provider_id, country_id, stream_id, user_id, is_updated)
VALUES (1000, UNIX_TIMESTAMP() - 8 * 60 * 60, UNIX_TIMESTAMP() - 8 * 60 * 60, UNIX_TIMESTAMP(), 1470876956, 1, 0.00, 0.00, 0.08, 0.00, 0.00, 0.08, 4.09, 0.06, 0.07,
        '111111', 1, 1, 1, 1, 1, 101, 3, 0, 5, 84, 101, 1);


INSERT INTO `subscription_offs` (`hit_id`, `trans_id`, `time`, `date`, `hour`, `landing_id`, `source_id`, `operator_id`, `platform_id`, `landing_pay_type_id`, `is_cpa`, `currency_id`, `provider_id`, `is_fake`) VALUES ( 1000, 'f74373f9-96dc-4619-9b2b-aae12e537d50', UNIX_TIMESTAMP() - 8 * 60 * 60, '2018-01-01', 5, 8, 1, 78, 105, 1, 0, 3, 0, 0);

INSERT INTO subscriptions (hit_id, trans_id, time, date, hour, landing_id, source_id, operator_id, platform_id, landing_pay_type_id, phone, is_cpa, currency_id, provider_id, is_fake) VALUES (1000, '169884408', UNIX_TIMESTAMP() - 8 * 60 * 60, '2018-01-01', 15, 1166, 1, 29, 101, 4, '111111', 1, 0, 1, 0);


INSERT INTO subscription_rebills (hit_id, trans_id, time, date, hour, default_profit, default_profit_currency, currency_id, real_profit_rub, real_profit_eur, real_profit_usd, reseller_profit_rub, reseller_profit_eur, reseller_profit_usd, profit_rub, profit_eur, profit_usd, landing_id, source_id, operator_id, platform_id, landing_pay_type_id, is_cpa, provider_id) VALUES (1000, '169882286', UNIX_TIMESTAMP() - 8 * 60 * 60, '2018-01-01', 15, 1.70, 3, 3, 0.00, 1.70, 0.00, 0.00, 1.70, 0.00, 91.23, 1.53, 1.51, 1, 1, 1, 101, 4, 1, 1);

# Отписка 24
INSERT INTO search_subscriptions (hit_id, time_on, time_off, time_rebill, last_time, count_rebills, sum_real_profit_rub, sum_real_profit_eur, sum_real_profit_usd, sum_reseller_profit_rub, sum_reseller_profit_eur, sum_reseller_profit_usd, sum_profit_rub, sum_profit_eur, sum_profit_usd, phone, is_cpa, currency_id, landing_id, source_id, operator_id, platform_id, landing_pay_type_id, provider_id, country_id, stream_id, user_id, is_updated)
VALUES (1001, UNIX_TIMESTAMP() - 8 * 60 * 60, UNIX_TIMESTAMP() - 8 * 60 * 60, UNIX_TIMESTAMP(), 1470876956, 1, 0.00, 0.00, 0.08, 0.00, 0.00, 0.08, 4.09, 0.06, 0.07,
                                                                                                                                             '222222', 1, 1, 1, 1, 1, 101, 3, 0, 5, 84, 101, 1);


INSERT INTO `subscription_offs` (`hit_id`, `trans_id`, `time`, `date`, `hour`, `landing_id`, `source_id`, `operator_id`, `platform_id`, `landing_pay_type_id`, `is_cpa`, `currency_id`, `provider_id`, `is_fake`) VALUES ( 1001, 'f74373f9-96dc-4619-9b2b-aae12e537d50', UNIX_TIMESTAMP() + 22 * 60 * 60, '2018-01-01', 5, 8, 1, 78, 105, 1, 0, 3, 0, 0);

INSERT INTO subscriptions (hit_id, trans_id, time, date, hour, landing_id, source_id, operator_id, platform_id, landing_pay_type_id, phone, is_cpa, currency_id, provider_id, is_fake) VALUES (1001, '169884408', UNIX_TIMESTAMP(), '2018-01-01', 15, 1166, 1, 29, 101, 4, '222222', 1, 0, 1, 0);


INSERT INTO subscription_rebills (hit_id, trans_id, time, date, hour, default_profit, default_profit_currency, currency_id, real_profit_rub, real_profit_eur, real_profit_usd, reseller_profit_rub, reseller_profit_eur, reseller_profit_usd, profit_rub, profit_eur, profit_usd, landing_id, source_id, operator_id, platform_id, landing_pay_type_id, is_cpa, provider_id) VALUES (1001, '169882287', UNIX_TIMESTAMP(), '2018-01-01', 15, 1.70, 3, 3, 0.00, 1.70, 0.00, 0.00, 1.70, 0.00, 91.23, 1.53, 1.51, 1, 1, 1, 101, 4, 1, 1);
