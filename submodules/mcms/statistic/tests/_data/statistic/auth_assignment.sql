set foreign_key_checks=0;
TRUNCATE TABLE `auth_assignment`;
INSERT INTO `auth_assignment` (`item_name`, `user_id`, `created_at`) VALUES ('admin', '2', 1527197736);
INSERT INTO `auth_assignment` (`item_name`, `user_id`, `created_at`) VALUES ('manager', '11', 1527629933);
INSERT INTO `auth_assignment` (`item_name`, `user_id`, `created_at`) VALUES ('manager', '12', 1527629957);
INSERT INTO `auth_assignment` (`item_name`, `user_id`, `created_at`) VALUES ('partner', '10', 1527197740);
INSERT INTO `auth_assignment` (`item_name`, `user_id`, `created_at`) VALUES ('partner', '3', 1527197737);
INSERT INTO `auth_assignment` (`item_name`, `user_id`, `created_at`) VALUES ('partner', '7', 1527197741);
INSERT INTO `auth_assignment` (`item_name`, `user_id`, `created_at`) VALUES ('partner', '9', 1527197739);
INSERT INTO `auth_assignment` (`item_name`, `user_id`, `created_at`) VALUES ('reseller', '4', 1527197738);
INSERT INTO `auth_assignment` (`item_name`, `user_id`, `created_at`) VALUES ('root', '1', 1527197735);

set foreign_key_checks=1;