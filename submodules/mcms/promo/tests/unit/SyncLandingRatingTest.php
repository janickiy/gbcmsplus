<?php

namespace tests\unit;

use mcms\common\codeception\TestCase;
use mcms\promo\components\handlers\KP;
use mcms\promo\components\handlers\Mobleaders;
use mcms\promo\models\Provider;
use mcms\promo\Module;
use Yii;
use yii\db\Query;

/**
 * Class SyncLandingRatingTest
 * @package tests\unit
 */
class SyncLandingRatingTest extends TestCase
{

  protected function _before()
  {
    Yii::$app->db->createCommand('delete from operator_top_landings')->execute();
  }

  public function _fixtures()
  {
    return $this->convertFixtures([
      'users.users',
      'promo.user_promo_settings',
      'promo.landings',
      'promo.landing_operators',
    ]);
  }

  public function testSyncKP()
  {
    $landingsJson = file_get_contents(__DIR__ . '/../_data/sync-providers/kp-landings.json');
    /** @var KP|\PHPUnit_Framework_MockObject_MockObject $stub */
    $stub = $this->getMockBuilder(KP::class)
      ->setMethods(['getLandingsFromApi'])
      ->setConstructorArgs(['provider' => new Provider(['code' => 'mobleaders'])])
      ->getMock();
    $stub->method('getLandingsFromApi')->willReturn($landingsJson);

    $stub->syncRating();

    $landForCat3Op2 = (new Query())
      ->select(['landing_id', 'rating'])
      ->from('operator_top_landings')
      ->where(['category_id' => 3, 'operator_id' => 2])
      ->one();
    $landForCat6Op1 = (new Query())
      ->select('landing_id')
      ->from('operator_top_landings')
      ->where(['category_id' => 6, 'operator_id' => 1])
      ->scalar();

    $this->assertEquals($landForCat3Op2['landing_id'], 1, 'Топовый ленд для категории 3 и оператора 2 по рейтингу неверен');
    $this->assertEquals(round($landForCat3Op2['rating'], 2), 1.11, 'Рейтинг для топового ленда для категории 3 и оператора 2 по рейтингу неверен');
    $this->assertEquals($landForCat6Op1, 4, 'Топовый ленд для категории 6 и оператора 2 по последней подписке неверен');
  }

  public function testSyncMobleaders()
  {
    $landingsJson = file_get_contents(__DIR__ . '/../_data/sync-providers/mobleaders-landings.json');
    /** @var KP|\PHPUnit_Framework_MockObject_MockObject $stub */
    $stub = $this->getMockBuilder(Mobleaders::class)
      ->setMethods(['getLandingsFromApi'])
      ->setConstructorArgs(['provider' => new Provider(['code' => 'mobleaders'])])
      ->getMock();
    $stub->method('getLandingsFromApi')->willReturn($landingsJson);

    $stub->syncRating();

    $landForCat3Op3 = (new Query())
      ->select(['landing_id', 'rating'])
      ->from('operator_top_landings')
      ->where(['category_id' => 3, 'operator_id' => 3])
      ->one();
    $landForCat6Op1 = (new Query())
      ->select('landing_id')
      ->from('operator_top_landings')
      ->where(['category_id' => 6, 'operator_id' => 1])
      ->scalar();

    $this->assertEquals($landForCat3Op3['landing_id'], 1, 'Топовый ленд для категории 3 и оператора 3 вычесленный по рейтингу неверен');
    $this->assertEquals(round($landForCat3Op3['rating'], 2), 1.22, 'Рейтинг для топового ленд для категории 3 и оператора 3 неверен');
    $this->assertEquals($landForCat6Op1, 4, 'Топовый ленд для категории 6 и оператора 2 вычесленный по последней подписке неверен');
  }
}
