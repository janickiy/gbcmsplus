<?php

return [
  [
    'user_id' => 101,
    'user_payment_id' => NULL,
    'currency' => 'rub',
    'amount' => 1000.00,
    'description' => NULL,
    'created_at' => 1466679472,
    'created_by' => 1,
    'type' => 3,
  ],
  [
    'user_id' => 101,
    'user_payment_id' => NULL,
    'currency' => 'rub',
    'amount' => 4000,
    'description' => NULL,
    'created_at' => 1466679472,
    'created_by' => 1,
    'type' => 1,
  ],
  [
    'user_id' => 101,
    'user_payment_id' => NULL,
    'currency' => 'rub',
    'amount' => -100,
    'description' => NULL,
    'created_at' => 1466679472,
    'created_by' => 1,
    'type' => 1,
  ],
  // выплата по холду
  [
    'user_id' => 104,
    'user_payment_id' => 1000000,
    'currency' => 'rub',
    'amount' => -100,
    'created_at' => strtotime('now -2day'),
    'created_by' => 1,
    'type' => 2, //TYPE_EARLY_PAYMENT
  ],
  // перевод с холда на основной
  [
    'user_id' => 104,
    'user_payment_id' => NULL,
    'currency' => 'rub',
    'amount' => -50,
    'created_at' => strtotime('now -2day'),
    'created_by' => 1,
    'type' => 8, //TYPE_FROM_HOLD_TRANSFER
  ],
  [
    'user_id' => 104,
    'user_payment_id' => NULL,
    'currency' => 'rub',
    'amount' => 50,
    'created_at' => strtotime('now -2day'),
    'created_by' => 1,
    'type' => 8, //TYPE_FROM_HOLD_TRANSFER
  ],
];
