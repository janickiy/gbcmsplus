delete from user_promo_settings where user_id=103;
truncate table traffic_blocks;

insert into user_promo_settings (user_id, is_blacklist_traffic_blocks) VALUES
  (101, 1),
  (102, 0)
on duplicate key update is_blacklist_traffic_blocks = VALUES(is_blacklist_traffic_blocks);

insert into traffic_blocks (user_id, operator_id, is_blacklist) VALUES
  (101, 1, 1),
  (101, 2, 0),
  (102, 1, 1),
  (102, 2, 0),
  (103, 1, 1),
  (103, 2, 0);
