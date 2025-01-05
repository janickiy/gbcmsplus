set foreign_key_checks=0;
TRUNCATE TABLE `country_currency_log`;
INSERT INTO `country_currency_log` (`id`, `country_id`, `currency`, `created_at`) VALUES (3, 11, 'rub', 0);
INSERT INTO `country_currency_log` (`id`, `country_id`, `currency`, `created_at`) VALUES (4, 11, 'eur', 1526936400);
set foreign_key_checks=1;
