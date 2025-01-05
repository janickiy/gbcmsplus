set foreign_key_checks=0;
TRUNCATE TABLE `operators`;
INSERT INTO `operators` (`id`, `country_id`, `name`, `is_3g`, `status`, `created_by`, `created_at`, `updated_at`, `sync_updated_at`, `is_trial`, `is_disallow_replace_landing`, `show_service_url`) VALUES (1, 1, 'MTS', 1, 1, 1, 1521622087, 1526935862, 1524656694, NULL, 0, 1);
INSERT INTO `operators` (`id`, `country_id`, `name`, `is_3g`, `status`, `created_by`, `created_at`, `updated_at`, `sync_updated_at`, `is_trial`, `is_disallow_replace_landing`, `show_service_url`) VALUES (222, 11, 'Kcell', 1, 1, 1, 1521622155, 1521622155, 1512483225, NULL, 0, 1);
set foreign_key_checks=1;