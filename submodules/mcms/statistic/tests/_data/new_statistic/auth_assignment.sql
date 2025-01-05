SET foreign_key_checks = 0;
TRUNCATE TABLE `auth_assignment`;

INSERT INTO `auth_assignment` (`item_name`,`user_id`,`created_at`) VALUES ('admin','2',1478008246);
INSERT INTO `auth_assignment` (`item_name`,`user_id`,`created_at`) VALUES ('manager','1485',1538130001);
INSERT INTO `auth_assignment` (`item_name`,`user_id`,`created_at`) VALUES ('manager','2512',1525251931);
INSERT INTO `auth_assignment` (`item_name`,`user_id`,`created_at`) VALUES ('partner','1052',1509485159);
INSERT INTO `auth_assignment` (`item_name`,`user_id`,`created_at`) VALUES ('partner','2348',1534237272);
INSERT INTO `auth_assignment` (`item_name`,`user_id`,`created_at`) VALUES ('partner','2524',1553011496);
INSERT INTO `auth_assignment` (`item_name`,`user_id`,`created_at`) VALUES ('partner','3310',1553780436);
INSERT INTO `auth_assignment` (`item_name`,`user_id`,`created_at`) VALUES ('reseller','4',1483263624);
INSERT INTO `auth_assignment` (`item_name`,`user_id`,`created_at`) VALUES ('root','1',1478008245);

SET foreign_key_checks = 1;