<?php
namespace mcms\promo\tests\unit\sources;

use mcms\common\codeception\TestCase;
use mcms\promo\commands\DisabledLandingsReplaceController;
use mcms\promo\models\Landing;
use mcms\promo\models\SourceOperatorLanding;
use Yii;

/**
 * Class DisabledLandingsReplaceTest
 * @package mcms\promo\tests\unit\sources
 */
class DisabledLandingsReplaceTest extends TestCase
{

  public function _fixtures()
  {
    return $this->convertFixtures([
      'promo.sources',
      'promo.landings',
      'promo.operators',
      'promo.landing_operators',
      'promo.sources_operator_landings',
    ]);
  }

  protected function setUp()
  {
    parent::setUp();
    $this->loginAsRoot();
  }


  public function testSuccessReplace()
  {
    Landing::updateAll(['status' => Landing::STATUS_INACTIVE], ['id' => 3]);

    (new DisabledLandingsReplaceController('superId', Yii::$app->getModule('promo')))->actionIndex();

    $exists = SourceOperatorLanding::find()->where(['source_id' => 3, 'landing_id' => 4])->exists();

    $this->assertTrue($exists, 'Landing successfully replaced');
  }



  public function testFailReplaceHiddenLand()
  {
    Landing::updateAll(['status' => Landing::STATUS_INACTIVE], ['id' => 3]);
    Landing::updateAll(['access_type' => Landing::ACCESS_TYPE_HIDDEN], ['id' => 4]);

    (new DisabledLandingsReplaceController('superId', Yii::$app->getModule('promo')))->actionIndex();

    $exists = SourceOperatorLanding::find()->where(['source_id' => 3, 'landing_id' => 4])->exists();

    $this->assertFalse($exists, 'Hidden landing replace fail');
  }

}