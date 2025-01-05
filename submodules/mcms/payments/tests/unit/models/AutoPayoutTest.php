<?php

namespace mcms\payments\tests\unit\models;

use Codeception\Util\Stub;
use mcms\common\codeception\TestCase;
use mcms\payments\models\AutoPayout;
use mcms\payments\models\UserPayment;
use mcms\payments\models\UserPaymentForm;
use mcms\payments\models\UserPaymentSetting;
use mcms\payments\models\wallet\Wallet;
use mcms\payments\tests\traits\PaymentsTrait;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use rgk\payprocess\components\handlers\AbstractPayHandler;
use rgk\payprocess\components\PayoutService;
use rgk\payprocess\components\serviceResponse\PayoutPayResponse;

class AutoPayoutTest extends TestCase
{
  use PaymentsTrait;

  const WEBMONEY_WALLET_ID = 1;
  const PARTNER_ID = 101;

  public function _fixtures()
  {
    return $this->convertFixtures([
      'payments.user_wallets',
      'payments.user_payment_settings',
      'payments.wallets',
      'payments.payment_systems_api',
    ]);
  }

  protected function setUp()
  {
    parent::setUp();

    UserPayment::deleteAll();

    // Привязка отправителя к ПС получателя
    $wallet = Wallet::findOne(1);
    $wallet->rub_sender_api_id = 1;
    $wallet->update();

    // Заглушение PayoutService
    \Yii::$container->set(PayoutService::class, function () {
      return Stub::make(PayoutService::class, ['pay' => new PayoutPayResponse(['status' => AbstractPayHandler::STATUS_COMPLETED])]);
    });

    // Проверка успешной выплаты проводится перед каждым тестом, что бы убедится, что условия теста не нарушены другими проблемами
    $this->loginAsRoot();
    $this->testSuccessInternal();
  }

  /**
   * Проверка применения правила @see \mcms\payments\components\rbac\AutoPayoutRule.
   * Реселлер может выплачить только партнерам
   */
  public function testPermissions()
  {
    $this->loginAsReseller();

    $this->assertFalse(
      (new AutoPayout($this->createPayment(4)))->pay(),
      'Реселлер не должен иметь возможность выплачивать кому-либо, кроме партнеров');

    $this->assertTrue(
      (new AutoPayout($this->createPayment(self::PARTNER_ID)))->pay(),
      'Реселлер должен иметь возможность выплачивать партнерам'
    );
  }

  /**
   * Проверка статуса выплаты
   */
  public function testIsPayable()
  {
    $this->assertFalse(
      (new AutoPayout($this->createPaymentWithStatus(self::PARTNER_ID, ['status' => UserPayment::STATUS_COMPLETED])))->pay(),
      'Выплата не должна пройти, так как статус выплты "completed"'
    );
    $this->assertFalse(
      (new AutoPayout($this->createPaymentWithStatus(self::PARTNER_ID, ['status' => UserPayment::STATUS_PROCESS])))->pay(),
      'Выплата не должна пройти, так как статус выплты "process"'
    );
  }

  /**
   * Проверка отключения выплат в настройках пользователя
   */
  public function testPaymentsDisable()
  {
    $this->loginById(static::PARTNER_ID);
    // Выключение возможности выплат в настроках пользователя-получателя
    $settings = UserPaymentSetting::findOne(['user_id' => static::PARTNER_ID]);
    $settings->is_disabled = 1;
    $settings->update();

    $this->assertFalse(
      (new AutoPayout($this->createPayment(self::PARTNER_ID, [], UserPaymentForm::SCENARIO_CREATE)))->pay(),
      'Выплата не должна пройти, так как выплаты для указанного пользователя отключены'
    );
  }

  /**
   * Проверка успешной автовыплаты.
   * Выполняется перед каждым методом теста
   */
  private function testSuccessInternal()
  {
    $this->assertTrue(
      (new AutoPayout($this->createPayment(self::PARTNER_ID)))->pay(),
      'Не удалось выполнить выплату'
    );
  }

  /**
   * Создание выплаты для тестов
   * @param int $userId Получатель
   * @param array $params Дополнительные параметры
   * @return UserPaymentForm
   * @throws \Exception
   */
  private function createPayment($userId, $params = [], $scenario = null)
  {
    $this->resetBalance(1000, 'rub', $userId);
    $scenario = $scenario ?: ($userId == 4)
      ? UserPaymentForm::SCENARIO_CREATE_RESELLER_PAYMENT
      : UserPaymentForm::SCENARIO_ADMIN_CREATE;

    $paymentPartner = new UserPaymentForm([
      'scenario' => $scenario
    ]);
    $paymentPartner->user_id = $userId;
    $paymentPartner->invoice_amount = 105; // там лимит 100р с учетом процентов
    $paymentPartner->from_date = date('Y-m-d');
    $paymentPartner->to_date = date('Y-m-d');
    $paymentPartner->status = UserPaymentForm::STATUS_AWAITING;
    $paymentPartner->currency = 'rub';
    $paymentPartner->user_wallet_id = self::WEBMONEY_WALLET_ID;
    $paymentPartner->wallet_type = self::WEBMONEY_WALLET_ID;
    $paymentPartner->setAttributes($params);
    $paymentPartner->save();

    return $paymentPartner;
  }

  /**
   * Создание выплаты для тестов + редактирование ее (для изменения статуса)
   * @param int $userId Получатель
   * @param array $params Дополнительные параметры
   * @return UserPaymentForm
   * @throws \Exception
   */
  private function createPaymentWithStatus($userId, $params = [])
  {
    $paymentPartner = $this->createPayment($userId, $params);
    $paymentPartner->setAttributes($params);
    $paymentPartner->save();

    return $paymentPartner;
  }
}