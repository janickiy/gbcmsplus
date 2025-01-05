set foreign_key_checks=0;
TRUNCATE TABLE `sources_operator_landings`;
INSERT INTO `sources_operator_landings` (`id`, `source_id`, `profit_type`, `operator_id`, `landing_id`, `is_changed`, `change_description`, `landing_choose_type`, `is_disable_handled`, `rating`) VALUES (1, 1, 1, 1, 549, 0, '', 0, 0, 0.0000);
INSERT INTO `sources_operator_landings` (`id`, `source_id`, `profit_type`, `operator_id`, `landing_id`, `is_changed`, `change_description`, `landing_choose_type`, `is_disable_handled`, `rating`) VALUES (2, 1, 2, 222, 2335, 0, '', 0, 0, 0.0000);
INSERT INTO `sources_operator_landings` (`id`, `source_id`, `profit_type`, `operator_id`, `landing_id`, `is_changed`, `change_description`, `landing_choose_type`, `is_disable_handled`, `rating`) VALUES (3, 3, 2, 1, 549, 0, '', 0, 0, 0.0000);
INSERT INTO `sources_operator_landings` (`id`, `source_id`, `profit_type`, `operator_id`, `landing_id`, `is_changed`, `change_description`, `landing_choose_type`, `is_disable_handled`, `rating`) VALUES (4, 3, 2, 222, 2335, 0, '', 0, 0, 0.0000);
set foreign_key_checks=1;