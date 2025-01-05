<?php

use mcms\user\tests\ApiTester;


/** @var \Codeception\Scenario $scenario */

$I = new ApiTester($scenario);
$I->wantTo('auth user');


$data['email'] = 'test_user_1@mail.ru';
$data['pass'] = 'test_user_1';

$I->setHeader('Content-Type', 'application/json');
$I->sendPOST('users/user/auth/', json_encode($data));
$I->seeResponseCodeIs(200);

$response =  json_decode($I->grabResponse());
$I->seeResponseContainsJson(['success' => true, 'data' => ['access_token' => $response->data->access_token]]);

$I->sendGET('users/user/index/');
$I->seeResponseContainsJson(['success' => false]);

$I->sendGET('users/user/index/', ['access-token' => $response->data->access_token]);
$I->seeResponseContainsJson(['success' => true]);
