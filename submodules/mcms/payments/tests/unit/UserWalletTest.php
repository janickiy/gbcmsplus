<?php
namespace mcms\payments\tests;

use mcms\common\codeception\TestCase;
use mcms\payments\models\UserPaymentSetting;
use mcms\payments\models\UserWallet;
use mcms\payments\models\wallet\AbstractWallet;
use mcms\payments\models\wallet\Wallet;
use Yii;

class UserWalletTest extends TestCase
{
  const USER = 101;
  const WEBMONEY = 1;
  const YANDEX = 2;
  const EPAYMENTS = 3;
  const PAYPAL = 5;
  const PAXUM = 6;
  const WIREIBAN = 7;
  const CARD = 10;
  const PRIVATE_PERSON = 11;
  const JURIDICAL_PERSON = 12;
  const QIWI = 13;

  public function _fixtures()
  {
    return $this->convertFixtures([
      'payments.wallets',
      'payments.user_payment_settings',
    ]);
  }

  protected function setUp()
  {
    parent::setUp();
    Yii::$app->db->createCommand("DELETE FROM user_wallets")->execute();
  }


  public function testAddWallet()
  {
    $model = $this->createWallet(self::USER, self::WEBMONEY, ['wallet' => 'R123456789000'], 'rub');
    $this->assertNotNull($model->id, 'Создание кошелека вебмани');
    $model = $this->createWallet(self::USER, self::WEBMONEY, ['wallet' => 'XXXX'], 'rub');
    $this->assertNull($model->id, 'Создание неправильного кошелька вебмани');
    $model = $this->createWallet(self::USER, self::WEBMONEY, [], 'rub');
    $this->assertNull($model->id, 'Создание пустого кошелька вебмани');

    $model = $this->createWallet(self::USER, self::YANDEX, ['wallet' => 'yandex@ya.ru'], 'rub');
    $this->assertNotNull($model->id, 'Создание кошелька Яндекс');
    $model = $this->createWallet(self::USER, self::YANDEX, ['wallet' => ''], 'rub');
    $this->assertNull($model->id, 'Создание незаполненного кошелька Яндекс');

    $model = $this->createWallet(self::USER, self::EPAYMENTS, ['wallet' => '000-123456'], 'usd');
    $this->assertNotNull($model->id, 'Создание кошелека Epayments');
    $model = $this->createWallet(self::USER, self::EPAYMENTS, ['wallet' => '000'], 'usd');
    $this->assertNull($model->id, 'Создание неправильного кошелька Epayments');
    $model = $this->createWallet(self::USER, self::EPAYMENTS, [], 'usd');
    $this->assertNull($model->id, 'Создание пустого кошелька Epayments');

    $model = $this->createWallet(self::USER, self::PAYPAL, ['name' => 'Ivan Ivanov', 'email' => 'ivan@gmail.com'], 'usd');
    $this->assertNotNull($model->id, 'Создание кошелека Paypal');
    $model = $this->createWallet(self::USER, self::PAYPAL, ['name' => 'Ivan', 'email' => 'ivan@gmail.com'], 'usd');
    $this->assertNull($model->id, 'Создание кошелька Paypal c неверным именем');
    $model = $this->createWallet(self::USER, self::PAYPAL, ['name' => 'Ivan Ivanov', 'email' => 'ivan'], 'usd');
    $this->assertNull($model->id, 'Создание кошелька Paypal c неверным email');
    $model = $this->createWallet(self::USER, self::PAYPAL, [], 'usd');
    $this->assertNull($model->id, 'Создание пустого кошелька Paypal');

    $model = $this->createWallet(self::USER, self::PAXUM, ['email' => 'ivan@gmail.com'], 'usd');
    $this->assertNotNull($model->id, 'Создание кошелека Paxum');
    $model = $this->createWallet(self::USER, self::PAXUM, ['email' => 'ivan'], 'usd');
    $this->assertNull($model->id, 'Создание кошелька Paxum c неверным email');
    $model = $this->createWallet(self::USER, self::PAXUM, [], 'usd');
    $this->assertNull($model->id, 'Создание пустого кошелька Paxum');

    $model = $this->createWallet(self::USER, self::CARD, ['bank_name' => 'Sberbank LTD', 'card_number' => '1111222233334444', 'cardholder_name' => 'Vasya Petrov'], 'usd');
    $this->assertNotNull($model->id, 'Создание кошелька Карта');
    $model = $this->createWallet(self::USER, self::CARD, ['bank_name' => 'Sberbank LTD', 'card_number' => '1111'], 'usd');
    $this->assertNull($model->id, 'Создание неправильного кошелька Карта');
    $model = $this->createWallet(self::USER, self::CARD, ['card_number' => ''], 'usd');
    $this->assertNull($model->id, 'Создание незаполненного кошелька Карта');

    $model = $this->createWallet(self::USER, self::PRIVATE_PERSON, [
      'ip_name' => 'И.И. Иванов',
      'juridical_address' => 'Россия, г. Москва, ул Ленина 10a-2, 420192',
      'actual_address' => 'Россия, г. Москва, ул Ленина 10a-2, 420192',
      'phone_number' => '+79876543210',
      'email' => 'ivanov@example.com',
      'inn' => '583504135303',
      'ogrn' => '102774537290723',
      'ogrn_date' => '2017-09-20',
      'ip_certificate_number' => '301018102000000',
      'account' => '40802810029170000709',
      'bank_name' => 'Sberbank LTD',
      'bik' => '042202824',
      'kor' => '30101810200000000824',
    ], 'rub');
    $this->assertNotNull($model->id, 'Создание кошелька ИП');

    $model = $this->createWallet(self::USER, self::JURIDICAL_PERSON, [
      'company_name' => 'ООО "Компания"',
      'ceo' => 'Иванов Иван Иванович',
      'juridical_address' => 'Россия, г. Москва, ул Ленина 10a-2, 420192',
      'actual_address' => 'Россия, г. Москва, ул Ленина 10a-2, 420192',
      'phone_number' => '+79876543210',
      'email' => 'ivanov@example.com',
      'inn' => '583504135303',
      'kpp' => '773601001',
      'ogrn' => '102774537290723',
      'ogrn_date' => '2017-09-20',
      'account' => '40802810029170000709',
      'bank_name' => 'Sberbank LTD',
      'bik' => '042202824',
      'kor' => '30101810200000000824',
    ], 'rub');

    $this->assertNotNull($model->id, 'Создание кошелька Юр. лица');

    $model = $this->createWallet(self::USER, self::QIWI, ['phone_number' => '+79997776655'], 'rub');
    $this->assertNotNull($model->id, 'Создание кошелека QIWI');
    $model = $this->createWallet(self::USER, self::QIWI, ['phone_number' => '812312123123'], 'rub');
    $this->assertNull($model->id, 'Создание неправильного кошелька QIWI');
    $model = $this->createWallet(self::USER, self::QIWI, [], 'rub');
    $this->assertNull($model->id, 'Создание пустого кошелька QIWI');
  }


  /**
   * @param $userId integer Id юзера
   * @param $walletType integer тип кошелька
   * @param $attributes array атрибуты кошелька
   *
   * @return UserWallet
   */
  private function createWallet($userId, $walletType, $attributes, $currency)
  {
    $model = new UserWallet(['user_id' => $userId, 'currency' => $currency]);
    $model->wallet_type = $walletType;

    /**
     * @var $walletAccount AbstractWallet
     */
    $walletAccount = Wallet::getObject($model->wallet_type, $attributes);

    $model->wallet_account = (string)$walletAccount;

    if ($model->validate() && $walletAccount->validate()) {
      $model->save();
    }

    return $model;
  }

}