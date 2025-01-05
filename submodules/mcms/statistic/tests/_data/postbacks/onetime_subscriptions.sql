truncate table hits;
truncate table hit_params;
truncate table sold_subscriptions;
truncate table onetime_subscriptions;
truncate table subscriptions;
truncate table subscription_rebills;
truncate table subscription_offs;
truncate table postbacks;
truncate table sources;

INSERT INTO sources (id, hash, user_id, default_profit_type, url, status, source_type, name, category_id, stream_id, domain_id, created_at, is_notify_cpa, postback_url)
VALUES
  (
    1, 'testhash', 101, 1, 'url', 1, 2, 'name', 1, 123, 321, unix_timestamp(), 1,
   'http://some.ru/?stream_id={stream_id}&link_id={link_id}&link_name={link_name}&link_hash={link_hash}&action_time={action_time}&action_date={action_date}&type={type}&subscription_id={subscription_id}&operator_id={operator_id}&landing_id={landing_id}&description={description}&sum_rub={sum_rub}&sum_usd={sum_usd}&sum_eur={sum_eur}&rebill_id={rebill_id}&msisdn={msisdn}'
  );

INSERT INTO hits (id, is_unique, is_tb, time, date, hour, operator_id, landing_id, source_id, platform_id, landing_pay_type_id, is_cpa) VALUES
  (1, 1, 0, 1531236243, '2018-07-10', 15, 1, 2, 1, 4, 2, 1);

INSERT INTO hit_params (hit_id, ip, referer, user_agent) VALUES
  (1, 3333, 'ref', 'uagent');

INSERT INTO onetime_subscriptions (hit_id, trans_id, time, date, hour, default_profit, default_profit_currency, currency_id, real_profit_rub, real_profit_eur, real_profit_usd, reseller_profit_rub, reseller_profit_eur, reseller_profit_usd, profit_rub, profit_eur, profit_usd, calc_profit_rub, calc_profit_eur, calc_profit_usd, landing_id, source_id, operator_id, platform_id, landing_pay_type_id, provider_id, country_id, stream_id, user_id, phone, ip, is_visible_to_partner)
VALUES
  (1, 44444, 1531236243, '2018-07-10', 15, 3, 3, 3, 4, 5, 6, 7, 8, 9, 4, 5, 6, 7, 8, 2, 2, 1, 1, 2, 0, 0, 1, 1, '123123123',
   66544, 1, 1);