<?php
$rP = 90;
$pP = 90;
$eur = 71.24;
$rub = 1;
$usd = 64.82;

$userId = rand(5, 6);
$realRub = $faker->randomFloat(2, 100, 300);
$realEur = $realRub / $eur;
$realUsd = $realRub / $usd;

/** @var Faker\Generator $faker */
return [
  'hit_id' => $index,
  'trans_id' => $faker->uuid,
  'time' => $time = strtotime('2016-06-16 -' . rand(1, 3) . 'days'),
  'date' => $date = date('Y-m-d', $time),
  'hour' => rand(0, 23),
  'default_profit' => $faker->randomFloat(2, 1, 3),
  'default_profit_currency' => rand(1, 3),
  'currency_id' => $currencyId = rand(1, 3),
  'real_profit_rub' => ($currencyId == 1 ? $realRub : 0),
  'real_profit_eur' => ($currencyId == 2 ? $realEur : 0),
  'real_profit_usd' => ($currencyId == 3 ? $realUsd : 0),
  'reseller_profit_rub' => ($currencyId == 1 ? $realRub * $rP / 100 : 0),
  'reseller_profit_eur' => ($currencyId == 2 ? $realEur * $rP/ 100 / $eur : 0),
  'reseller_profit_usd' => ($currencyId == 3 ? $realUsd * $rP / 100 / $usd : 0),
  'profit_rub' => $realRub * $rP / 100 * $pP / 100,
  'profit_eur' => $realEur * $rP / 100 * $pP / 100 / $eur,
  'profit_usd' => $realUsd * $rP / 100 * $pP / 100 / $usd,
  'user_id' => $userId,
  'landing_id' => rand(1, 2),
  'operator_id' => rand(1, 3),
];