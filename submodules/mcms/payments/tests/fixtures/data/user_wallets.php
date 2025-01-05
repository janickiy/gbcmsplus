<?php

return [
  'u101_webmoney' => [
    'id' => 1,
    'user_id' => 101,
    'currency' => 'rub',
    'wallet_type' => 1,
    'wallet_account' => '{"wallet":"R123456789000"}'
  ],
  'u104' => [
    'id' => 2,
    'user_id' => 104,
    'currency' => 'rub',
    'wallet_type' => 1,
    'wallet_account' => '{"wallet":"R123456789001"}'
  ],
  'u101_yandex' => [
    'id' => 3,
    'user_id' => 101,
    'currency' => 'rub',
    'wallet_type' => 2,
    'wallet_account' => '{"wallet":"qweqwew@ya.ru"}'
  ],
  'u101_epayments' => [
    'id' => 4,
    'user_id' => 101,
    'currency' => 'rub',
    'wallet_type' => 3,
    'wallet_account' => '{"wallet":"000-123456"}'
  ],
  'u101_paypal' => [
    'id' => 6,
    'user_id' => 101,
    'currency' => 'rub',
    'wallet_type' => 5,
    'wallet_account' => '{"name":"Vasua", "email":"paypal@gmail.com"}'
  ],
  'u101_paxum' => [
    'id' => 7,
    'user_id' => 101,
    'currency' => 'rub',
    'wallet_type' => 6,
    'wallet_account' => '{"email":"vasua@gmail.com"}'
  ],
  'u101_wireIban' => [
    'id' => 8,
    'user_id' => 101,
    'currency' => 'rub',
    'wallet_type' => 7,
    'wallet_account' => '{"bank_county":"Украина","recipient":"Vasuok","iban_code":"12345678","swift_code":"1234","comment":"comment"}'
  ],
  'u101_Card' => [
    'id' => 9,
    'user_id' => 101,
    'currency' => 'rub',
    'wallet_type' => 10,
    'wallet_account' => '{"bank_name":"Bank","card_number":"1234123412341234"}',
  ],
  'u101_private_person' => [
    'id' => 10,
    'user_id' => 101,
    'currency' => 'rub',
    'wallet_type' => 11,
    'wallet_account' => '{"inn":"123456789111","account":"11111111111111555555","bank_name":"Bank","kor":"10000000000000000000","bik":"123456789","ip":"120123552121212","nds":"1"}',
  ],
  'u101_Juridical_person' => [
    'id' => 11,
    'user_id' => 101,
    'currency' => 'rub',
    'wallet_type' => 12,
    'wallet_account' => '{"company_name":"Company","actual_address":"address","juridical_address":"address2","ogrn":"1235556554545 \u0432","okved":"52., 63","scan_tax_registration":"","scan_tax_registration_file":"","scan_ogrn_registration":"","scan_ogrn_registration_file":"","ceo":"Vasua","inn":"125454545455","kpp":"123456789","account":"11111111111111555555","bank_name":"Bank","kor":"11111111111111555111","bik":"123456789","ip":"120123552121212","nds":"1"}',
  ],
  'u101_qiwi' => [
    'id' => 12,
    'user_id' => 101,
    'currency' => 'rub',
    'wallet_type' => 13,
    'wallet_account' => '{"phone_number":"+71234567891"}',
  ],
  'u101_webmoney_usd' => [
    'id' => 13,
    'user_id' => 101,
    'currency' => 'usd',
    'wallet_type' => 1,
    'wallet_account' => '{"wallet":"Z123456789000"}'
  ],
];
