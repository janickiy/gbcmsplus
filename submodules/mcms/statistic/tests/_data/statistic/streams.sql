set foreign_key_checks=0;
TRUNCATE TABLE `streams`;
INSERT INTO `streams` (`id`, `name`, `status`, `user_id`, `created_at`, `updated_at`) VALUES (1, 'Default', 1, 9, 1527536796, 1527536796);
INSERT INTO `streams` (`id`, `name`, `status`, `user_id`, `created_at`, `updated_at`) VALUES (2, 'Default', 1, 10, 1527537088, 1527537088);
set foreign_key_checks=1;