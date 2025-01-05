<?php
namespace mcms\promo\tests\unit\models;

use Codeception\Util\Stub;
use mcms\common\codeception\TestCase;
use mcms\common\helpers\ArrayHelper;
use mcms\promo\models\Source;
use Yii;
use yii\helpers\Html;

/**
 * Class SourceTest
 * @package mcms\promo\tests\unit\models
 */
class SourceTest extends TestCase
{

  public function _fixtures()
  {
    return $this->convertFixtures([
      'promo.streams', 'promo.domains', 'promo.landing_operators'
    ]);
  }

  public function testGetArbitraryLink()
  {
    // Когда домен без слэша в конце
    $source = new Source([
      'scenario' => Source::SCENARIO_PARTNER_CREATE_ARBITRARY_SOURCE,
      'user_id' => 101,
      'domain_id' => 1,
      'subid1' => 'lll1',
      'subid2' => 'lll2',
      'hash' => 'asdasdasd',
      'trafficback_type' => Source::TRAFFICBACK_TYPE_DYNAMIC
    ]);

    $this->assertEquals('http://system_domain1.ru/asdasdasd/?subid1=lll1&subid2=lll2&back_url=', $source->getLink(), 'Когда домен без слэша в конце');

    // Когда домен без слэша в конце и без http:// вначале
    $source = new Source([
      'scenario' => Source::SCENARIO_PARTNER_CREATE_ARBITRARY_SOURCE,
      'user_id' => 101,
      'domain_id' => 2,
      'subid1' => 'lll1',
      'subid2' => 'lll2',
      'hash' => 'asdasdasd',
      'trafficback_type' => Source::TRAFFICBACK_TYPE_DYNAMIC
    ]);

    $this->assertEquals('http://system_domain2.ru/asdasdasd/?subid1=lll1&subid2=lll2&back_url=', $source->getLink(), 'Когда домен без слэша в конце и без http:// вначале');

    // Когда домен без http:// вначале
    $source = new Source([
      'scenario' => Source::SCENARIO_PARTNER_CREATE_ARBITRARY_SOURCE,
      'user_id' => 101,
      'domain_id' => 3,
      'subid1' => 'lll1',
      'subid2' => 'lll2',
      'hash' => 'asdasdasd',
      'trafficback_type' => Source::TRAFFICBACK_TYPE_DYNAMIC
    ]);

    $this->assertEquals('http://system_domain3.ru/asdasdasd/?subid1=lll1&subid2=lll2&back_url=', $source->getLink(), 'Когда домен без http:// вначале');

    // Когда домен с http:// вначале и с слэшем в конце
    $source = new Source([
      'scenario' => Source::SCENARIO_PARTNER_CREATE_ARBITRARY_SOURCE,
      'user_id' => 101,
      'domain_id' => 4,
      'subid1' => 'lll1',
      'subid2' => 'lll2',
      'hash' => 'asdasdasd',
      'trafficback_type' => Source::TRAFFICBACK_TYPE_DYNAMIC
    ]);

    $this->assertEquals('http://system_domain4.ru/asdasdasd/?subid1=lll1&subid2=lll2&back_url=', $source->getLink(), 'Когда домен с http:// вначале и с слэшем в конце');

    // Без меток
    $source = new Source([
      'scenario' => Source::SCENARIO_PARTNER_CREATE_ARBITRARY_SOURCE,
      'user_id' => 101,
      'domain_id' => 4,
      'hash' => 'asdasdasd',
      'trafficback_type' => Source::TRAFFICBACK_TYPE_DYNAMIC
    ]);

    $this->assertEquals('http://system_domain4.ru/asdasdasd/?back_url=', $source->getLink(), 'Без меток');

    // С меткой 1
    $source = new Source([
      'scenario' => Source::SCENARIO_PARTNER_CREATE_ARBITRARY_SOURCE,
      'user_id' => 101,
      'domain_id' => 4,
      'subid1' => 'lll1',
      'hash' => 'asdasdasd',
      'trafficback_type' => Source::TRAFFICBACK_TYPE_DYNAMIC
    ]);

    $this->assertEquals('http://system_domain4.ru/asdasdasd/?subid1=lll1&back_url=', $source->getLink(), 'С меткой 1');

    // С меткой 2
    $source = new Source([
      'scenario' => Source::SCENARIO_PARTNER_CREATE_ARBITRARY_SOURCE,
      'user_id' => 101,
      'domain_id' => 4,
      'subid2' => 'lll2',
      'hash' => 'asdasdasd',
      'trafficback_type' => Source::TRAFFICBACK_TYPE_DYNAMIC
    ]);

    $this->assertEquals('http://system_domain4.ru/asdasdasd/?subid2=lll2&back_url=', $source->getLink(), 'С меткой 2');

    // Статичный ТБ
    $source = new Source([
      'scenario' => Source::SCENARIO_PARTNER_CREATE_ARBITRARY_SOURCE,
      'user_id' => 101,
      'domain_id' => 4,
      'hash' => 'asdasdasd',
      'trafficback_type' => Source::TRAFFICBACK_TYPE_STATIC
    ]);

    $this->assertEquals('http://system_domain4.ru/asdasdasd/', $source->getLink(), 'Статичный ТБ');
  }

  /**
   * Проверка источников на XSS
   */
  public function testXSS()
  {
    $testCases = require(__DIR__ . '/../../_data/url.php');

    foreach ($testCases as $testCase) {
      $source = Stub::make(Source::class, [
        'scenario' => Source::SCENARIO_PARTNER_CREATE_ARBITRARY_SOURCE,
        'user_id' => 101,
        'domain_id' => 4,
        'hash' => Yii::$app->security->generateRandomString(16),
        'name' => Yii::$app->security->generateRandomString(16),
        'stream_id' => 1,
        'afterSave' => true
      ]);

      $source->linkOperatorLandings = ['1' => ['1' => []]];

      foreach ($testCase as $attribute => $values) {
        $source->{$attribute} = $values[0];
      }

      $this->assertTrue(
        $source->save(),
        'Source model not saved, ' . json_encode(ArrayHelper::toArray($source)) . Html::errorSummary($source));

      foreach ($testCase as $attribute => $values) {
        $this->assertEquals(
          $values[1],
          $source->{$attribute},
          'Attribute filter fail ' . $attribute . ' value=' . $values[1]
        );
      }
    }
  }

}