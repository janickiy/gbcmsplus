TRUNCATE TABLE personal_profit;
TRUNCATE TABLE partner_program_items;
TRUNCATE TABLE partner_programs;

INSERT INTO partner_programs (id, name, description, created_by, updated_by, created_at, updated_at) VALUES
  (1, 'tst1', 'tst1', 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
  (2, 'tst2', 'tst2', 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

INSERT INTO personal_profit (user_id, operator_id, landing_id, rebill_percent, buyout_percent, cpa_profit_rub, cpa_profit_usd, cpa_profit_eur, created_by, created_at)
VALUES
  -- строка профита с указанным оператором и все цпа профиты указаны
  (0, 1, 0, 90, 90, 999, 888, 777, 1, UNIX_TIMESTAMP());

INSERT INTO partner_program_items (id, partner_program_id, operator_id, landing_id, rebill_percent, buyout_percent, cpa_profit_rub, cpa_profit_usd, cpa_profit_eur, created_by, updated_by, created_at, updated_at)
VALUES
  -- строка профита с указанным оператором и все цпа профиты указаны
  (2, 1, 1, 0, 90, 90, 999, 888, 777, 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());