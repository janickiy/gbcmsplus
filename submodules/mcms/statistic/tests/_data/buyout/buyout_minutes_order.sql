set foreign_key_checks=0;
DELETE FROM search_subscriptions;
DELETE FROM buyout_conditions;
set foreign_key_checks=1;

# Правила в порядке приоритета. Первому задаю 20 минут, что не позволяет выкупиться первой подписке (не смотря на то, что остальные правила проходят), но позволяет второй

INSERT INTO buyout_conditions (id, name, operator_id, user_id, landing_id, type, buyout_minutes, is_buyout_only_after_1st_rebill, is_buyout_only_unique_phone, created_by, created_at, updated_at) VALUES (1, 'test condition1', 1, 37, 1, 1, 20, null, null, 1, 111, 111);

INSERT INTO buyout_conditions (id, name, operator_id, user_id, landing_id, type, buyout_minutes, is_buyout_only_after_1st_rebill, is_buyout_only_unique_phone, created_by, created_at, updated_at) VALUES (2, 'test condition2', 0, 37, 1, 1, 10, null, null, 1, 111, 111);

INSERT INTO buyout_conditions (id, name, operator_id, user_id, landing_id, type, buyout_minutes, is_buyout_only_after_1st_rebill, is_buyout_only_unique_phone, created_by, created_at, updated_at) VALUES (3, 'test condition3', 1, 37, 0, 1, 10, null, null, 1, 111, 111);

INSERT INTO buyout_conditions (id, name, operator_id, user_id, landing_id, type, buyout_minutes, is_buyout_only_after_1st_rebill, is_buyout_only_unique_phone, created_by, created_at, updated_at) VALUES (4, 'test condition4', 0, 37, 0, 1, 10, null, null, 1, 111, 111);

INSERT INTO buyout_conditions (id, name, operator_id, user_id, landing_id, type, buyout_minutes, is_buyout_only_after_1st_rebill, is_buyout_only_unique_phone, created_by, created_at, updated_at) VALUES (5, 'test condition5', 1, 0, 1, 1, 10, null, null, 1, 111, 111);

INSERT INTO buyout_conditions (id, name, operator_id, user_id, landing_id, type, buyout_minutes, is_buyout_only_after_1st_rebill, is_buyout_only_unique_phone, created_by, created_at, updated_at) VALUES (6, 'test condition6', 0, 0, 1, 1, 10, null, null, 1, 111, 111);

INSERT INTO buyout_conditions (id, name, operator_id, user_id, landing_id, type, buyout_minutes, is_buyout_only_after_1st_rebill, is_buyout_only_unique_phone, created_by, created_at, updated_at) VALUES (7, 'test condition7', 1, 0, 0, 1, 10, null, null, 1, 111, 111);


# Одна подписка с оператором buyout_minutes = 10 старше 10мин выкупится
INSERT INTO search_subscriptions (hit_id, time_on, time_off, time_rebill, last_time, count_rebills, sum_real_profit_rub, sum_real_profit_eur, sum_real_profit_usd, sum_reseller_profit_rub, sum_reseller_profit_eur, sum_reseller_profit_usd, sum_profit_rub, sum_profit_eur, sum_profit_usd, phone, is_cpa, currency_id, landing_id, source_id, operator_id, platform_id, landing_pay_type_id, provider_id, country_id, stream_id, user_id, is_updated)
VALUES (1001, UNIX_TIMESTAMP() - 11 * 60, 0, UNIX_TIMESTAMP(), 1470876956, 1, 0.00, 0.00, 0.08, 0.00, 0.00, 0.08, 4.09, 0.06, 0.07,
        '', 1, 2, 1, 627, 1, 101, 3, 0, 5, 84, 37, 1);

INSERT INTO search_subscriptions (hit_id, time_on, time_off, time_rebill, last_time, count_rebills, sum_real_profit_rub, sum_real_profit_eur, sum_real_profit_usd, sum_reseller_profit_rub, sum_reseller_profit_eur, sum_reseller_profit_usd, sum_profit_rub, sum_profit_eur, sum_profit_usd, phone, is_cpa, currency_id, landing_id, source_id, operator_id, platform_id, landing_pay_type_id, provider_id, country_id, stream_id, user_id, is_updated)
VALUES (1002, UNIX_TIMESTAMP() - 21 * 60, 0, UNIX_TIMESTAMP(), 1470876956, 1, 0.00, 0.00, 0.08, 0.00, 0.00, 0.08, 4.09, 0.06, 0.07,
        '', 1, 2, 1, 627, 1, 101, 3, 0, 5, 84, 37, 1);