TRUNCATE TABLE personal_profit;
TRUNCATE TABLE partner_program_items;

INSERT INTO personal_profit (user_id, operator_id, landing_id, rebill_percent, buyout_percent, cpa_profit_rub, cpa_profit_usd, cpa_profit_eur, created_by, created_at)
  VALUES
    -- строка профита с не указанными фикс. цпа
    (0, 1, 0, 90, 90, null, null, null, 1, UNIX_TIMESTAMP());

INSERT INTO partner_program_items (partner_program_id, operator_id, landing_id, rebill_percent, buyout_percent, cpa_profit_rub, cpa_profit_usd, cpa_profit_eur, created_by, updated_by, created_at, updated_at)
VALUES
  -- строка профита с не указанными фикс. цпа
  (1, 1, 0, 90, 90, null, null, null, 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());