set foreign_key_checks=0;
TRUNCATE TABLE `countries`;
INSERT INTO `countries` (`id`, `name`, `status`, `code`, `currency`, `created_at`, `updated_at`, `sync_updated_at`) VALUES (1, 'Russia', 1, 'RU', 'rub', 1527198238, NULL, NULL);
INSERT INTO `countries` (`id`, `name`, `status`, `code`, `currency`, `created_at`, `updated_at`, `sync_updated_at`) VALUES (11, 'Kazakhstan', 1, 'KZ', 'eur', 1527198238, NULL, NULL);
set foreign_key_checks=1;