<?php
$rebills = [];

$date = new DateTime('2017-01-02 09:00:00');
$id = 7;
$rebills[] = [
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
  'profit_rub' => 120,
  'profit_usd' => 2,
  'profit_eur' => 1.9,
  'provider_id' => '0',
];

$date = new DateTime('2017-01-02 09:00:00');
$id = 6;
$rebills[] = [
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
  'profit_rub' => 120,
  'profit_usd' => 2,
  'profit_eur' => 1.9,
  'provider_id' => '0',
];

return $rebills;