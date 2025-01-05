<?php
namespace mcms\payments\tests\unit;
use DateTime;
use mcms\common\codeception\TestCase;
use mcms\currency\components\PartnerCurrenciesProvider;
use mcms\payments\components\UserBalance;
use mcms\payments\models\UserPayment;
use mcms\payments\models\UserPaymentForm;
use mcms\payments\models\wallet\Wallet;
use mcms\payments\tests\traits\PaymentsTrait;
use Yii;
use yii\db\Query;

/**
 * Тест основной модели UserPayment для создания выплат
 */
class UserPaymentTest extends TestCase
{
  use PaymentsTrait;

  const USER_ID = 101;
  const WALLET_ID = 1;
  const WALLET_USD_ID = 13;

  public function _fixtures()
  {
    return $this->convertFixtures([
      'payments.user_wallets',
      'payments.user_payment_settings',
    ]);
  }

  /**
   * @inheritdoc
   */
  protected function setUp()
  {
    parent::setUp();
    $this->loginById(self::USER_ID);

    // Удаление всех выплат
    UserPayment::deleteAll();

    // Установка баланса
    $this->resetBalance(100000);
    Yii::$app->cache->flush();
  }

  /**
   * @throws \Throwable
   * @throws \yii\db\StaleObjectException
   */
  public function testMaxLimit()
  {
    $this->updateWallet(500, 1000, 1000000, 1000000);

    $this->assertTrue($this->createPayment(1030.92), 'Не удалось создать выплату не превышающую лимит');
    $this->assertFalse($this->createPayment(1030.94), 'Выплата превышающая лимит создалась');
  }

  /**
   * @throws \Throwable
   * @throws \yii\db\StaleObjectException
   */
  public function testBalance()
  {
    $this->updateWallet(500, 1000, 1000000, 1000000);

    $this->createPayment(1000);
    $balance = $this->getUserBalance();
    $this->assertEquals(99000, $balance, 'Баланс партнера неверный');
  }

  /**
   * @throws \Throwable
   * @throws \yii\db\StaleObjectException
   */
  public function testMinLimit()
  {
    $this->updateWallet(500, 1000, 1000000, 1000000);

    $this->assertFalse($this->createPayment(499), 'Выплата меньше минимальной создалась');
  }

  /**
   * Суточный лимит
   * @throws \Throwable
   * @throws \yii\db\StaleObjectException
   */
  public function testLimitDaily()
  {
    $this->updateWallet(500, 1000000, 2999, 1000000, 0);

    $this->assertTrue($this->createPayment(1000), 'Не удалось создать выплату не превышающую дневной лимит');
    $this->assertTrue($this->createPayment(1000), 'Не удалось создать выплату не превышающую дневной лимит');
    $this->assertFalse($this->createPayment(1000), 'Выплата превышающая дневной лимит создалась');
    $this->assertTrue($this->createPayment(999), 'Не удалось создать выплату не превышающую дневной лимит');
  }

  /**
   * Месячный лимит
   * @throws \Throwable
   * @throws \yii\db\StaleObjectException
   */
  public function testLimitMonthly()
  {
    $this->updateWallet(500, 1000000, 1000000, 2999, 0);

    $this->assertTrue($this->createPayment(1000), 'Не удалось создать выплату не превышающую месячный лимит');
    $this->assertTrue($this->createPayment(1000), 'Не удалось создать выплату не превышающую месячный лимит');
    $this->assertFalse($this->createPayment(1000), 'Выплата превышающая месячный лимит создалась');
    $this->assertTrue($this->createPayment(999), 'Не удалось создать выплату не превышающую дневной лимит');
  }

  /**
   * Проверка выплаты с конвертацией
   * @throws \Throwable
   * @throws \yii\base\InvalidConfigException
   * @throws \yii\db\StaleObjectException
   */
  public function testConvertedPayments()
  {
    $this->updateWallet(500, 1000000, 1000000, 2999, 0);

    $this->assertTrue($this->createPayment(1000, self::WALLET_USD_ID));

    $lastPayment = $this->getLastPayment();
    $usdAmount = PartnerCurrenciesProvider::getInstance()
      ->getCurrencies()
      ->getCurrency('rub')
      ->convert(1000, 'usd');

    $this->assertEquals(round($usdAmount, 2), $lastPayment['amount'], 'Сконвертированная сумма неверная');
    $this->assertEquals(1000, $lastPayment['invoice_amount'], 'Исходная сумма неверная');
  }

  /**
   * Создать выплату
   * @param $invoiceAmount
   * @param int $userWalletId
   * @return bool
   * @throws \yii\base\InvalidConfigException
   */
  private function createPayment($invoiceAmount, $userWalletId = self::WALLET_ID)
  {
    $datetime = new DateTime;
    $time = $datetime->getTimestamp();

    $payment = new UserPaymentForm([
      'user_id' => static::USER_ID,
      'user_wallet_id' => $userWalletId,
      'invoice_amount' => $invoiceAmount,
      'status' => UserPayment::STATUS_AWAITING,
      'created_at' => $time,
      'from_date' => Yii::$app->formatter->asGridDate($time),
      'to_date' => Yii::$app->formatter->asGridDate($time),
      'processing_type' => UserPayment::TYPE_MANUAL
    ]);
    $payment->scenario = UserPayment::SCENARIO_ADMIN_CREATE;

    $result = $payment->save();
//    if (!$result) { // do not delete, uncomment to debug
//      var_dump($payment->getErrors()); exit;
//    }

    return $result;
  }

  /**
   * @return array|bool
   */
  private function getLastPayment()
  {
    return (new Query())->select('*')->from('user_payments')
      ->orderBy(['id' => SORT_DESC])
      ->one();
  }

  /**
   * Установка специфических лимитов для удобного тестирования
   * @param $rubMinPayoutSum
   * @param $rubMaxPayoutSum
   * @param $rubPayoutLimitDaily
   * @param $rubPayoutLimitMonthly
   * @param int $profitPercent
   * @throws \Throwable
   * @throws \yii\db\StaleObjectException
   */
  private function updateWallet($rubMinPayoutSum, $rubMaxPayoutSum, $rubPayoutLimitDaily, $rubPayoutLimitMonthly, $profitPercent = -3)
  {
    $wallet = Wallet::findOne(1);
    $wallet->rub_min_payout_sum = $rubMinPayoutSum;
    $wallet->rub_max_payout_sum = $rubMaxPayoutSum;
    $wallet->rub_payout_limit_daily = $rubPayoutLimitDaily;
    $wallet->rub_payout_limit_monthly = $rubPayoutLimitMonthly;
    $wallet->profit_percent = $profitPercent;
    $wallet->update();
  }

  /**
   * @return float
   * @throws \mcms\payments\components\exceptions\UserBalanceException
   */
  private function getUserBalance()
  {
    $balance = new UserBalance(['userId' => self::USER_ID, 'currency' => 'rub']);
    return $balance->getBalance(false);
  }
}