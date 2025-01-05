set foreign_key_checks=0;
DELETE FROM search_subscriptions;
DELETE FROM subscription_rebills;
DELETE FROM sold_subscriptions;
DELETE FROM buyout_conditions;
set foreign_key_checks=1;

INSERT INTO buyout_conditions (name, operator_id, user_id, landing_id, type, buyout_minutes, is_buyout_only_after_1st_rebill, is_buyout_only_unique_phone, created_by, created_at, updated_at) VALUES ('test condition', 1, 0, 0, 2, null, 1, null, 1, 111, 111);

# Подписка с оператором is_buyout_only_after_1st_rebill = 1 и без ребиллов не выкупится
INSERT INTO search_subscriptions (hit_id, time_on, time_off, time_rebill, last_time, count_rebills, sum_real_profit_rub, sum_real_profit_eur, sum_real_profit_usd, sum_reseller_profit_rub, sum_reseller_profit_eur, sum_reseller_profit_usd, sum_profit_rub, sum_profit_eur, sum_profit_usd, phone, is_cpa, currency_id, landing_id, source_id, operator_id, platform_id, landing_pay_type_id, provider_id, country_id, stream_id, user_id, is_updated)
VALUES (1000, UNIX_TIMESTAMP() - 8 * 60, 0, UNIX_TIMESTAMP(), 1470876956, 1, 0.00, 0.00, 0.08, 0.00, 0.00, 0.08, 4.09, 0.06, 0.07,
        '', 1, 2, 1, 1, 1, 101, 3, 0, 5, 84, 37, 1);

# Подписка с оператором is_buyout_only_after_1st_rebill = 1 и 1 ребиллом выкупится
INSERT INTO search_subscriptions (hit_id, time_on, time_off, time_rebill, last_time, count_rebills, sum_real_profit_rub, sum_real_profit_eur, sum_real_profit_usd, sum_reseller_profit_rub, sum_reseller_profit_eur, sum_reseller_profit_usd, sum_profit_rub, sum_profit_eur, sum_profit_usd, phone, is_cpa, currency_id, landing_id, source_id, operator_id, platform_id, landing_pay_type_id, provider_id, country_id, stream_id, user_id, is_updated)
VALUES (1001, UNIX_TIMESTAMP() - 8 * 60, 0, UNIX_TIMESTAMP(), 1470876956, 1, 0.00, 0.00, 0.08, 0.00, 0.00, 0.08, 4.09, 0.06, 0.07,
        '', 1, 2, 1, 1, 1, 101, 3, 0, 5, 84, 37, 1);

INSERT INTO subscription_rebills (id, hit_id, trans_id, time, date, hour, default_profit, default_profit_currency, currency_id, real_profit_rub, real_profit_eur, real_profit_usd, reseller_profit_rub, reseller_profit_eur, reseller_profit_usd, profit_rub, profit_eur, profit_usd, landing_id, source_id, operator_id, platform_id, landing_pay_type_id, is_cpa, provider_id) VALUES (1, 1001, '169882286', 1471006282, '2016-08-12', 15, 1.70, 3, 3, 0.00, 1.70, 0.00, 0.00, 1.70, 0.00, 91.23, 1.53, 1.51, 1, 1, 1, 101, 4, 1, 1);

# Подписка с оператором is_buyout_only_after_1st_rebill = null и без ребиллов выкупится
INSERT INTO search_subscriptions (hit_id, time_on, time_off, time_rebill, last_time, count_rebills, sum_real_profit_rub, sum_real_profit_eur, sum_real_profit_usd, sum_reseller_profit_rub, sum_reseller_profit_eur, sum_reseller_profit_usd, sum_profit_rub, sum_profit_eur, sum_profit_usd, phone, is_cpa, currency_id, landing_id, source_id, operator_id, platform_id, landing_pay_type_id, provider_id, country_id, stream_id, user_id, is_updated)
VALUES (1002, UNIX_TIMESTAMP() - 8 * 60, 0, UNIX_TIMESTAMP(), 1470876956, 1, 0.00, 0.00, 0.08, 0.00, 0.00, 0.08, 4.09, 0.06, 0.07,
        '', 1, 2, 2, 1, 2, 101, 3, 0, 5, 84, 37, 1);