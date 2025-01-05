<?php
namespace mcms\promo\tests\unit\api;

use mcms\common\codeception\TestCase;
use mcms\promo\models\Source;
use mcms\promo\models\source\SourceCopy;
use mcms\promo\Module;
use Yii;

/**
 * Class SourceCopyTest
 * @package mcms\promo\tests\unit\api
 */
class SourceCopyTest extends TestCase
{

  const PARTNER_1 = 101;
  const PARTNER_2 = 3;
  const DONOR_SOURCE_ID = 4;

  public function _fixtures()
  {
    return $this->convertFixtures([
      'promo.sources',
      'promo.streams',
      'promo.domains',
      'promo.landing_categories',
    ]);
  }

  protected function tearDown()
  {
    Yii::$app->user->logout();
    parent::tearDown();
  }

  protected function setUp()
  {
    $sql = file_get_contents(__DIR__ . '/../../_data/sources_operator_landings.sql');
    Yii::$app->db->createCommand($sql)->execute();

    parent::setUp();
    Yii::$app->user->logout();
  }


  public function testSuccessCopy()
  {
    /** @var Module $promo */
    $promo = Yii::$app->getModule('promo');

    $donor = Source::findOne(self::DONOR_SOURCE_ID);

    $this->loginById(self::PARTNER_1);

    /** @var SourceCopy $result */
    $result = $promo->api(
      'sourceCopy',
      ['source_id' => self::DONOR_SOURCE_ID]
    )->getResult();

    $this->assertNotNull($result->id);

    foreach ($donor->getAttributes() as $attribute => $value) {
      switch ($attribute) {
        case 'id':
        case 'created_at':
        case 'updated_at':
        case 'hash':
          $this->assertNotEquals($value, $result->{$attribute});
          break;
        case 'name':
          $this->assertEquals($value . SourceCopy::POSTFIX_NAME, $result->name);
          break;
        default:
          $this->assertEquals($value, $result->{$attribute});
      }
    }

    $this->assertEquals(
      count($donor->getSourceOperatorLanding()->all()),
      count($result->getSourceOperatorLanding()->all())
    );

  }


  public function testFailCopy()
  {
    /** @var Module $promo */
    $promo = Yii::$app->getModule('promo');

    $this->loginById(self::PARTNER_2);

    /** @var SourceCopy $result */
    $result = $promo->api(
      'sourceCopy',
      ['source_id' => self::DONOR_SOURCE_ID]
    )->getResult();

    $this->assertNull($result);
  }


}