<?php
// Комментарии в фикстуре subscription_offs
$hits = [];

$date = new DateTime('2017-01-01 8:00:00');
$id = 1;
$hits[] = [
  'id' => $id,
  'is_unique' => '0',
  'is_tb' => '0',
  'time' => $date->getTimestamp(),
  'date' => $date->format('Y-m-d'),
  'hour' => $date->format('G'),
  'operator_id' => '1',
  'landing_id' => '1',
  'source_id' => '1',
  'platform_id' => '1',
  'landing_pay_type_id' => '1',
  'is_cpa' => '1',
];

$date = new DateTime('2017-01-01 8:00:00');
$id = 2;
$hits[] = [
  'id' => '2',
  'is_unique' => '0',
  'is_tb' => '0',
  'time' => $date->getTimestamp(),
  'date' => $date->format('Y-m-d'),
  'hour' => $date->format('G'),
  'operator_id' => '1',
  'landing_id' => '1',
  'source_id' => '1',
  'platform_id' => '1',
  'landing_pay_type_id' => '1',
  'is_cpa' => '1',
];

$date = new DateTime('2017-01-01 8:00:00');
$id = 3;
$hits[] = [
  'id' => '3',
  'is_unique' => '0',
  'is_tb' => '0',
  'time' => $date->getTimestamp(),
  'date' => $date->format('Y-m-d'),
  'hour' => $date->format('G'),
  'operator_id' => '2',
  'landing_id' => '1',
  'source_id' => '1',
  'platform_id' => '1',
  'landing_pay_type_id' => '1',
  'is_cpa' => '1',
];

$date = new DateTime('2017-01-01 8:00:00');
$id = 4;
$hits[] = [
  'id' => '4',
  'is_unique' => '0',
  'is_tb' => '0',
  'time' => $date->getTimestamp(),
  'date' => $date->format('Y-m-d'),
  'hour' => $date->format('G'),
  'operator_id' => '3',
  'landing_id' => '1',
  'source_id' => '1',
  'platform_id' => '1',
  'landing_pay_type_id' => '1',
  'is_cpa' => '1',
];

$date = new DateTime('2017-01-02 8:00:00');
$id = 5;
$hits[] = [
  'id' => '5',
  'is_unique' => '0',
  'is_tb' => '0',
  'time' => $date->getTimestamp(),
  'date' => $date->format('Y-m-d'),
  'hour' => $date->format('G'),
  'operator_id' => '1',
  'landing_id' => '1',
  'source_id' => '1',
  'platform_id' => '1',
  'landing_pay_type_id' => '1',
  'is_cpa' => '1',
];

$date = new DateTime('2017-01-02 8:00:00');
$id = 6;
$hits[] = [
  'id' => '6',
  'is_unique' => '0',
  'is_tb' => '0',
  'time' => $date->getTimestamp(),
  'date' => $date->format('Y-m-d'),
  'hour' => $date->format('G'),
  'operator_id' => '1',
  'landing_id' => '1',
  'source_id' => '1',
  'platform_id' => '1',
  'landing_pay_type_id' => '1',
  'is_cpa' => '0',
];

$date = new DateTime('2017-01-02 8:00:00');
$id = 7;
$hits[] = [
  'id' => '7',
  'is_unique' => '0',
  'is_tb' => '0',
  'time' => $date->getTimestamp(),
  'date' => $date->format('Y-m-d'),
  'hour' => $date->format('G'),
  'operator_id' => '1',
  'landing_id' => '1',
  'source_id' => '1',
  'platform_id' => '1',
  'landing_pay_type_id' => '1',
  'is_cpa' => '1',
];

return $hits;