set foreign_key_checks=0;
TRUNCATE TABLE `landing_operator_pay_types`;
INSERT INTO `landing_operator_pay_types` (`id`, `landing_id`, `operator_id`, `landing_pay_type_id`) VALUES (1, 2335, 222, 5);
INSERT INTO `landing_operator_pay_types` (`id`, `landing_id`, `operator_id`, `landing_pay_type_id`) VALUES (2, 549, 1, 3);
INSERT INTO `landing_operator_pay_types` (`id`, `landing_id`, `operator_id`, `landing_pay_type_id`) VALUES (3, 549, 1, 4);

set foreign_key_checks=1;