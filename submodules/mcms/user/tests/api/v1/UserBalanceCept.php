<?php

use mcms\user\tests\ApiTester;

/** @var \Codeception\Scenario $scenario */

$I = new ApiTester($scenario);
$I->wantTo('Get user balance');

$data['email'] = 'test_user_1@mail.ru';
$data['pass'] = 'test_user_1';

$I->setHeader('Content-Type', 'application/json');
$I->sendPOST('users/user/auth/', json_encode($data));
$I->seeResponseCodeIs(200);

$response = json_decode($I->grabResponse());
Yii::$app->db->createCommand()->truncateTable('partner_country_unhold')->execute();
Yii::$app->db->createCommand()->truncateTable('currency_log')->execute();
Yii::$app->cache->flush();

/**
 * балансы из фикстур user_balance_invoices и user_balances_grouped_by_day из модуля payments.
 * @see \mcms\user\tests\Helper\FixtureHelper::fixtures()
 */
$I->sendGET('users/user/balance/', ['access-token' => $response->data->access_token]);
$I->seeResponseContainsJson(['success' => true, 'data' => [
  'currency' => 'rub',
  'main' => 5166.66,
  'today' => 44.44,
  'hold' => 200.00
]]);
$I->seeResponseMatchesJsonType([
  'data' => [
    'currency' => 'string',
    'main' => 'float|integer',
    'today' => 'float|integer',
    'hold' => 'float|integer',
  ]]);
