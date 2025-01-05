INSERT INTO user_balances_grouped_by_day (date, user_id, country_id, type, profit_rub, profit_eur, profit_usd, user_currency) VALUES
  ('2019-03-05', 101, 1, 0, 240.00, 3, 3.6, 'rub'),
  ('2019-03-05', 101, 1, 2, 273.00, 3, 6.9, 'usd'),
  ('2019-03-05', 101, 2, 1, 271.00, 3, 4.9, 'rub'),
  ('2019-03-20', 101, 1, 0, 1020.00, 12, 15, 'rub'),
  ('2019-03-20', 101, 1, 1, 783.00, 9, 14.4, 'rub'),
  ('2019-03-20', 101, 1, 2, 516.00, 6, 13.5, 'usd'),
  ('2019-03-20', 101, 2, 1, 241.00, 3, 4.6, 'rub'),
  ('2019-03-20', 101, 2, 2, 516.00, 6, 13.5, 'rub');

INSERT INTO user_balance_invoices (mgmp_id, user_id, country_id, user_payment_id, currency, amount, description, created_at, date, updated_at, created_by, type, file) VALUES
  (null, 101, 0, null, 'rub', 100.00, '', 111, '2019-03-24', null, 1, 1, null),
  (null, 101, 1, null, 'rub', 120.00, '', 111, '2019-03-20', null, 1, 1, null),
  (null, 101, 1, null, 'usd', 23.00, '', 111, '2019-03-20', null, 1, 1, null),
  (null, 101, 1, null, 'rub', 130.00, '', 111, '2019-03-21', null, 1, 1, null);

INSERT INTO user_payment_settings (user_id, is_auto_payments, referral_percent, early_payment_percent_old, currency, early_payment_percent, old_wallet_type, old_wallet_account, is_disabled, is_hold_autopay_enabled, is_auto_payout_disabled, last_generated_payment, pay_terms, hold_program_id) VALUES
  (101, 0, 3, 0.0, 'rub', null, null, null, 0, 0, 1, null, 6, 2);

INSERT INTO hold_programs (id, name, description, is_default, created_at, updated_at) VALUES
  (1, 'Дефолтная программа', 'Описание', 1, 1524833853, 1524833962),
  (2, 'Вторая программа', 'Описание', 0, 1524834758, 1524834758);

INSERT INTO hold_program_rules (id, hold_program_id, country_id, unhold_range, unhold_range_type, min_hold_range, min_hold_range_type, at_day, at_day_type, key_date, created_at, updated_at) VALUES
  (1, 2, null, 3, 3, 13, 1, 3, 1, '2019-04-01', 1524833949, 1524909379),
  (2, 2, 1, 73, 1, 2, 3, 1, 1, '2019-04-19', 1524834797, 1524909471);