<?php
$subscriptions = [];

// Проверка группировки
// Два хита, которые в результате должны быть сгрупированы
$date = new DateTime('2017-01-01 9:00:00');
$id = 1;
$subscriptions[] = [
  'id' => $id,
  'hit_id' => $id,
  'trans_id' => $id . '9661b-f1cc-4ff5-8a03-3ea04dc9514c',
  'time' => $date->getTimestamp(),
  'date' => $date->format('Y-m-d'),
  'hour' => $date->format('G'),
  'operator_id' => '1',
  'landing_id' => '1',
  'source_id' => '1',
  'platform_id' => '1',
  'landing_pay_type_id' => '1',
  'is_cpa' => '1',
  'currency_id' => '1',
  'provider_id' => '0',
  'is_fake' => '0',
];

$date = new DateTime('2017-01-01 9:00:00');
$id = 2;
$subscriptions[] = [
  'id' => $id,
  'hit_id' => $id,
  'trans_id' => $id . '9661b-f1cc-4ff5-8a03-3ea04dc9514c',
  'time' => $date->getTimestamp(),
  'date' => $date->format('Y-m-d'),
  'hour' => $date->format('G'),
  'operator_id' => '1',
  'landing_id' => '1',
  'source_id' => '1',
  'platform_id' => '1',
  'landing_pay_type_id' => '1',
  'is_cpa' => '1',
  'currency_id' => '1',
  'provider_id' => '0',
  'is_fake' => '0',
];

// Два хита, которые в результате не должны быть сгрупированы
$date = new DateTime('2017-01-01 9:00:00');
$id = 3;
$subscriptions[] = [
  'id' => $id,
  'hit_id' => $id,
  'trans_id' => $id . '9661b-f1cc-4ff5-8a03-3ea04dc9514c',
  'time' => $date->getTimestamp(),
  'date' => $date->format('Y-m-d'),
  'hour' => $date->format('G'),
  'operator_id' => '2',
  'landing_id' => '1',
  'source_id' => '1',
  'platform_id' => '1',
  'landing_pay_type_id' => '1',
  'is_cpa' => '1',
  'currency_id' => '1',
  'provider_id' => '0',
  'is_fake' => '0',
];

$date = new DateTime('2017-01-01 9:00:00');
$id = 4;
$subscriptions[] = [
  'id' => $id,
  'hit_id' => $id,
  'trans_id' => $id . '9661b-f1cc-4ff5-8a03-3ea04dc9514c',
  'time' => $date->getTimestamp(),
  'date' => $date->format('Y-m-d'),
  'hour' => $date->format('G'),
  'operator_id' => '3',
  'landing_id' => '1',
  'source_id' => '1',
  'platform_id' => '1',
  'landing_pay_type_id' => '1',
  'is_cpa' => '1',
  'currency_id' => '1',
  'provider_id' => '0',
  'is_fake' => '0',
];

// Проверка учета периода в 24 часа
// Две отписки в течении 24 часов. В результате должны быть сгруппированы
$date = new DateTime('2017-01-02 9:00:00');
$id = 5;
$subscriptions[] = [
  'id' => $id,
  'hit_id' => $id,
  'trans_id' => $id . '9661b-f1cc-4ff5-8a03-3ea04dc9514c',
  'time' => $date->getTimestamp(),
  'date' => $date->format('Y-m-d'),
  'hour' => $date->format('G'),
  'operator_id' => '1',
  'landing_id' => '1',
  'source_id' => '1',
  'platform_id' => '1',
  'landing_pay_type_id' => '1',
  'is_cpa' => '1',
  'currency_id' => '1',
  'provider_id' => '0',
  'is_fake' => '0',
];

$date = new DateTime('2017-01-02 9:00:00');
$id = 6;
$subscriptions[] = [
  'id' => $id,
  'hit_id' => $id,
  'trans_id' => $id . '9661b-f1cc-4ff5-8a03-3ea04dc9514c',
  'time' => $date->getTimestamp(),
  'date' => $date->format('Y-m-d'),
  'hour' => $date->format('G'),
  'operator_id' => '1',
  'landing_id' => '1',
  'source_id' => '1',
  'platform_id' => '1',
  'landing_pay_type_id' => '1',
  'is_cpa' => '0',
  'currency_id' => '1',
  'provider_id' => '0',
  'is_fake' => '0',
];

// Третья отписка через 25 часов, не должна попасть в таблицу statistic_data_hour_group, так как не попадает в 24 часа
$date = new DateTime('2017-01-03 10:00:00');
$id = 7;
$subscriptions[] = [
  'id' => $id,
  'hit_id' => $id,
  'trans_id' => $id . '9661b-f1cc-4ff5-8a03-3ea04dc9514c',
  'time' => $date->getTimestamp(),
  'date' => $date->format('Y-m-d'),
  'hour' => $date->format('G'),
  'operator_id' => '1',
  'landing_id' => '1',
  'source_id' => '1',
  'platform_id' => '1',
  'landing_pay_type_id' => '1',
  'is_cpa' => '1',
  'currency_id' => '1',
  'provider_id' => '0',
  'is_fake' => '0',
];

return $subscriptions;