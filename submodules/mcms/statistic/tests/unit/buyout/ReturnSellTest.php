<?php
namespace mcms\statistic\tests\unit\buyout;

use mcms\common\codeception\TestCase;
use mcms\statistic\commands\BuyoutController;
use mcms\statistic\components\ReturnSell;
use Yii;
use yii\db\Query;

/**
 * Class ReturnSellTest
 * @package mcms\statistic\tests\unit\buyout
 */
class ReturnSellTest extends TestCase
{
  public function _fixtures()
  {
    return $this->convertFixtures([
      'promo.landing_operators',
      'promo.sources',
    ]);
  }

  public function testReturnSellToPartner()
  {
    $sql = file_get_contents(__DIR__ . '/../../_data/buyout/sold_subscriptions.sql');
    Yii::$app->db->createCommand($sql)->execute();

    $hitId = 85146877;
    (new ReturnSell(['hitId' => $hitId]))->setVisibleToPartner();

    $sold = (new Query())
      ->select([
        'profit_rub',
        'profit_usd',
        'profit_eur',
        'is_visible_to_partner',
      ])
      ->from(['st' => 'sold_subscriptions'])
      ->where(['st.hit_id' => $hitId])->one();

    $this->assertEquals($sold['is_visible_to_partner'],1);
    $this->assertEquals($sold['profit_rub'],30.00);
    $this->assertEquals($sold['profit_eur'],0.40);
    $this->assertEquals($sold['profit_usd'],0.48);

  }

  public function testNotReturnSellToPartner()
  {
    $sql = file_get_contents(__DIR__ . '/../../_data/buyout/sold_subscriptions.sql');
    Yii::$app->db->createCommand($sql)->execute();

    $hitId = 85146874;
    (new ReturnSell(['hitId' => $hitId]))->setVisibleToPartner();

    $sold = (new Query())
      ->select([
        'profit_rub',
        'profit_usd',
        'profit_eur',
        'is_visible_to_partner',
      ])
      ->from(['st' => 'sold_subscriptions'])
      ->where(['st.hit_id' => $hitId])->one();

    $this->assertEquals($sold['is_visible_to_partner'],0);
    $this->assertEquals($sold['profit_rub'],0.00);
    $this->assertEquals($sold['profit_eur'],0.00);
    $this->assertEquals($sold['profit_usd'],0.00);
  }

}