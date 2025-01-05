set foreign_key_checks=0;
TRUNCATE TABLE `providers`;
INSERT INTO `providers` (`id`, `name`, `code`, `url`, `status`, `redirect_to`, `created_by`, `created_at`, `updated_at`, `handler_class_name`, `settings`) VALUES (1, 'kpru', 'kpru', '', 1, NULL, 1, 0, NULL, NULL, NULL);
INSERT INTO `providers` (`id`, `name`, `code`, `url`, `status`, `redirect_to`, `created_by`, `created_at`, `updated_at`, `handler_class_name`, `settings`) VALUES (2, 'kpkz', 'kpkz', '', 1, NULL, 1, 0, NULL, NULL, NULL);
set foreign_key_checks=1;
