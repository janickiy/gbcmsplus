<?php

namespace mcms\statistic\tests\unit\buyout;

use mcms\common\codeception\TestCase;
use mcms\statistic\commands\BuyoutController;
use mcms\statistic\models\Complain;
use mcms\user\models\User;
use Yii;

/**
 * Class ComplainsTest
 * @package mcms\statistic\tests\unit\buyout
 */
class ComplainsTest extends TestCase
{

  public function _fixtures()
  {
    return $this->convertFixtures([
      'promo.landing_operators',
      'promo.sources',
    ]);
  }


  public function testComplains()
  {
    $sql = file_get_contents(__DIR__ . '/../../_data/buyout/add_complains.sql');
    Yii::$app->db->createCommand($sql)->execute();

    (new BuyoutController('buyout', Yii::$app->getModule('statistic')))->actionIndex();

    $shouldBeAddMomentComplain = [1000];
    $result = Yii::$app->db->createCommand('SELECT hit_id FROM complains WHERE type=:type', [':type' => Complain::TYPE_AUTO_MOMENT])->queryColumn();
    $this->assertEquals($shouldBeAddMomentComplain, $result);

    $shouldBeAddMomentComplain = [1001];
    $result = Yii::$app->db->createCommand('SELECT hit_id FROM complains WHERE type=:type', [':type' => Complain::TYPE_AUTO_24])->queryColumn();
    $this->assertEquals($shouldBeAddMomentComplain, $result);

  }

}