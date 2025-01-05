<?php

namespace mcms\statistic\tests\unit\buyout;

use mcms\common\codeception\TestCase;
use mcms\statistic\commands\BuyoutController;
use mcms\statistic\Module;
use Yii;

/**
 * Выкупы при включенной настройке "Выкупать только после 1го ребилла"
 */
class GlobalAfter1stRebillOnlyTest extends TestCase
{

  public function _fixtures()
  {
    return $this->convertFixtures([
      'promo.landing_operators',
      'promo.sources',
    ]);
  }

  protected function setUp()
  {
    parent::setUp();
    $this->setModuleSetting(Module::SETTINGS_BUYOUT_AFTER_1ST_REBILL_ONLY, true);
  }


  public function testBuyoutMinutes()
  {
    $sql = file_get_contents(__DIR__ . '/../../_data/buyout_global_after_1st_rebill_only/buyout_minutes.sql');
    Yii::$app->db->createCommand($sql)->execute();

    $controller = new BuyoutController('buyout-controller', Yii::$app->getModule('statistic'));

    $controller->setMinutes(1);

    $query = $controller->getSubscriptionQuery();

    $result = $query->all();

    $this->assertEquals(0, count($result));
  }

  public function test1stRebill()
  {
    $sql = file_get_contents(__DIR__ . '/../../_data/buyout_global_after_1st_rebill_only/1st_rebill.sql');
    Yii::$app->db->createCommand($sql)->execute();

    $controller = new BuyoutController('buyout-controller', Yii::$app->getModule('statistic'));

    $controller->setMinutes(1);

    $query = $controller->getSubscriptionQuery();

    $result = $query->all();

    $shouldBeSell = [1001];
    $this->assertEquals(count($shouldBeSell), count($result));

    foreach ($result AS $sub) {
      $this->assertTrue(in_array($sub['hit_id'], $shouldBeSell));
    }
  }

  public function testUniquePhone()
  {
    $sql = file_get_contents(__DIR__ . '/../../_data/buyout_global_after_1st_rebill_only/unique_phone.sql');
    Yii::$app->db->createCommand($sql)->execute();

    (new BuyoutController('buyout', Yii::$app->getModule('statistic')))->actionIndex();
    (new BuyoutController('buyout', Yii::$app->getModule('statistic')))->actionIndex(); // не удалять
    (new BuyoutController('buyout', Yii::$app->getModule('statistic')))->actionIndex(); // не удалять

    $shouldBeSell = [
      12332, // эта пдп уже выкуплена в фикстурах
      1005, // эта пдп уже выкуплена в фикстурах
    ];
    sort($shouldBeSell);
    $result = Yii::$app->db->createCommand('SELECT hit_id FROM sold_subscriptions')->queryColumn();
    $this->assertEquals($shouldBeSell, $result);
  }

}