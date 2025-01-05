<?php

use mcms\user\tests\ApiTester;


/** @var \Codeception\Scenario $scenario */

$I = new ApiTester($scenario);

$I->wantTo('auth user with wrong pass');
$I->sendPOST('users/user/auth/', ['email' => 'test_user_1@mail.r', 'pass' => 'wrong pass']);
$I->seeResponseCodeIs(200);
$I->seeResponseContainsJson(['success' => true, 'data' => false]);

$I->sendPOST('users/user/auth/', ['email' => 'test_user_1@mail.ru']);
$I->seeResponseCodeIs(200);
$I->seeResponseContainsJson(['success' => false]);