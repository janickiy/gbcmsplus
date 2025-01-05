<?php

return [
  // досрочная выплата по холду
  [
    'id' => 1000000,
    'user_id' => 104,
    'wallet_type' => 1,
    'currency' => 'rub',
    'amount' => 100,
    'invoice_amount' => 100,
    'status' => 1, //STATUS_COMPLETED
    'created_at' => strtotime('now -2day'),
    'updated_at' => strtotime('now -2day'),
    'payed_at' => strtotime('now -1day'),
    'response' => '',
    'is_hold' => 1,
    'created_by' => 5,
    'from_date' => null,
    'to_date' => null,
    'type' => 0 //TYPE_ADMIN_MANUAL
  ]
];