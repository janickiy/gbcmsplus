SET foreign_key_checks = 0;
TRUNCATE TABLE `hits`;

INSERT INTO `refunds` (`id`, `type`, `hit_id`, `trans_id`, `time`, `date`, `hour`, `description`, `currency_id`, `local_currency`, `local_sum`, `reseller_rub`, `reseller_usd`, `reseller_eur`, `landing_id`, `source_id`, `operator_id`, `platform_id`, `landing_pay_type_id`, `is_cpa`) VALUES ('320', '1', '272311101', '96', '1551162480', '2019-02-26', '9', 'complaint from MNO \r\n', '3', X'62796e', '3.25000', '96.66353', '1.47570', '1.28499', '4339', '11077', '13', '101', '5', '1');
INSERT INTO `refunds` (`id`, `type`, `hit_id`, `trans_id`, `time`, `date`, `hour`, `description`, `currency_id`, `local_currency`, `local_sum`, `reseller_rub`, `reseller_usd`, `reseller_eur`, `landing_id`, `source_id`, `operator_id`, `platform_id`, `landing_pay_type_id`, `is_cpa`) VALUES ('324', '2', '168624659', '305', '1551254220', '2019-02-27', '10', '', '3', X'6b7a74', '0.00000', '0.00000', '0.00000', '0.00000', '4515', '8227', '222', '101', '5', '1');
INSERT INTO `refunds` (`id`, `type`, `hit_id`, `trans_id`, `time`, `date`, `hour`, `description`, `currency_id`, `local_currency`, `local_sum`, `reseller_rub`, `reseller_usd`, `reseller_eur`, `landing_id`, `source_id`, `operator_id`, `platform_id`, `landing_pay_type_id`, `is_cpa`) VALUES ('333', '1', '267317011', '2906', '1551440640', '2019-03-01', '14', 'complaint from MNO \r\n', '3', X'727562', '600.00000', '600.00000', '8.61002', '7.49725', '5121', '11892', '2', '101', '5', '0');
INSERT INTO `refunds` (`id`, `type`, `hit_id`, `trans_id`, `time`, `date`, `hour`, `description`, `currency_id`, `local_currency`, `local_sum`, `reseller_rub`, `reseller_usd`, `reseller_eur`, `landing_id`, `source_id`, `operator_id`, `platform_id`, `landing_pay_type_id`, `is_cpa`) VALUES ('337', '1', '271238830', '2920', '1551705000', '2019-03-04', '16', 'complaint from MNO \r\n', '3', X'727562', '390.00000', '390.00000', '5.59652', '4.87321', '5226', '10641', '2', '101', '5', '1');
INSERT INTO `refunds` (`id`, `type`, `hit_id`, `trans_id`, `time`, `date`, `hour`, `description`, `currency_id`, `local_currency`, `local_sum`, `reseller_rub`, `reseller_usd`, `reseller_eur`, `landing_id`, `source_id`, `operator_id`, `platform_id`, `landing_pay_type_id`, `is_cpa`) VALUES ('338', '1', '271993598', '99', '1551709680', '2019-03-04', '17', 'complaint from MNO \r\n', '3', X'62796e', '7.80000', '231.99248', '3.54168', '3.08397', '4339', '11077', '13', '101', '5', '1');
INSERT INTO `refunds` (`id`, `type`, `hit_id`, `trans_id`, `time`, `date`, `hour`, `description`, `currency_id`, `local_currency`, `local_sum`, `reseller_rub`, `reseller_usd`, `reseller_eur`, `landing_id`, `source_id`, `operator_id`, `platform_id`, `landing_pay_type_id`, `is_cpa`) VALUES ('381', '1', '254808248', '2928', '1552034760', '2019-03-08', '11', 'complaint from MNO \r\n', '3', X'727562', '750.00000', '750.00000', '10.76253', '9.37157', '5226', '10641', '2', '101', '5', '1');

SET foreign_key_checks = 1;