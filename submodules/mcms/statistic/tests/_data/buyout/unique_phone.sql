SET foreign_key_checks=0;
DELETE FROM search_subscriptions;
DELETE FROM subscriptions;
DELETE FROM sold_subscriptions;
DELETE FROM subscription_offs;
DELETE FROM subscription_rebills;
DELETE FROM buyout_conditions;
SET foreign_key_checks=1;

INSERT INTO buyout_conditions (name, operator_id, user_id, landing_id, type, buyout_minutes, is_buyout_only_after_1st_rebill, is_buyout_only_unique_phone, created_by, created_at, updated_at) VALUES ('test condition', 1, 0, 0, 3, null, null, 1, 1, 111, 111);

# Подписка с оператором is_buyout_only_unique_phone = 1 и без дублей телефона выкупится, но только одна!

INSERT INTO search_subscriptions (hit_id, time_on, time_off, time_rebill, last_time, count_rebills, sum_real_profit_rub, sum_real_profit_eur, sum_real_profit_usd, sum_reseller_profit_rub, sum_reseller_profit_eur, sum_reseller_profit_usd, sum_profit_rub, sum_profit_eur, sum_profit_usd, phone, is_cpa, currency_id, landing_id, source_id, operator_id, platform_id, landing_pay_type_id, provider_id, country_id, stream_id, user_id, is_updated)
VALUES (1000, UNIX_TIMESTAMP() - 8 * 60 * 60, 0, UNIX_TIMESTAMP(), 1470876956, 1, 0.00, 0.00, 0.08, 0.00, 0.00, 0.08, 4.09, 0.06, 0.07,
                                                                                                                '111111', 1, 1, 1, 1, 1, 101, 3, 0, 5, 84, 101, 1);

INSERT INTO subscriptions (hit_id, trans_id, time, date, hour, landing_id, source_id, operator_id, platform_id, landing_pay_type_id, phone, is_cpa, currency_id, provider_id, is_fake) VALUES (1000, '169884408', UNIX_TIMESTAMP() - 8 * 60 * 60, '2016-08-12', 15, 1166, 1, 29, 101, 4, '111111', 1, 0, 1, 0);

INSERT INTO search_subscriptions (hit_id, time_on, time_off, time_rebill, last_time, count_rebills, sum_real_profit_rub, sum_real_profit_eur, sum_real_profit_usd, sum_reseller_profit_rub, sum_reseller_profit_eur, sum_reseller_profit_usd, sum_profit_rub, sum_profit_eur, sum_profit_usd, phone, is_cpa, currency_id, landing_id, source_id, operator_id, platform_id, landing_pay_type_id, provider_id, country_id, stream_id, user_id, is_updated)
VALUES (8888, UNIX_TIMESTAMP() - 25 * 60 * 60, 0, UNIX_TIMESTAMP(), 1470876956, 1, 0.00, 0.00, 0.08, 0.00, 0.00, 0.08, 4.09, 0.06, 0.07,
                                                                                                                 '111111', 1, 1, 1, 1, 1, 101, 3, 0, 5, 84, 101, 1);

INSERT INTO subscriptions (hit_id, trans_id, time, date, hour, landing_id, source_id, operator_id, platform_id, landing_pay_type_id, phone, is_cpa, currency_id, provider_id, is_fake) VALUES (8888, '169884408', UNIX_TIMESTAMP() - 25 * 60 * 60, '2016-08-12', 15, 1166, 1, 29, 101, 4, '111111', 1, 0, 1, 0);

INSERT INTO search_subscriptions (hit_id, time_on, time_off, time_rebill, last_time, count_rebills, sum_real_profit_rub, sum_real_profit_eur, sum_real_profit_usd, sum_reseller_profit_rub, sum_reseller_profit_eur, sum_reseller_profit_usd, sum_profit_rub, sum_profit_eur, sum_profit_usd, phone, is_cpa, currency_id, landing_id, source_id, operator_id, platform_id, landing_pay_type_id, provider_id, country_id, stream_id, user_id, is_updated)
VALUES (9999, UNIX_TIMESTAMP() - 8 * 60 * 60, 0, UNIX_TIMESTAMP(), 1470876956, 1, 0.00, 0.00, 0.08, 0.00, 0.00, 0.08, 4.09, 0.06, 0.07,
                                                                                                                '111111', 1, 1, 1, 1, 1, 101, 3, 0, 5, 84, 101, 1);

INSERT INTO subscriptions (hit_id, trans_id, time, date, hour, landing_id, source_id, operator_id, platform_id, landing_pay_type_id, phone, is_cpa, currency_id, provider_id, is_fake) VALUES (9999, '169884408', UNIX_TIMESTAMP() - 8 * 60 * 60, '2016-08-12', 15, 1166, 1, 29, 101, 4, '111111', 1, 0, 1, 0);










# Подписка с оператором is_buyout_only_unique_phone = 1 и с дублем телефона не выкупится
INSERT INTO search_subscriptions (hit_id, time_on, time_off, time_rebill, last_time, count_rebills, sum_real_profit_rub, sum_real_profit_eur, sum_real_profit_usd, sum_reseller_profit_rub, sum_reseller_profit_eur, sum_reseller_profit_usd, sum_profit_rub, sum_profit_eur, sum_profit_usd, phone, is_cpa, currency_id, landing_id, source_id, operator_id, platform_id, landing_pay_type_id, provider_id, country_id, stream_id, user_id, is_updated)
VALUES (1001, UNIX_TIMESTAMP() - 8 * 60, 0, UNIX_TIMESTAMP(), 1470876956, 1, 0.00, 0.00, 0.08, 0.00, 0.00, 0.08, 4.09, 0.06, 0.07,
                                                                                                           '111122', 1, 1, 1, 1, 1, 101, 3, 0, 5, 84, 101, 1);

INSERT INTO subscriptions (id, hit_id, trans_id, time, date, hour, landing_id, source_id, operator_id, platform_id, landing_pay_type_id, phone, is_cpa, currency_id, provider_id, is_fake) VALUES (1, 1001, '169884408', UNIX_TIMESTAMP()- 8 * 60, '2016-08-12', 15, 1166, 1, 29, 101, 4, '111122', 1, 1, 1, 0);


# Подписка с оператором is_buyout_only_unique_phone = null и с дублем телефона выкупится
INSERT INTO search_subscriptions (hit_id, time_on, time_off, time_rebill, last_time, count_rebills, sum_real_profit_rub, sum_real_profit_eur, sum_real_profit_usd, sum_reseller_profit_rub, sum_reseller_profit_eur, sum_reseller_profit_usd, sum_profit_rub, sum_profit_eur, sum_profit_usd, phone, is_cpa, currency_id, landing_id, source_id, operator_id, platform_id, landing_pay_type_id, provider_id, country_id, stream_id, user_id, is_updated)
VALUES (1002, UNIX_TIMESTAMP() - 8 * 60, 0, UNIX_TIMESTAMP(), 1470876956, 1, 0.00, 0.00, 0.08, 0.00, 0.00, 0.08, 4.09, 0.06, 0.07,
                                                                                                           '111122', 1, 1, 2, 1, 2, 101, 3, 0, 5, 84, 101, 1);

INSERT INTO subscriptions (id, hit_id, trans_id, time, date, hour, landing_id, source_id, operator_id, platform_id, landing_pay_type_id, phone, is_cpa, currency_id, provider_id, is_fake) VALUES (2, 1002, '169884408', UNIX_TIMESTAMP()- 8 * 60, '2016-08-12', 15, 1166, 1, 29, 101, 4, '111122', 1, 1, 1, 0);


INSERT INTO subscriptions (hit_id, trans_id, time, date, hour, landing_id, source_id, operator_id, platform_id, landing_pay_type_id, phone, is_cpa, currency_id, provider_id, is_fake) VALUES (12332, '169884408', UNIX_TIMESTAMP(), '2016-08-12', 15, 1166, 1, 29, 101, 4, '111122', 1, 1, 1, 0);

INSERT INTO sold_subscriptions (hit_id, time, date, hour, landing_id, source_id, operator_id, platform_id, landing_pay_type_id, currency_id, provider_id) VALUES (12332, UNIX_TIMESTAMP(), '2016-08-12', 15, 1166, 1, 29, 101, 4, 1, 1);





#Выкупленная подписка по номеру 55555555
INSERT INTO search_subscriptions (hit_id, time_on, time_off, time_rebill, last_time, count_rebills, sum_real_profit_rub, sum_real_profit_eur, sum_real_profit_usd, sum_reseller_profit_rub, sum_reseller_profit_eur, sum_reseller_profit_usd, sum_profit_rub, sum_profit_eur, sum_profit_usd, phone, is_cpa, currency_id, landing_id, source_id, operator_id, platform_id, landing_pay_type_id, provider_id, country_id, stream_id, user_id, is_updated)
VALUES (1005, UNIX_TIMESTAMP() - 100 * 60 * 60, 0, UNIX_TIMESTAMP(), 1470876956, 1, 0.00, 0.00, 0.08, 0.00, 0.00, 0.08, 4.09, 0.06, 0.07,
                                                                                                                  '55555555', 1, 1, 1, 1, 1, 101, 3, 0, 5, 84, 101, 1);

INSERT INTO sold_subscriptions (hit_id, time, date, hour, landing_id, source_id, operator_id, platform_id, landing_pay_type_id, currency_id, provider_id) VALUES (1005, UNIX_TIMESTAMP() - 100 * 60 * 60, '2016-08-12', 15, 1166, 1, 29, 101, 4, 1, 1);

INSERT INTO subscriptions (hit_id, trans_id, time, date, hour, landing_id, source_id, operator_id, platform_id, landing_pay_type_id, phone, is_cpa, currency_id, provider_id, is_fake) VALUES (1005, '169884408', UNIX_TIMESTAMP() - 100 * 60 * 60, '2016-08-12', 15, 1166, 1, 29, 101, 4, '55555555', 1, 0, 1, 0);



#Подписка по номеру 55555555 должна выкупиться, т.к. выкуп предущий был больше 24 часов
INSERT INTO search_subscriptions (hit_id, time_on, time_off, time_rebill, last_time, count_rebills, sum_real_profit_rub, sum_real_profit_eur, sum_real_profit_usd, sum_reseller_profit_rub, sum_reseller_profit_eur, sum_reseller_profit_usd, sum_profit_rub, sum_profit_eur, sum_profit_usd, phone, is_cpa, currency_id, landing_id, source_id, operator_id, platform_id, landing_pay_type_id, provider_id, country_id, stream_id, user_id, is_updated)
VALUES (1006, UNIX_TIMESTAMP() - 8 * 60 * 60, 0, UNIX_TIMESTAMP(), 1470876956, 1, 0.00, 0.00, 0.08, 0.00, 0.00, 0.08, 4.09, 0.06, 0.07,
                                                                                                                '55555555', 1, 1, 1, 1, 1, 101, 3, 0, 5, 84, 101, 1);

INSERT INTO subscriptions (hit_id, trans_id, time, date, hour, landing_id, source_id, operator_id, platform_id, landing_pay_type_id, phone, is_cpa, currency_id, provider_id, is_fake) VALUES (1006, '169884408', UNIX_TIMESTAMP() - 8 * 60 * 60, '2016-08-12', 15, 1166, 1, 29, 101, 4, '55555555', 1, 0, 1, 0);