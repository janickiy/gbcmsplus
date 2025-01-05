<?php

namespace mcms\payments\tests\models;

use mcms\common\codeception\TestCase;
use mcms\payments\models\UserPaymentForm;
use Yii;
use yii\base\Exception;

class UserPaymentConvertTest extends TestCase
{

  public function _fixtures()
  {
    return $this->convertFixtures([
      'payments.wallets',
      'payments.user_wallets',
      'payments.user_payment_settings',
      'payments.user_balance_invoices',
      'users.users',
    ]);
  }

  protected function setUp()
  {
    $this->loginAsRoot();
    Yii::$app->db->createCommand("DELETE FROM user_payments")->execute();
    parent::setUp();
  }

//  public function testPayment()
//  {
//    $payment = new UserPaymentForm;
//    $payment->user_id = 101;
//    $payment->user_wallet_id = 13;
//    $payment->invoice_amount = 100;
//
//    $payment->save();
//    var_dump($payment->getErrors()); exit;
//
//  }
//
//  public function testPaymentWithConvert()
//  {
//
//  }
}