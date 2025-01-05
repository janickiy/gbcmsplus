-- заинсертить баланс по стране, иначе не посчитает её
INSERT INTO `user_balances_grouped_by_day` (`date`, `user_id`, `country_id`, `type`, `profit_rub`, `profit_usd`, `profit_eur`, `user_currency`)
VALUES ('2018-04-01', 101, 1, 1, '1', '1', '2', 'rub');

INSERT INTO `hold_programs` (`id`, `name`, `description`, `is_default`, `created_at`, `updated_at`)
VALUES (1, 'tst', 'tst', 1, 1524930412, 1524930412);

-- тут правила прописаны рандомно, на самом деле сами правила не нужны, нужна только связка со страной
INSERT INTO `hold_program_rules` (`id`, `hold_program_id`, `country_id`, `unhold_range`, `unhold_range_type`, `min_hold_range`, `min_hold_range_type`, `at_day`, `at_day_type`, `created_at`, `updated_at`)
VALUES (1, 1, 1, 1, 1, 1, 1, 1, 1, 1524930412, 1524930412);

INSERT INTO `hold_program_rules` (`id`, `hold_program_id`, `country_id`, `unhold_range`, `unhold_range_type`, `min_hold_range`, `min_hold_range_type`, `at_day`, `at_day_type`, `created_at`, `updated_at`)
VALUES (2, 1, NULL, 1, 1, 1, 1, 1, 1, 1524930412, 1524930412);

INSERT INTO `user_payment_settings` (`user_id`, `hold_program_id`, `referral_percent`, `early_payment_percent`, `early_payment_percent_old`)
VALUES (101, 1, 3, '3', '3');

INSERT INTO `rule_unhold_plan` (`rule_id`, `date_from`, `date_to`, `unhold_date`, `meta`)
VALUES (1, '2018-04-01', '2018-04-10', '2018-04-12', '123123');

INSERT INTO `rule_unhold_plan` (`rule_id`, `date_from`, `date_to`, `unhold_date`, `meta`)
VALUES (1, '2018-04-11', '2018-04-21', CURDATE() + INTERVAL 1 WEEK, '321312123');

-- чтобы разбавить вводим ещё немного фикстур
INSERT INTO `rule_unhold_plan` (`rule_id`, `date_from`, `date_to`, `unhold_date`)
VALUES (2, '2018-04-01', '2018-04-09', '2018-04-13');

INSERT INTO `rule_unhold_plan` (`rule_id`, `date_from`, `date_to`, `unhold_date`)
VALUES (2, '2018-04-11', '2018-04-20', CURDATE() + INTERVAL 1 MONTH);