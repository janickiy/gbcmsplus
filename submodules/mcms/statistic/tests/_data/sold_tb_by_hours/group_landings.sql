INSERT INTO hits (id, is_unique, is_tb, time, date, hour, operator_id, landing_id, source_id, platform_id, landing_pay_type_id, is_cpa)
VALUES
  (1, 0, 1, 1516170148, '2018-01-17', 6, 1, 0, 3, 4, 5, 0),
  (2, 0, 1, 1516170148, '2018-01-17', 6, 2, 0, 4, 5, 6, 0),
  (3, 0, 1, 1516170148, '2018-01-17', 6, 3, 0, 3, 6, 1, 0),
  (4, 0, 1, 1516170148, '2018-01-17', 6, 4, 0, 4, 1, 2, 0);

INSERT INTO sell_tb_hits (hit_id, tb_provider_id, category_id, date, hour, operator_id, source_id, platform_id) VALUES
  (1, 1, 0, '2018-01-17', 6, 1, 3, 4),
  (2, 2, 0, '2018-01-17', 9, 2, 4, 5),
  (3, 3, 1, '2018-01-17', 2, 3, 3, 6),
  (4, 4, 2, '2018-01-17', 16, 4, 4, 1);

INSERT INTO sold_trafficback (hit_id, trans_id, time, date, hour, currency_id, reseller_profit_rub, reseller_profit_eur, reseller_profit_usd, landing_id, source_id, operator_id, platform_id, landing_pay_type_id, provider_id, tb_provider_id, category_id, country_id, stream_id, user_id)
  SELECT
    hit_id,
    MD5(hit_id),
    h.time + 60,
    FROM_UNIXTIME(h.time + 60),
    FROM_UNIXTIME(h.time + 60, '%H'),
    CASE s.hit_id
      WHEN 2 THEN 2
      WHEN 3 THEN 3
      ELSE 1
    END,
    120,
    1.8,
    2,

    0,
    h.source_id,
    h.operator_id,
    h.platform_id,
    0,
    0,
    s.tb_provider_id,
    s.category_id,
    1,
    2,
    3
  FROM sell_tb_hits s INNER JOIN hits h ON h.id = s.hit_id;