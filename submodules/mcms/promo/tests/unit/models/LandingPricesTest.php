<?php
namespace mcms\promo\tests\unit\models;

use mcms\common\codeception\TestCase;
use mcms\promo\components\LandingOperatorPrices;
use mcms\promo\models\LandingOperator;
use mcms\promo\models\LandingSubscriptionType;
use Yii;

/**
 * Тестим класс @see LandingOperatorPrices
 */
class LandingPricesTest extends TestCase
{

  /**
   * @return array
   */
  public function _fixtures()
  {
    return $this->convertFixtures([
      'promo.landing_operators',
    ]);
  }

  protected function setUp()
  {
    parent::setUp();
    Yii::$app->db->createCommand('DELETE FROM personal_profit')->execute();
    $this->loginAsRoot();
    Yii::$app->cache->flush();
  }

  public function testBuyoutPrices()
  {
    $userId = 101;
    $landingId = 1;
    $operatorId = 1;

    foreach ($this->getBuyoutTestData() as $key => $testCase) {
      $this->setUp(); // чтобы изолировать тесткейсы

      $this->setLandingBuyoutPrice($landingId, $operatorId, $testCase['land_price']);
      $this->setPartnerBuyoutPercent($landingId, $operatorId, $userId, $testCase['partner_percent'], $testCase['partner_fixed_price_rub'], $testCase['partner_fixed_price_eur'], $testCase['partner_fixed_price_usd']);
      Yii::$app->cache->flush();

      $prices = LandingOperatorPrices::create(
        LandingOperator::findOne(['landing_id' => $landingId, 'operator_id' => $operatorId]),
        $userId
      );
      $this->assertEquals($testCase['partner_price'], $prices->getBuyoutPrice('rub'), "[$key] за сколько продаем пдп");
      $this->assertEquals($testCase['partner_view_price'], $prices->getBuyoutProfit('rub'), "[$key] сколько получает партнер");
    }
  }


  public function testRebillPrices()
  {
    $userId = 101;
    $landingId = 1;
    $operatorId = 1;

    foreach ($this->getRebillTestData() as $key => $testCase) {
      $this->setUp(); // чтобы изолировать тесткейсы

      $this->setLandingRebillPrice($landingId, $operatorId, $testCase['land_price'], $testCase['land_is_onetime']);
      $this->setPartnerRebillPercent($landingId, $operatorId, $userId, $testCase['partner_percent'], $testCase['partner_cpa_fixed_price_rub'], $testCase['partner_cpa_fixed_price_eur'], $testCase['partner_cpa_fixed_price_usd']);
      Yii::$app->cache->flush();

      $prices = LandingOperatorPrices::create(
        LandingOperator::findOne(['landing_id' => $landingId, 'operator_id' => $operatorId]),
        $userId
      );
      $this->assertEquals($testCase['partner_price'], $prices->getRebillPrice('rub'), "[$key] partner view price OK");
    }
  }

  /**
   * @return array
   */
  protected function getRebillTestData()
  {
    $keys = [
      /** дано */
      'land_is_onetime',
      'land_price',
      'partner_percent',
      'partner_cpa_fixed_price_rub',
      'partner_cpa_fixed_price_eur',
      'partner_cpa_fixed_price_usd',

      /** результаты */
      'partner_price',
    ];

    return [
      array_combine($keys, [false, 20, 80, null, null, null, 16]),
      array_combine($keys, [false, 11, 78, null, null, null, 8.58]),
      array_combine($keys, [false, 13, 50, null, null, null, 6.5]),
      array_combine($keys, [false, 14, 70, null, null, null, 9.8]),

      array_combine($keys, [true, 20, 80, 51, 510, 511, 51]),
      array_combine($keys, [true, 11, 78, 52, 520, 521, 52]),
      array_combine($keys, [true, 13, 50, 53, 530, 531, 53]),
      array_combine($keys, [true, 14, 70, 54, 540, 541, 54]),
    ];
  }
  
  
  /**
   * @return array
   */
  protected function getBuyoutTestData()
  {
    $keys = [
      /** дано */
      'land_price',
      'partner_percent',
      'partner_fixed_price_rub',
      'partner_fixed_price_eur',
      'partner_fixed_price_usd',

      /** результаты */
      'partner_price', // прайс (цена выкупа без учета фикс. цпа)
      'partner_view_price', // профит (цена выкупа с учетом фикс. цпа)
    ];

    return [
      array_combine($keys, [20, 80, null, null, null, 16, 16]),
      array_combine($keys, [20, 95, null, null, null, 19, 19]),
      array_combine($keys, [20, 131, null, null, null, 26.2, 26.2]),
      array_combine($keys, [20, 121, null, null, null, 24.2, 24.2]),
      array_combine($keys, [20, 131, null, null, null, 26.2, 26.2]),
      array_combine($keys, [23, 100, null, null, null, 23, 23]),

      array_combine($keys, [20, 80, 50, 500, 501, 16, 50]),
      array_combine($keys, [20, 95, 51, 510, 511, 19, 51]),
      array_combine($keys, [20, 131, 52, 520, 521, 26.2, 52]),
      array_combine($keys, [20, 121, 53, 530, 531, 24.2, 53]),
      array_combine($keys, [20, 131, 54, 540, 541, 26.2, 54]),
      array_combine($keys, [23, 100, 55, 550, 551, 23, 55]),
    ];
  }

  /**
   * @param $landingId
   * @param $operatorId
   * @param $userId
   * @param $percent
   * @param null $fixedCpaPriceRub
   * @param null $fixedCpaPriceEur
   * @param null $fixedCpaPriceUsd
   * @throws \yii\db\Exception
   */
  protected function setPartnerBuyoutPercent($landingId, $operatorId, $userId, $percent, $fixedCpaPriceRub = null, $fixedCpaPriceEur = null, $fixedCpaPriceUsd = null)
  {
    Yii::$app->db->createCommand()->insert('personal_profit',
      [
        'buyout_percent' => $percent,
        'cpa_profit_rub' => $fixedCpaPriceRub,
        'cpa_profit_eur' => $fixedCpaPriceEur,
        'cpa_profit_usd' => $fixedCpaPriceUsd,
        'created_at' => time(),
        'user_id' => $userId,
        'created_by' => 1,
        'landing_id' => $landingId,
        'operator_id' => $operatorId
      ]
    )->execute();
  }

  /**
   * @param $landingId
   * @param $operatorId
   * @param $userId
   * @param $percent
   * @param null $fixedCpaPriceRub
   * @param null $fixedCpaPriceEur
   * @param null $fixedCpaPriceUsd
   * @throws \yii\db\Exception
   */
  protected function setPartnerRebillPercent($landingId, $operatorId, $userId, $percent, $fixedCpaPriceRub = null, $fixedCpaPriceEur = null, $fixedCpaPriceUsd = null)
  {
    Yii::$app->db->createCommand()->insert('personal_profit',
      [
        'rebill_percent' => $percent,
        'created_at' => time(),
        'cpa_profit_rub' => $fixedCpaPriceRub,
        'cpa_profit_eur' => $fixedCpaPriceEur,
        'cpa_profit_usd' => $fixedCpaPriceUsd,
        'user_id' => $userId,
        'created_by' => 1,
        'landing_id' => $landingId,
        'operator_id' => $operatorId
      ]
    )->execute();
  }

  /**
   * @param $landingId
   * @param $operatorId
   * @param $priceRub
   */
  protected function setLandingBuyoutPrice($landingId, $operatorId, $priceRub)
  {
    LandingOperator::updateAll(
      ['buyout_price_rub' => $priceRub],
      ['landing_id' => $landingId, 'operator_id' => $operatorId]
    );
  }

  /**
   * @param $landingId
   * @param $operatorId
   * @param $priceRub
   * @param bool $isOnetime
   */
  protected function setLandingRebillPrice($landingId, $operatorId, $priceRub, $isOnetime = false)
  {
    LandingOperator::updateAll(
      [
        'rebill_price_rub' => $priceRub,
        'local_currency_rebill_price' => $priceRub,
        'local_currency_id' => 1,
        'subscription_type_id' => $isOnetime
          ? LandingSubscriptionType::findOne(['code' => 'onetime'])->id
          : LandingSubscriptionType::findOne(['code' => 'sub'])->id
        ],
      ['landing_id' => $landingId, 'operator_id' => $operatorId]
    );
  }
}