<?php
$hits = [];
$id = 1;

$date = new DateTime('-14 days');
$hits[] = [
  'id' => $id,
  'is_unique' => '0',
  'is_tb' => '1',
  'time' => $date->getTimestamp() + 300, // добавил 5 минут, чтобы дать время на работу теста
  'date' => $date->format('Y-m-d'),
  'hour' => $date->format('G'),
  'operator_id' => '1',
  'landing_id' => '1',
  'source_id' => '1',
  'platform_id' => '1',
  'landing_pay_type_id' => '1',
  'is_cpa' => '0',
];

$date = new DateTime('-15 days');
$hits[] = [
  'id' => ++$id,
  'is_unique' => '0',
  'is_tb' => '1',
  'time' => $date->getTimestamp(),
  'date' => $date->format('Y-m-d'),
  'hour' => $date->format('G'),
  'operator_id' => '2',
  'landing_id' => '1',
  'source_id' => '1',
  'platform_id' => '1',
  'landing_pay_type_id' => '1',
  'is_cpa' => '0',
];

$date = new DateTime('-25 days');
$hits[] = [
  'id' => ++$id,
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
  'is_cpa' => '0',
];

$date = new DateTime('-31 days');
$hits[] = [
  'id' => ++$id,
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