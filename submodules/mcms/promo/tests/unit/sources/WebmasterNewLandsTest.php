<?php
namespace mcms\promo\tests\unit\sources;

use mcms\common\codeception\TestCase;
use mcms\promo\models\Landing;
use mcms\promo\models\SourceOperatorLanding;
use mcms\promo\models\Source;
use mcms\promo\components\WebmasterNewLandsHandler;

/**
 * Class WebmasterNewLandsTest
 * @package mcms\promo\tests\unit\sources
 */
class WebmasterNewLandsTest extends TestCase
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


  public function testNewLandsFound()
  {
    /* @var Source $source */
    $source = Source::findOne($this->tester->grabFixture('promo.sources')['source1']['id']);
    $properLandingOperator = $this->tester->grabFixture('promo.landing_operators')['common1_beeline'];

    $newLandOperators = (new WebmasterNewLandsHandler())->getSourceNewLandOperators($source);

    $operators = array_map(function ($landingOperator) {
      return $landingOperator->operator_id;
    }, $newLandOperators);

    $landings = array_map(function ($landingOperator) {
      return $landingOperator->landing_id;
    }, $newLandOperators);

    $this->assertTrue(count($newLandOperators) == 2, 'Two landing operator should be found');
    $this->assertTrue(in_array($properLandingOperator['operator_id'], $operators), 'Proper operator should be found');
    $this->assertTrue(in_array($properLandingOperator['landing_id'], $landings), 'Proper landing should be found');

    $landingWithWrongCategory = Landing::find()
      ->where(['<>', 'category_id', $source->category_id])
      ->andWhere(['id' => $landings])->all();

    $this->assertEmpty($landingWithWrongCategory, 'No landings with wrong category should be found');

  }

  public function testNewLandsAdded()
  {
    /* @var Source $source */
    $source = Source::findOne($this->tester->grabFixture('promo.sources')['source1']['id']);
    $landingOperator = $this->tester->grabFixture('promo.landing_operators')['common1_beeline'];

    $newLandOperators = (new WebmasterNewLandsHandler())->getSourceNewLandOperators($source);
    (new WebmasterNewLandsHandler())->addNewLandsToSource($source, $newLandOperators);

    $sourceOperatorLanding = SourceOperatorLanding::findOne([
      'source_id' => $source->id,
      'operator_id' => $landingOperator['operator_id'],
      'landing_id' => $landingOperator['landing_id'],
    ]);

    $this->assertNotNull($sourceOperatorLanding, 'Added landing operators for source should be found');
  }

}