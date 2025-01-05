<?php
namespace mcms\payments\tests;

use mcms\common\codeception\TestCase;
use mcms\payments\models\UserPayment;
use mcms\payments\models\UserPaymentForm;
use Yii;

/**
 * Тест выплат, подсчет процентов и т.д. без проверки
 *
 * TRICKY фикстура user_balance_invoices не должна содержать для юзера 101 инвойсов в иностранной валюте!
 *
 */
class InvoiceAmountTest extends TestCase
{
  const USER = 101;

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
    parent::setUp();
    Yii::$app->db->createCommand("DELETE FROM user_payments")->execute();
    Yii::$app->db->createCommand("DELETE FROM user_balances_grouped_by_day")->execute();
    Yii::$app->cache->flush();
  }

  public function testAdminPayments()
  {
    $this->loginAsRoot();

    $this->createPayment(1, 1, 'rub', 194, 'WebMoney amount рассчитан неверно');
    $this->createPayment(3, 2, 'rub', 197, 'Yandex.Money invoice_amount рассчитан неверно');
    $this->createPayment(4, 3, 'rub', 190, 'Epayments invoice_amount рассчитан неверно');
    $this->createPayment(6, 5, 'rub', 194, 'Paypal invoice_amount рассчитан неверно');
    $this->createPayment(7, 6, 'rub', 194, 'Paxum invoice_amount рассчитан неверно');
    $this->createPayment(8, 7, 'rub', 194, 'WireIban invoice_amount рассчитан неверно');

    $this->createPayment(9, 10, 'rub', 194, 'Card invoice_amount рассчитан неверно');
    $this->createPayment(10, 11, 'rub', 212, 'Private person invoice_amount рассчитан неверно');
    $this->createPayment(11, 12, 'rub', 216, 'Juridical person invoice_amount рассчитан неверно');
    $this->createPayment(12, 13, 'rub', 203, 'Qiwi invoice_amount рассчитан неверно');
  }

    /**
     * @param $walletId integer Id кошелька
     * @param $wallet_type
     * @param $currency string Валюта
     * @param $actual integer Ожидаемое значение invoice_amount
     * @param $message string Сообщение об ошибке
     */
  private function createPayment($walletId, $wallet_type, $currency, $actual, $message)
  {
    $model = new UserPaymentForm(['scenario' => UserPaymentForm::SCENARIO_ADMIN_CREATE]);
    $model->user_id = self::USER;
    $model->invoice_amount = 200;
    $model->from_date = date('Y-m-d');
    $model->to_date = date('Y-m-d');
    $model->status = UserPaymentForm::STATUS_PROCESS;
    $model->currency = $currency;
    $model->user_wallet_id = $walletId;
    $model->wallet_type = $wallet_type;
    $model->save();

    $result = UserPayment::findOne($model->id);

    // Проверка результата выплаты
    $this->assertNotNull($result, $message);

    // Проверка пользователя
    $this->assertEquals(self::USER, $result->user_id, $message . ' (user_id)');

    // Проверка кошельков
    $this->assertEquals($wallet_type, $result->wallet_type, $message . ' (wallet_type)');

    // Проверка валюты
    $this->assertEquals($currency, $result->currency, $message . ' (currency)');

    // Проверка снятой суммы
    $this->assertEquals(200, $result->invoice_amount, $message . ' (invoice_amount)');

    // Проверка выплаченной суммы
    $this->assertEquals($actual, $result->amount, $message . ' (amount)');
  }

}
