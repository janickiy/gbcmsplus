<?php
namespace mcms\promo\tests\unit\models;

use mcms\common\codeception\TestCase;
use mcms\promo\components\WebmasterNewLandsHandler;
use mcms\promo\models\Source;
use mcms\promo\models\SourceOperatorLanding;

/**
 * Class LandingConvertTestTest
 * @package mcms\promo\tests\unit\sources
 */
class LandingConvertTestTest extends TestCase
{

  public function _fixtures()
  {
    return $this->convertFixtures([
      'promo.landing_convert_tests',
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


  public function testConvertTest()
  {
    /* @var Source $source */
    $source = Source::findOne($this->tester->grabFixture('promo.sources')['source1']['id']);

    $sourceOperatorLandings = array_map(function ($landOperator) use ($source) {
      return [
        'source_id' => $source->id,
        'operator_id' => $landOperator->operator_id,
        'landing_id' => $landOperator->landing_id,
      ];
    }, $source->sourceOperatorLanding);

    (new WebmasterNewLandsHandler())->beginLandingsConvertTest($source);

    foreach ($sourceOperatorLandings as $sourceOperatorLanding) {
      $this->assertNotNull(SourceOperatorLanding::findOne([
        'source_id' => $sourceOperatorLanding['source_id'],
        'operator_id' => $sourceOperatorLanding['operator_id'],
        'landing_id' => $sourceOperatorLanding['landing_id'],
      ]), 'Source landing operators should be found');
    }


    $this->assertEquals(
      count($sourceOperatorLandings),
      SourceOperatorLanding::find()->where(['source_id' => $source->id])->count(),
      'New source operators landing should not be found'
    );

  }

}