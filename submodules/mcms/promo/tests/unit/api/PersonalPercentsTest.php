<?php
namespace mcms\promo\tests\unit\api;

use mcms\common\codeception\TestCase;
use mcms\promo\models\PersonalProfit;
use mcms\promo\Module;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class PersonalPercentsTest
 * @package mcms\promo\tests\unit\api
 */
class PersonalPercentsTest extends TestCase
{

  const INCORRECT_ID = 999;
  const PARTNER_1 = 101;
  const PARTNER_2 = 102;
  const PARTNER_3 = 3;
  const PARTNER_103 = 103;

  public function _fixtures()
  {
    return $this->convertFixtures([
      'promo.personal_profits',
      'promo.landing_operators',
      'users.users',
    ]);
  }

  protected function tearDown()
  {
    Yii::$app->user->logout();
    parent::tearDown();
  }

  protected function setUp()
  {
    parent::setUp();
    Yii::$app->user->logout();

    Yii::$app->cache->flush();
    (new PersonalProfit())->invalidateCache();
    $this->setModuleSetting(Module::SETTINGS_MAIN_REBILL_PERCENT_FOR_PARTNER, 11);
    $this->setModuleSetting(Module::SETTINGS_MAIN_BUYOUT_PERCENT_FOR_PARTNER, 12);
    $this->setPercents();
  }


  public function testPartnerPercents()
  {
    /** @var Module $promo */
    $promo = Yii::$app->getModule('promo');

    // Проверка по юзеру-ленд-оператору
    $profits = $promo->api('personalProfit', ['userId' => self::PARTNER_1, 'operatorId' => 1, 'landingId' => 1])->getResult();
    $this->assertEquals(70, ArrayHelper::getValue($profits, 'rebill_percent'));
    $this->assertEquals(71, ArrayHelper::getValue($profits, 'buyout_percent'));
    $this->assertEquals(72, ArrayHelper::getValue($profits, 'cpa_profit_rub'));
    $this->assertEquals(720, ArrayHelper::getValue($profits, 'cpa_profit_eur'));
    $this->assertEquals(721, ArrayHelper::getValue($profits, 'cpa_profit_usd'));
    $this->assertEquals(123123, ArrayHelper::getValue($profits, 'updated_at'));

    // Проверка по юзеру-ленду
    $profits = $promo->api('personalProfit', ['userId' => self::PARTNER_1, 'operatorId' => self::INCORRECT_ID, 'landingId' => 1])->getResult();
    $this->assertEquals(73, ArrayHelper::getValue($profits, 'rebill_percent'));
    $this->assertEquals(74, ArrayHelper::getValue($profits, 'buyout_percent'));
    $this->assertEquals(75, ArrayHelper::getValue($profits, 'cpa_profit_rub'));
    $this->assertEquals(750, ArrayHelper::getValue($profits, 'cpa_profit_eur'));
    $this->assertEquals(751, ArrayHelper::getValue($profits, 'cpa_profit_usd'));
    $this->assertEquals(123124, ArrayHelper::getValue($profits, 'updated_at'));

    // Проверка по юзеру-оператору
    $profits = $promo->api('personalProfit', ['userId' => self::PARTNER_1, 'operatorId' => 1, 'landingId' => self::INCORRECT_ID])->getResult();
    $this->assertEquals(76, ArrayHelper::getValue($profits, 'rebill_percent'));
    $this->assertEquals(77, ArrayHelper::getValue($profits, 'buyout_percent'));
    $this->assertEquals(78, ArrayHelper::getValue($profits, 'cpa_profit_rub'));
    $this->assertEquals(780, ArrayHelper::getValue($profits, 'cpa_profit_eur'));
    $this->assertEquals(781, ArrayHelper::getValue($profits, 'cpa_profit_usd'));
    $this->assertEquals(123125, ArrayHelper::getValue($profits, 'updated_at'));

    // Проверка по юзеру
    $profits = $promo->api('personalProfit', ['userId' => self::PARTNER_1, 'operatorId' => self::INCORRECT_ID, 'landingId' => self::INCORRECT_ID])->getResult();
    $this->assertEquals(79, ArrayHelper::getValue($profits, 'rebill_percent'));
    $this->assertEquals(80, ArrayHelper::getValue($profits, 'buyout_percent'));
    $this->assertEquals(81, ArrayHelper::getValue($profits, 'cpa_profit_rub'));
    $this->assertEquals(810, ArrayHelper::getValue($profits, 'cpa_profit_eur'));
    $this->assertEquals(811, ArrayHelper::getValue($profits, 'cpa_profit_usd'));
    $this->assertEquals(123126, ArrayHelper::getValue($profits, 'updated_at'));

    // Проверка по ленд-оператору
    $profits = $promo->api('personalProfit', ['userId' => self::PARTNER_2, 'operatorId' => 1, 'landingId' => 1])->getResult();
    $this->assertEquals(82, ArrayHelper::getValue($profits, 'rebill_percent'));
    $this->assertEquals(83, ArrayHelper::getValue($profits, 'buyout_percent'));
    $this->assertEquals(84, ArrayHelper::getValue($profits, 'cpa_profit_rub'));
    $this->assertEquals(840, ArrayHelper::getValue($profits, 'cpa_profit_eur'));
    $this->assertEquals(841, ArrayHelper::getValue($profits, 'cpa_profit_usd'));
    $this->assertEquals(123127, ArrayHelper::getValue($profits, 'updated_at'));

    // Проверка по ленду
    $profits = $promo->api('personalProfit', ['userId' => self::PARTNER_2, 'operatorId' => self::INCORRECT_ID, 'landingId' => 1])->getResult();
    $this->assertEquals(85, ArrayHelper::getValue($profits, 'rebill_percent'));
    $this->assertEquals(86, ArrayHelper::getValue($profits, 'buyout_percent'));
    $this->assertEquals(87, ArrayHelper::getValue($profits, 'cpa_profit_rub'));
    $this->assertEquals(870, ArrayHelper::getValue($profits, 'cpa_profit_eur'));
    $this->assertEquals(871, ArrayHelper::getValue($profits, 'cpa_profit_usd'));
    $this->assertEquals(123128, ArrayHelper::getValue($profits, 'updated_at'));

    // Проверка по оператору
    $profits = $promo->api('personalProfit', ['userId' => self::PARTNER_2, 'operatorId' => 1, 'landingId' => self::INCORRECT_ID])->getResult();
    $this->assertEquals(88, ArrayHelper::getValue($profits, 'rebill_percent'));
    $this->assertEquals(89, ArrayHelper::getValue($profits, 'buyout_percent'));
    $this->assertEquals(90, ArrayHelper::getValue($profits, 'cpa_profit_rub'));
    $this->assertEquals(900, ArrayHelper::getValue($profits, 'cpa_profit_eur'));
    $this->assertEquals(901, ArrayHelper::getValue($profits, 'cpa_profit_usd'));
    $this->assertEquals(123129, ArrayHelper::getValue($profits, 'updated_at'));

    // Проверка глобального
    $profits = $promo->api('personalProfit', ['userId' => self::PARTNER_2, 'operatorId' => self::INCORRECT_ID, 'landingId' => self::INCORRECT_ID])->getResult();
    $this->assertEquals(91, ArrayHelper::getValue($profits, 'rebill_percent'));
    $this->assertEquals(92, ArrayHelper::getValue($profits, 'buyout_percent'));
    $this->assertEquals(93, ArrayHelper::getValue($profits, 'cpa_profit_rub'));
    $this->assertEquals(930, ArrayHelper::getValue($profits, 'cpa_profit_eur'));
    $this->assertEquals(931, ArrayHelper::getValue($profits, 'cpa_profit_usd'));
    $this->assertEquals(123130, ArrayHelper::getValue($profits, 'updated_at'));

    // Проверка конфига модуля
    PersonalProfit::findOne(['user_id' => 0, 'operator_id' => 0, 'landing_id' => 0])->delete();
    $profits = $promo->api('personalProfit', [
      'userId' => self::PARTNER_2,
      'operatorId' => self::INCORRECT_ID,
      'landingId' => self::INCORRECT_ID
    ])->getResult();

    $this->assertEquals(90, ArrayHelper::getValue($profits, 'rebill_percent'));
    $this->assertEquals(90, ArrayHelper::getValue($profits, 'buyout_percent'));
    $this->assertEquals(0, ArrayHelper::getValue($profits, 'cpa_profit_rub'));
    $this->assertEquals(0, ArrayHelper::getValue($profits, 'cpa_profit_eur'));
    $this->assertEquals(0, ArrayHelper::getValue($profits, 'cpa_profit_usd'));
    $this->assertNull(ArrayHelper::getValue($profits, 'updated_at'));
  }

  private function countryPersonalProfitsFixtures()
  {
    return [
      // юзер-ленд-оператор
      'u1o1l1' => [
        'landing_id' => 1,
        'operator_id' => 1,
        'user_id' => 101,
        'rebill_percent' => 70,
        'buyout_percent' => 71,
        'cpa_profit_rub' => 72,
        'cpa_profit_eur' => 720,
        'cpa_profit_usd' => 721,
        'created_by' => 1,
        'created_at' => 111111,
        'updated_at' => 100000
      ],
      //юзеру-ленд-страна
      'u1c1l1' => [
        'landing_id' => 2,
        'country_id' => 1,
        'user_id' => 101,
        'rebill_percent' => 80,
        'buyout_percent' => 81,
        'cpa_profit_rub' => 82,
        'cpa_profit_eur' => 820,
        'cpa_profit_usd' => 821,
        'created_by' => 1,
        'created_at' => 111111,
        'updated_at' => 100001
      ],
      // юезр-провайдер-оператор
      'u1p1l1' => [
        'provider_id' => 1,
        'operator_id' => 1,
        'user_id' => 101,
        'rebill_percent' => 70,
        'buyout_percent' => 80,
        'cpa_profit_rub' => 45,
        'cpa_profit_eur' => 0.780,
        'cpa_profit_usd' => 0.690,
        'created_by' => 1,
        'created_at' => 111111,
        'updated_at' => 100002
      ],
      //юзеру-провайдер-страна
      'u1p1c1' => [
        'provider_id' => 1,
        'country_id' => 1,
        'user_id' => 101,
        'rebill_percent' => 80,
        'buyout_percent' => 81,
        'cpa_profit_rub' => 82,
        'cpa_profit_eur' => 820,
        'cpa_profit_usd' => 821,
        'created_by' => 1,
        'created_at' => 111111,
        'updated_at' => 100003
      ],
      //юзеру-ленду
      'u1l3' => [
        'landing_id' => 1,
        'user_id' => 101,
        'rebill_percent' => 73,
        'buyout_percent' => 74,
        'cpa_profit_rub' => 75,
        'cpa_profit_eur' => 750,
        'cpa_profit_usd' => 751,
        'created_by' => 1,
        'created_at' => 111111,
        'updated_at' => 100004
      ],
      //юзеру-провайдеру TODO
      'u1p3' => [
        'provider_id' => 1,
        'user_id' => 101,
        'rebill_percent' => 73,
        'buyout_percent' => 74,
        'cpa_profit_rub' => 75,
        'cpa_profit_eur' => 750,
        'cpa_profit_usd' => 751,
        'created_by' => 1,
        'created_at' => 111111,
        'updated_at' => 100005
      ],
      //юзеру-оператору
      'u1o1' => [
        'operator_id' => 1,
        'user_id' => 101,
        'rebill_percent' => 76,
        'buyout_percent' => 77,
        'cpa_profit_rub' => 78,
        'cpa_profit_eur' => 780,
        'cpa_profit_usd' => 781,
        'created_by' => 1,
        'created_at' => 111111,
        'updated_at' => 100006
      ],
      //юзеру-страна
      'u1c1' => [
        'country_id' => 1,
        'user_id' => 101,
        'rebill_percent' => 81,
        'buyout_percent' => 82,
        'cpa_profit_rub' => 83,
        'cpa_profit_eur' => 840,
        'cpa_profit_usd' => 851,
        'created_by' => 1,
        'created_at' => 111111,
        'updated_at' => 100007
      ],
      //юзеру
      'u1' => [
        'user_id' => 101,
        'rebill_percent' => 79,
        'buyout_percent' => 80,
        'cpa_profit_rub' => 81,
        'cpa_profit_eur' => 810,
        'cpa_profit_usd' => 811,
        'created_by' => 1,
        'created_at' => 111111,
        'updated_at' => 100008
      ],

      //ленд-оператору
      'o1l1' => [
        'landing_id' => 1,
        'operator_id' => 1,
        'rebill_percent' => 82,
        'buyout_percent' => 83,
        'cpa_profit_rub' => 84,
        'cpa_profit_eur' => 840,
        'cpa_profit_usd' => 841,
        'created_by' => 1,
        'created_at' => 111111,
        'updated_at' => 100009
      ],
      //ленд-страна
      'l2c1' => [
        'landing_id' => 1,
        'operator_id' => 0,
        'country_id' => 1,
        'user_id' => 0,
        'rebill_percent' => 81,
        'buyout_percent' => 82,
        'cpa_profit_rub' => 83,
        'cpa_profit_eur' => 840,
        'cpa_profit_usd' => 851,
        'created_by' => 1,
        'created_at' => 111111,
        'updated_at' => 100010
      ],
      //провайдеру-оператору
      'o1p1' => [
        'provider_id' => 1,
        'operator_id' => 1,
        'rebill_percent' => 82,
        'buyout_percent' => 83,
        'cpa_profit_rub' => 84,
        'cpa_profit_eur' => 840,
        'cpa_profit_usd' => 841,
        'created_by' => 1,
        'created_at' => 111111,
        'updated_at' => 100011
      ],
      //провайдерв-страна
      'l2p1' => [
        'provider_id' => 1,
        'operator_id' => 0,
        'country_id' => 1,
        'user_id' => 0,
        'rebill_percent' => 81,
        'buyout_percent' => 82,
        'cpa_profit_rub' => 83,
        'cpa_profit_eur' => 840,
        'cpa_profit_usd' => 851,
        'created_by' => 1,
        'created_at' => 111111,
        'updated_at' => 100012
      ],
      //ленду
      'l1' => [
        'landing_id' => 1,
        'rebill_percent' => 85,
        'buyout_percent' => 86,
        'cpa_profit_rub' => 87,
        'cpa_profit_eur' => 870,
        'cpa_profit_usd' => 871,
        'created_by' => 1,
        'created_at' => 111111,
        'updated_at' => 100013
      ],
      //провайдеру
      'p1' => [
        'provider_id' => 1,
        'rebill_percent' => 82,
        'buyout_percent' => 83,
        'cpa_profit_rub' => 84,
        'cpa_profit_eur' => 850,
        'cpa_profit_usd' => 861,
        'created_by' => 1,
        'created_at' => 111111,
        'updated_at' => 100014
      ],
      //оператору
      'o1' => [
        'operator_id' => 1,
        'rebill_percent' => 88,
        'buyout_percent' => 89,
        'cpa_profit_rub' => 90,
        'cpa_profit_eur' => 900,
        'cpa_profit_usd' => 901,
        'created_by' => 1,
        'created_at' => 111111,
        'updated_at' => 100015
      ],
      // страна
      'c1' => [
        'landing_id' => 0,
        'operator_id' => 0,
        'country_id' => 1,
        'user_id' => 0,
        'rebill_percent' => 81,
        'buyout_percent' => 82,
        'cpa_profit_rub' => 83,
        'cpa_profit_eur' => 840,
        'cpa_profit_usd' => 851,
        'created_by' => 1,
        'created_at' => 111111,
        'updated_at' => 100016
      ],
      // глобальное
      'global' => [
        'rebill_percent' => 91,
        'buyout_percent' => 92,
        'cpa_profit_rub' => 93,
        'cpa_profit_eur' => 930,
        'cpa_profit_usd' => 931,
        'created_by' => 1,
        'created_at' => 111111,
        'updated_at' => 100017
      ],
      'u2o1l1_rebill_null' => [
        'landing_id' => 1,
        'operator_id' => 1,
        'user_id' => 103,
        'rebill_percent' => null,
        'buyout_percent' => 71,
        'cpa_profit_rub' => 72,
        'cpa_profit_eur' => 720,
        'cpa_profit_usd' => 721,
        'created_by' => 1,
        'created_at' => 111111,
        'updated_at' => 123131
      ],
      'u2o1l1_only_cpa' => [
        'landing_id' => 1,
        'user_id' => 103,
        'rebill_percent' => null,
        'buyout_percent' => null,
        'cpa_profit_rub' => 72,
        'cpa_profit_eur' => 720,
        'cpa_profit_usd' => 721,
        'created_by' => 1,
        'created_at' => 111111,
        'updated_at' => 123132
      ],
      'u2_rebill' => [
        'user_id' => 103,
        'rebill_percent' => 79,
        'buyout_percent' => null,
        'cpa_profit_rub' => null,
        'cpa_profit_eur' => null,
        'cpa_profit_usd' => null,
        'created_by' => 1,
        'created_at' => 111111,
        'updated_at' => 123133
      ],
    ];
  }

  private function installCountryPersonalProfit()
  {
    foreach ($this->countryPersonalProfitsFixtures() as $fixture) {
      Yii::$app
        ->getDb()
        ->createCommand()
        ->insert('personal_profit', $fixture)
        ->execute()
      ;
    }
  }

  public function testCountryPartnerPercents()
  {
    PersonalProfit::deleteAll([]);
    (new PersonalProfit())->invalidateCache();
    $this->installCountryPersonalProfit();

    /** @var Module $promo */
    $promo = Yii::$app->getModule('promo');

    // Проверка по юзеру-ленд-оператору
    $profits = $promo->api('personalProfit', ['userId' => self::PARTNER_1, 'operatorId' => 1, 'landingId' => 1])->getResult();
    $this->assertEquals(70, ArrayHelper::getValue($profits, 'rebill_percent'));
    $this->assertEquals(71, ArrayHelper::getValue($profits, 'buyout_percent'));
    $this->assertEquals(72, ArrayHelper::getValue($profits, 'cpa_profit_rub'));
    $this->assertEquals(720, ArrayHelper::getValue($profits, 'cpa_profit_eur'));
    $this->assertEquals(721, ArrayHelper::getValue($profits, 'cpa_profit_usd'));
    $this->assertEquals(100000, ArrayHelper::getValue($profits, 'updated_at'));

    // Проверка по юзеру-ленду-страна
    $profits = $promo->api('personalProfit', ['userId' => self::PARTNER_1, 'operatorId' => 2, 'landingId' => 2])->getResult();
    $this->assertEquals(80, ArrayHelper::getValue($profits, 'rebill_percent'));
    $this->assertEquals(81, ArrayHelper::getValue($profits, 'buyout_percent'));
    $this->assertEquals(82, ArrayHelper::getValue($profits, 'cpa_profit_rub'));
    $this->assertEquals(820, ArrayHelper::getValue($profits, 'cpa_profit_eur'));
    $this->assertEquals(821, ArrayHelper::getValue($profits, 'cpa_profit_usd'));
    $this->assertEquals(100001, ArrayHelper::getValue($profits, 'updated_at'));

    // Проверка по юзеру-провайдеру-оператору
    $profits = $promo->api('personalProfit', ['userId' => self::PARTNER_1, 'operatorId' => 1, 'landingId' => 3])->getResult();
    $this->assertEquals(70, ArrayHelper::getValue($profits, 'rebill_percent'));
    $this->assertEquals(80, ArrayHelper::getValue($profits, 'buyout_percent'));
    $this->assertEquals(45, ArrayHelper::getValue($profits, 'cpa_profit_rub'));
    $this->assertEquals(0.780, ArrayHelper::getValue($profits, 'cpa_profit_eur'));
    $this->assertEquals(0.690, ArrayHelper::getValue($profits, 'cpa_profit_usd'));
    $this->assertEquals(100002, ArrayHelper::getValue($profits, 'updated_at'));

    // Проверка по юзеру-провайдеру-стране
    $profits = $promo->api('personalProfit', ['userId' => self::PARTNER_1, 'operatorId' => 2, 'landingId' => 3])->getResult();
    $this->assertEquals(80, ArrayHelper::getValue($profits, 'rebill_percent'));
    $this->assertEquals(81, ArrayHelper::getValue($profits, 'buyout_percent'));
    $this->assertEquals(82, ArrayHelper::getValue($profits, 'cpa_profit_rub'));
    $this->assertEquals(820, ArrayHelper::getValue($profits, 'cpa_profit_eur'));
    $this->assertEquals(821, ArrayHelper::getValue($profits, 'cpa_profit_usd'));
    $this->assertEquals(100003, ArrayHelper::getValue($profits, 'updated_at'));

    // юзер ленд
    $profits = $promo->api('personalProfit', ['userId' => self::PARTNER_1, 'operatorId' => self::INCORRECT_ID, 'landingId' => 1])->getResult();
    $this->assertEquals(73, ArrayHelper::getValue($profits, 'rebill_percent'));
    $this->assertEquals(74, ArrayHelper::getValue($profits, 'buyout_percent'));
    $this->assertEquals(75, ArrayHelper::getValue($profits, 'cpa_profit_rub'));
    $this->assertEquals(750, ArrayHelper::getValue($profits, 'cpa_profit_eur'));
    $this->assertEquals(751, ArrayHelper::getValue($profits, 'cpa_profit_usd'));
    $this->assertEquals(100004, ArrayHelper::getValue($profits, 'updated_at'));

    // юзеру-провайдер
    $profits = $promo->api('personalProfit', ['userId' => self::PARTNER_1, 'operatorId' => self::INCORRECT_ID, 'landingId' => 3])->getResult();
    $this->assertEquals(73, ArrayHelper::getValue($profits, 'rebill_percent'));
    $this->assertEquals(74, ArrayHelper::getValue($profits, 'buyout_percent'));
    $this->assertEquals(75, ArrayHelper::getValue($profits, 'cpa_profit_rub'));
    $this->assertEquals(750, ArrayHelper::getValue($profits, 'cpa_profit_eur'));
    $this->assertEquals(751, ArrayHelper::getValue($profits, 'cpa_profit_usd'));
    $this->assertEquals(100005, ArrayHelper::getValue($profits, 'updated_at'));

    // Проверка по юзеру-оператору
    $profits = $promo->api('personalProfit', ['userId' => self::PARTNER_1, 'operatorId' => 1, 'landingId' => self::INCORRECT_ID])->getResult();
    $this->assertEquals(76, ArrayHelper::getValue($profits, 'rebill_percent'));
    $this->assertEquals(77, ArrayHelper::getValue($profits, 'buyout_percent'));
    $this->assertEquals(78, ArrayHelper::getValue($profits, 'cpa_profit_rub'));
    $this->assertEquals(780, ArrayHelper::getValue($profits, 'cpa_profit_eur'));
    $this->assertEquals(781, ArrayHelper::getValue($profits, 'cpa_profit_usd'));
    $this->assertEquals(100006, ArrayHelper::getValue($profits, 'updated_at'));

    // юзеру-страна
    $profits = $promo->api('personalProfit', ['userId' => self::PARTNER_1, 'operatorId' => 3, 'landingId' => self::INCORRECT_ID])->getResult();
    $this->assertEquals(81, ArrayHelper::getValue($profits, 'rebill_percent'));
    $this->assertEquals(82, ArrayHelper::getValue($profits, 'buyout_percent'));
    $this->assertEquals(83, ArrayHelper::getValue($profits, 'cpa_profit_rub'));
    $this->assertEquals(840, ArrayHelper::getValue($profits, 'cpa_profit_eur'));
    $this->assertEquals(851, ArrayHelper::getValue($profits, 'cpa_profit_usd'));
    $this->assertEquals(100007, ArrayHelper::getValue($profits, 'updated_at'));

    // Проверка по юзеру
    $profits = $promo->api('personalProfit', ['userId' => self::PARTNER_1, 'operatorId' => self::INCORRECT_ID, 'landingId' => self::INCORRECT_ID])->getResult();
    $this->assertEquals(79, ArrayHelper::getValue($profits, 'rebill_percent'));
    $this->assertEquals(80, ArrayHelper::getValue($profits, 'buyout_percent'));
    $this->assertEquals(81, ArrayHelper::getValue($profits, 'cpa_profit_rub'));
    $this->assertEquals(810, ArrayHelper::getValue($profits, 'cpa_profit_eur'));
    $this->assertEquals(811, ArrayHelper::getValue($profits, 'cpa_profit_usd'));
    $this->assertEquals(100008, ArrayHelper::getValue($profits, 'updated_at'));
    
    // Проверка по ленд-оператору
    $profits = $promo->api('personalProfit', ['userId' => self::PARTNER_2, 'operatorId' => 1, 'landingId' => 1])->getResult();
    $this->assertEquals(82, ArrayHelper::getValue($profits, 'rebill_percent'));
    $this->assertEquals(83, ArrayHelper::getValue($profits, 'buyout_percent'));
    $this->assertEquals(84, ArrayHelper::getValue($profits, 'cpa_profit_rub'));
    $this->assertEquals(840, ArrayHelper::getValue($profits, 'cpa_profit_eur'));
    $this->assertEquals(841, ArrayHelper::getValue($profits, 'cpa_profit_usd'));
    $this->assertEquals(100009, ArrayHelper::getValue($profits, 'updated_at'));

    // ленд-страна (юзер левый, оператор левый но от страны, лендинг норм)
    $profits = $promo->api('personalProfit', ['userId' => self::PARTNER_2, 'operatorId' => 3, 'landingId' => 1])->getResult();
    $this->assertEquals(81, ArrayHelper::getValue($profits, 'rebill_percent'));
    $this->assertEquals(82, ArrayHelper::getValue($profits, 'buyout_percent'));
    $this->assertEquals(83, ArrayHelper::getValue($profits, 'cpa_profit_rub'));
    $this->assertEquals(840, ArrayHelper::getValue($profits, 'cpa_profit_eur'));
    $this->assertEquals(851, ArrayHelper::getValue($profits, 'cpa_profit_usd'));
    $this->assertEquals(100010, ArrayHelper::getValue($profits, 'updated_at'));

    // по провайдер-оператору (юзер левый, оператор норм, лендинг левый но от провайдера)
    $profits = $promo->api('personalProfit', ['userId' => self::PARTNER_2, 'operatorId' => 1, 'landingId' => 3])->getResult();
    $this->assertEquals(82, ArrayHelper::getValue($profits, 'rebill_percent'));
    $this->assertEquals(83, ArrayHelper::getValue($profits, 'buyout_percent'));
    $this->assertEquals(84, ArrayHelper::getValue($profits, 'cpa_profit_rub'));
    $this->assertEquals(840, ArrayHelper::getValue($profits, 'cpa_profit_eur'));
    $this->assertEquals(841, ArrayHelper::getValue($profits, 'cpa_profit_usd'));
    $this->assertEquals(100011, ArrayHelper::getValue($profits, 'updated_at'));

    // провайдер-страна (юзер левый, оператор левый но от страны, лендинг левый но от провайдера)
    $profits = $promo->api('personalProfit', ['userId' => self::PARTNER_2, 'operatorId' => 3, 'landingId' => 3])->getResult();
    $this->assertEquals(81, ArrayHelper::getValue($profits, 'rebill_percent'));
    $this->assertEquals(82, ArrayHelper::getValue($profits, 'buyout_percent'));
    $this->assertEquals(83, ArrayHelper::getValue($profits, 'cpa_profit_rub'));
    $this->assertEquals(840, ArrayHelper::getValue($profits, 'cpa_profit_eur'));
    $this->assertEquals(851, ArrayHelper::getValue($profits, 'cpa_profit_usd'));
    $this->assertEquals(100012, ArrayHelper::getValue($profits, 'updated_at'));

    // Проверка по ленду
    $profits = $promo->api('personalProfit', ['userId' => self::PARTNER_2, 'operatorId' => self::INCORRECT_ID, 'landingId' => 1])->getResult();
    $this->assertEquals(85, ArrayHelper::getValue($profits, 'rebill_percent'));
    $this->assertEquals(86, ArrayHelper::getValue($profits, 'buyout_percent'));
    $this->assertEquals(87, ArrayHelper::getValue($profits, 'cpa_profit_rub'));
    $this->assertEquals(870, ArrayHelper::getValue($profits, 'cpa_profit_eur'));
    $this->assertEquals(871, ArrayHelper::getValue($profits, 'cpa_profit_usd'));
    $this->assertEquals(100013, ArrayHelper::getValue($profits, 'updated_at'));

    // по провайдеру (юезр левый, оператор некорректный, лендинг левый но от провайдера)
    $profits = $promo->api('personalProfit', ['userId' => self::PARTNER_2, 'operatorId' => self::INCORRECT_ID, 'landingId' => 3])->getResult();
    $this->assertEquals(82, ArrayHelper::getValue($profits, 'rebill_percent'));
    $this->assertEquals(83, ArrayHelper::getValue($profits, 'buyout_percent'));
    $this->assertEquals(84, ArrayHelper::getValue($profits, 'cpa_profit_rub'));
    $this->assertEquals(850, ArrayHelper::getValue($profits, 'cpa_profit_eur'));
    $this->assertEquals(861, ArrayHelper::getValue($profits, 'cpa_profit_usd'));
    $this->assertEquals(100014, ArrayHelper::getValue($profits, 'updated_at'));

    // Проверка по оператору
    $profits = $promo->api('personalProfit', ['userId' => self::PARTNER_2, 'operatorId' => 1, 'landingId' => self::INCORRECT_ID])->getResult();
    $this->assertEquals(88, ArrayHelper::getValue($profits, 'rebill_percent'));
    $this->assertEquals(89, ArrayHelper::getValue($profits, 'buyout_percent'));
    $this->assertEquals(90, ArrayHelper::getValue($profits, 'cpa_profit_rub'));
    $this->assertEquals(900, ArrayHelper::getValue($profits, 'cpa_profit_eur'));
    $this->assertEquals(901, ArrayHelper::getValue($profits, 'cpa_profit_usd'));
    $this->assertEquals(100015, ArrayHelper::getValue($profits, 'updated_at'));

    // страна
    $profits = $promo->api('personalProfit', ['userId' => self::PARTNER_2, 'operatorId' => 3, 'landingId' => self::INCORRECT_ID])->getResult();
    $this->assertEquals(81, ArrayHelper::getValue($profits, 'rebill_percent'));
    $this->assertEquals(82, ArrayHelper::getValue($profits, 'buyout_percent'));
    $this->assertEquals(83, ArrayHelper::getValue($profits, 'cpa_profit_rub'));
    $this->assertEquals(840, ArrayHelper::getValue($profits, 'cpa_profit_eur'));
    $this->assertEquals(851, ArrayHelper::getValue($profits, 'cpa_profit_usd'));
    $this->assertEquals(100016, ArrayHelper::getValue($profits, 'updated_at'));

    // Проверка глобального
    $profits = $promo->api('personalProfit', ['userId' => self::PARTNER_2, 'operatorId' => self::INCORRECT_ID, 'landingId' => self::INCORRECT_ID])->getResult();
    $this->assertEquals(91, ArrayHelper::getValue($profits, 'rebill_percent'));
    $this->assertEquals(92, ArrayHelper::getValue($profits, 'buyout_percent'));
    $this->assertEquals(93, ArrayHelper::getValue($profits, 'cpa_profit_rub'));
    $this->assertEquals(930, ArrayHelper::getValue($profits, 'cpa_profit_eur'));
    $this->assertEquals(931, ArrayHelper::getValue($profits, 'cpa_profit_usd'));
    $this->assertEquals(100017, ArrayHelper::getValue($profits, 'updated_at'));

    // Проверка конфига модуля
    PersonalProfit::deleteAll(['user_id' => 0, 'operator_id' => 0, 'landing_id' => 0]);
    (new PersonalProfit())->invalidateCache();
    $profits = $promo->api('personalProfit', [
      'userId' => self::PARTNER_2,
      'operatorId' => self::INCORRECT_ID,
      'landingId' => self::INCORRECT_ID
    ])->getResult();

    $this->assertEquals(90, ArrayHelper::getValue($profits, 'rebill_percent'));
    $this->assertEquals(90, ArrayHelper::getValue($profits, 'buyout_percent'));
    $this->assertEquals(0, ArrayHelper::getValue($profits, 'cpa_profit_rub'));
    $this->assertEquals(0, ArrayHelper::getValue($profits, 'cpa_profit_eur'));
    $this->assertEquals(0, ArrayHelper::getValue($profits, 'cpa_profit_usd'));
    $this->assertNull(ArrayHelper::getValue($profits, 'updated_at'));
  }

  public function testCountryPersonalProfits()
  {
    /** @var Module $promo */
    $promo = Yii::$app->getModule('promo');
    PersonalProfit::deleteAll('country_id <> 0');
    Yii::$app->cache->flush();


    (new PersonalProfit([
      'landing_id' => 1,
      'operator_id' => 0,
      'user_id' => 101,
      'country_id' => 1,
      'rebill_percent' => 70,
      'buyout_percent' => 71,
      'cpa_profit_rub' => 72,
      'cpa_profit_eur' => 720,
      'cpa_profit_usd' => 721,
      'created_by' => 1,
      'created_at' => 111111,
      'updated_at' => 123123
    ]))->save();

    (new PersonalProfit([
      'landing_id' => 2,
      'operator_id' => 0,
      'user_id' => 101,
      'country_id' => 1,
      'rebill_percent' => 71,
      'buyout_percent' => 72,
      'cpa_profit_rub' => 73,
      'cpa_profit_eur' => 721,
      'cpa_profit_usd' => 722,
      'created_by' => 1,
      'created_at' => 111111,
      'updated_at' => 123124
    ]))->save();

    (new PersonalProfit([
      'landing_id' => 0,
      'operator_id' => 0,
      'user_id' => 101,
      'country_id' => 1,
      'rebill_percent' => 72,
      'buyout_percent' => 73,
      'cpa_profit_rub' => 74,
      'cpa_profit_eur' => 722,
      'cpa_profit_usd' => 723,
      'created_by' => 1,
      'created_at' => 111111,
      'updated_at' => 123124
    ]))->save();

    // по юзеру лендингу и оператору
    $profits = $promo->api('personalProfit', [
      'userId' => self::PARTNER_1,
      'operatorId' => 1,
      'landingId' => 1,
    ])->getResult();

    $this->assertEquals(70, ArrayHelper::getValue($profits, 'rebill_percent'));
    $this->assertEquals(71, ArrayHelper::getValue($profits, 'buyout_percent'));
    $this->assertEquals(72, ArrayHelper::getValue($profits, 'cpa_profit_rub'));
    $this->assertEquals(720, ArrayHelper::getValue($profits, 'cpa_profit_eur'));
    $this->assertEquals(721, ArrayHelper::getValue($profits, 'cpa_profit_usd'));

    // по юзеру стране и ленду (неверный оператор)
    $profits = $promo->api('personalProfit', [
      'userId' => self::PARTNER_1,
      'operatorId' => 2,
      'landingId' => 2,
    ])->getResult();

    $this->assertEquals(71, ArrayHelper::getValue($profits, 'rebill_percent'));
    $this->assertEquals(72, ArrayHelper::getValue($profits, 'buyout_percent'));
    $this->assertEquals(73, ArrayHelper::getValue($profits, 'cpa_profit_rub'));
    $this->assertEquals(721, ArrayHelper::getValue($profits, 'cpa_profit_eur'));
    $this->assertEquals(722, ArrayHelper::getValue($profits, 'cpa_profit_usd'));

    // по стране и по юзеру
    $profits = $promo->api('personalProfit', [
      'userId' => self::PARTNER_1,
      'operatorId' => 2,
    ])->getResult();

    $this->assertEquals(72, ArrayHelper::getValue($profits, 'rebill_percent'));
    $this->assertEquals(73, ArrayHelper::getValue($profits, 'buyout_percent'));
    $this->assertEquals(74, ArrayHelper::getValue($profits, 'cpa_profit_rub'));
    $this->assertEquals(722, ArrayHelper::getValue($profits, 'cpa_profit_eur'));
    $this->assertEquals(723, ArrayHelper::getValue($profits, 'cpa_profit_usd'));
  }


  public function testNullProfits()
  {
    /** @var Module $promo */
    $promo = Yii::$app->getModule('promo');

    // Проверка по юзеру-ленд-оператору
    $profits = $promo->api('personalProfit', ['userId' => self::PARTNER_103, 'operatorId' => 1, 'landingId' => 1])->getResult();
    $this->assertEquals(79, ArrayHelper::getValue($profits, 'rebill_percent'));
    $this->assertEquals(71, ArrayHelper::getValue($profits, 'buyout_percent'));
    $this->assertEquals(72, ArrayHelper::getValue($profits, 'cpa_profit_rub'));
    $this->assertEquals(720, ArrayHelper::getValue($profits, 'cpa_profit_eur'));
    $this->assertEquals(721, ArrayHelper::getValue($profits, 'cpa_profit_usd'));
    $this->assertEquals(123133, ArrayHelper::getValue($profits, 'updated_at'));
  }


  /**
   * @return bool
   */
  private function setPercents()
  {

    $module = Module::getInstance()->settings;
    $module->offsetSet(Module::SETTINGS_MAIN_REBILL_PERCENT_FOR_PARTNER, '90');
    $module->offsetSet(Module::SETTINGS_MAIN_BUYOUT_PERCENT_FOR_PARTNER, '90');
  }


}