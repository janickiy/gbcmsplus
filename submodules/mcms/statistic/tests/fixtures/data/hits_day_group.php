<?php

$data = [];
for ($i = 0; $i < 50; $i ++) {
  $date = strtotime(sprintf("- %d day", rand(0, 6)));
  $data[] = [
    'count_hits' => rand(0, 10),
    'count_uniques' => rand(0, 10),
    'count_tb' => rand(0, 10),
    'date' => date('Y-m-d', $date),
    'source_id' => $i,
    'landing_id' => $i,
    'operator_id' => $i,
    'platform_id' => $i,
    'landing_pay_type_id' => $i,
    'provider_id' => $i,
    'stream_id' => $i,
    'user_id' => 101,
    'country_id' => $i,
    'is_cpa' => $i,
  ];
}

return $data;