<?php

namespace mcms\payments\tests\models;

use mcms\common\codeception\TestCase;
use mcms\common\web\User;
use mcms\payments\models\UserBalanceInvoice;
use mcms\payments\models\UserPayment;
use mcms\payments\models\UserPaymentForm;
use Yii;

/**
 * Class UserPaymentTest
 * @package mcms\payments\tests\models
 */
class UserPaymentTest extends TestCase
{
  const USER = 101;

  protected function setUp()
  {
    parent::setUp();
    Yii::$app->db->createCommand("DELETE FROM user_payments")->execute();
    Yii::$app->db->createCommand("DELETE FROM user_balances_grouped_by_day")->execute();
    Yii::$app->cache->flush();
  }

  public function _before()
  {
    $this->loginAsRoot();

    parent::_before();
  }

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

  public function testCreatePayment()
  {
    $paymentAmount = 105; // там лимит 100р с учетом процентов;

    $userPayment = $this->createPayment(1, 1, 'rub', $paymentAmount);

    // запрашиваем из базы еще раз для проверки
    $payment = UserPayment::findOne($userPayment->id);
    // проверяем что выплата создалась
    $this->assertEquals(UserPayment::STATUS_AWAITING, $payment->status);
    $this->assertEquals(-$paymentAmount, $this->getSumInvoices($payment));
  }

  public function testCancelPayment()
  {
    $paymentAmount = 105; // там лимит 100р с учетом процентов;

    $userPayment = $this->createPayment(1, 1, 'rub', $paymentAmount);

    // отменяем
    $result = $userPayment->cancel('blabla');

    $this->assertTrue($result);

    // запрашиваем из базы еще раз для проверки
    $payment = UserPayment::findOne($userPayment->id);

    $this->assertEquals(UserPayment::STATUS_CANCELED, $payment->status);
    $this->assertEquals('blabla', $payment->description);
    $this->assertEquals(UserPayment::PROCESSING_TYPE_SELF, $payment->processing_type);
    $this->assertEquals(0, $this->getSumInvoices($payment));
  }

  public function testAnnulPayment()
  {
    $paymentAmount = 105; // там лимит 100р с учетом процентов;

    $userPayment = $this->createPayment(1, 1, 'rub', $paymentAmount);

    // отменяем
    $result = $userPayment->annul('blabla');

    $this->assertTrue($result);


    // запрашиваем из базы еще раз для проверки
    $payment = UserPayment::findOne($userPayment->id);

    $this->assertEquals(UserPayment::STATUS_ANNULLED, $payment->status);
    $this->assertEquals('blabla', $payment->description);
    $this->assertEquals(UserPayment::PROCESSING_TYPE_SELF, $payment->processing_type);

    // за анулированную выплату не возращаются деньги
    $this->assertEquals(-$paymentAmount, $this->getSumInvoices($payment));
  }

  public function testManualPayment()
  {
    $paymentAmount = 105; // там лимит 100р с учетом процентов;

    $userPayment = $this->createPayment(1, 1, 'rub', $paymentAmount);

    // помечаем выплаченной в ручную
    $result = $userPayment->updateProcessToManual();

    $this->assertTrue($result);

    // запрашиваем из базы еще раз для проверки
    $payment = UserPayment::findOne($userPayment->id);

    $this->assertEquals(UserPayment::STATUS_COMPLETED, $payment->status);
    $this->assertEquals(UserPayment::PROCESSING_TYPE_SELF, $payment->processing_type);

    // ивнойс остается
    $this->assertEquals(-$paymentAmount, $this->getSumInvoices($payment));
  }

  public function testSendPaymentToExternalProcessingResellerBalanceError()
  {
    $paymentAmount = 105; // там лимит 100р с учетом процентов;

    $userPayment = $this->createPayment(1, 1, 'rub', $paymentAmount);

    // помечаем выплаченной в ручную
    $result = $userPayment->sendProcessToExternal();

    $this->assertFalse($result);
  }

  public function testSendPaymentToExternalProcessing()
  {
    $resellerId = UserPayment::getResellerId();
    $paymentAmount = 105; // там лимит 100р с учетом процентов;

    // делаем реселлеру компенсацию, иначе нельзя отправить на внешнюю выплату
    $this->createCompensation($resellerId, 9999);

    $userPayment = $this->createPayment(1, 1, 'rub', $paymentAmount);
    $userPayment->scenario = UserPayment::SCENARIO_SEND_TO_EXTERNAL;
    // помечаем отправленной на внешнюю обработку
    $result = $userPayment->sendProcessToExternal();

    $this->assertTrue($result);

    // запрашиваем из базы еще раз для проверки
    $payment = UserPayment::findOne($userPayment->id);

    $this->assertEquals(UserPayment::STATUS_PROCESS, $payment->status);
    $this->assertEquals(UserPayment::PROCESSING_TYPE_EXTERNAL, $payment->processing_type);

    // ивнойс остается
    $this->assertEquals(-$paymentAmount, $this->getSumInvoices($payment));
    // доп инвойс для реса
    $this->assertEquals(-102.9, $this->getSumInvoices($payment, $resellerId));
  }

  public function testHandleProcessExternal()
  {
    $resellerId = UserPayment::getResellerId();
    $paymentAmount = 105; // там лимит 100р с учетом процентов;

    // делаем реселлеру компенсацию, иначе нельзя отправить на внешнюю выплату
    $this->createCompensation($resellerId, 9999);

    $userPayment = $this->createPayment(1, 1, 'rub', $paymentAmount);

    // помечаем отправленной на внешнюю обработку
    $userPayment->scenario = UserPayment::SCENARIO_SEND_TO_EXTERNAL;
    $result = $userPayment->sendProcessToExternal();

    // далее эмуляция внешней обработки которая происходит в кроне
    // а именно там меняется статус добавляется описание и дата выплаты
    $userPayment->status = UserPayment::STATUS_ERROR;
    $userPayment->description = 'external processed';
    $userPayment->payed_at = time();

    // обрабатываем как внешнюю
    $userPayment->handleExternalProcess();

    // запрашиваем из базы еще раз для проверки
    $payment = UserPayment::findOne($userPayment->id);

    $this->assertEquals(UserPayment::STATUS_ERROR, $payment->status);
    $this->assertEquals(UserPayment::PROCESSING_TYPE_EXTERNAL, $payment->processing_type);

    // ивнойс партнера не обнуляется
    $this->assertEquals(-$paymentAmount, $this->getSumInvoices($payment));
    // доп инвойс для реса тоже обнуляется
    $this->assertEquals(0, $this->getSumInvoices($payment, $resellerId));
  }

  public function testUpdateStatusToProcessApi()
  {
    $paymentAmount = 105; // там лимит 100р с учетом процентов;

    $userPayment = $this->createPayment(1, 1, 'rub', $paymentAmount);

    // помечаем выплаченной в ручную
    $userPayment->setStatusToProcessApi();

    $this->assertTrue($userPayment->save());

    // запрашиваем из базы еще раз для проверки
    $payment = UserPayment::findOne($userPayment->id);

    $this->assertEquals(UserPayment::STATUS_PROCESS, $payment->status);
    $this->assertEquals(UserPayment::PROCESSING_TYPE_API, $payment->processing_type);

    // ивнойс остается
    $this->assertEquals(-$paymentAmount, $this->getSumInvoices($payment));
  }

  private function getSumInvoices(UserPayment $payment, $userId = null)
  {
    $userId = $userId ?: $payment->user_id;

    $invoices = UserBalanceInvoice::find()->where(['user_payment_id' => $payment->id, 'user_id' => $userId])->each();

    $sumInvoices = 0;
    foreach ($invoices as $invoice) {
      /** @var UserBalanceInvoice $invoice */
      $sumInvoices += $invoice->amount;
    }

    return $sumInvoices;
  }

  /**
   * @param $walletId integer Id кошелька
   * @param $wallet_type integer Тип кошелька
   * @param $currency string Валюта
   * @param $amount float Сумма выплаты
   * @return UserPaymentForm
   */
  private function createPayment($walletId, $wallet_type, $currency, $amount)
  {
    $model = new UserPaymentForm(['scenario' => UserPaymentForm::SCENARIO_ADMIN_CREATE]);
    $model->user_id = self::USER;
    $model->invoice_amount = $amount;
    $model->from_date = date('Y-m-d');
    $model->to_date = date('Y-m-d');
    $model->status = UserPaymentForm::STATUS_AWAITING;
    $model->currency = $currency;
    $model->user_wallet_id = $walletId;
    $model->wallet_type = $wallet_type;
    $model->processing_type = UserPayment::PROCESSING_TYPE_SELF;
    $model->save();

    return $model;
  }

  private function createCompensation($userId, $amount, $currency = 'rub')
  {
    return (new UserBalanceInvoice([
      'scenario' => UserBalanceInvoice::SCENARIO_COMPENSATION,
      'user_id' => $userId,
      'currency' => $currency,
      'amount' => $amount,
      'description' => 'compensation',
    ]))->save();
  }
}
