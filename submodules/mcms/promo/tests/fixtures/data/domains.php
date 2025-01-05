<?php

return [
  'system1' => [
    'id' => 1,
    'url' => 'http://system_domain1.ru', // без слэша в конце
    'status' => 1,
    'user_id' => 1,
    'type' => 1,
    'created_by' => 1,
    'is_system' => 1,
    'domain_name' => 'system_domain1.ru',
  ],
  'system2' => [
    'id' => 2,
    'url' => 'system_domain2.ru', // без слэша в конце и без http
    'status' => 1,
    'user_id' => 1,
    'type' => 1,
    'created_by' => 1,
    'is_system' => 1,
    'domain_name' => 'system_domain2.ru',
  ],
  'system3' => [
    'id' => 3,
    'url' => 'system_domain3.ru/', // без http вначале
    'status' => 1,
    'user_id' => 1,
    'type' => 1,
    'created_by' => 1,
    'is_system' => 1,
    'domain_name' => 'system_domain3.ru',
  ],
  'system4' => [
    'id' => 4,
    'url' => 'http://system_domain4.ru/', // типа правильная запись домена
    'status' => 1,
    'user_id' => 1,
    'type' => 1,
    'created_by' => 1,
    'is_system' => 1,
    'domain_name' => 'system_domain4.ru',
  ],
];