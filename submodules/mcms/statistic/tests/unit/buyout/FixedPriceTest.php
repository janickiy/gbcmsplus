<?php
namespace mcms\statistic\tests\unit\buyout;

use mcms\common\codeception\TestCase;
use mcms\statistic\commands\BuyoutController;
use Yii;

/**
 * Class FixedPriceTest
 * @package mcms\statistic\tests\unit\buyout
 */
class FixedPriceTest extends TestCase
{

  protected function setUp()
  {
    parent::setUp();
  }


  public function testNoFixedPrice()
  {
    $stub = $this->getMockBuilder(BuyoutController::class)
      ->setConstructorArgs(['buyout-controller', Yii::$app->getModule('statistic')])
      ->getMock();

    $method = new \ReflectionMethod(BuyoutController::class, 'getPartnerProfits');

    $arg = [
      'fixed_buyout_partner_profit' => null,
      'price_rub' => 20,
      'price_eur' => 10,
      'price_usd' => 15,
      'hit_id' => 1
    ];
    $method->setAccessible(true);

    $profits = $method->invokeArgs($stub, [&$arg]);

    $this->assertEquals(20, $profits['partner_profit_rub']);
    $this->assertEquals(10, $profits['partner_profit_eur']);
    $this->assertEquals(15, $profits['partner_profit_usd']);
    $this->assertEquals(1, $profits['is_visible_to_partner']);
  }

}