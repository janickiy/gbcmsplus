<?php

namespace tests\unit;

use Codeception\Util\Stub;
use mcms\common\codeception\TestCase;
use mcms\currency\models\Currency;
use mcms\promo\components\handlers\KP;
use mcms\promo\components\handlers\Mobleaders;
use mcms\promo\models\Landing;
use mcms\promo\models\Provider;
use mcms\promo\Module;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\httpclient\Client;
use yii\httpclient\Response;
use yii\web\HeaderCollection;

/**
 * Class BuyoutAutoPricesTest
 * @package tests\unit
 */
class BuyoutAutoPricesTest extends TestCase
{

  public function _fixtures()
  {
    return $this->convertFixtures([
      'promo.operators',
      'promo.providers',
      'promo.landing_categories',
    ]);
  }

  protected function setUp()
  {
    parent::setUp();
    Yii::$app->db->createCommand('SET FOREIGN_KEY_CHECKS = 0')->execute();
    Yii::$app->db->createCommand('TRUNCATE TABLE landing_operators')->execute();
    Yii::$app->db->createCommand('TRUNCATE TABLE landing_operator_pay_types')->execute();
    Yii::$app->db->createCommand('TRUNCATE TABLE landings')->execute();
    Yii::$app->db->createCommand('SET FOREIGN_KEY_CHECKS = 1')->execute();
    $sql = file_get_contents(__DIR__ . '/../_data/currencies.sql');
    Yii::$app->db->createCommand($sql)->execute();

  }

  // buyout_price не пришел
  public function testWithoutBuyoutPrice()
  {
    Yii::$app->settingsManager->offsetSet(Module::SETTINGS_AVR_NUMBER_REBILL_PER_SUBSCRIPTION, 3);

    $providerKp = Provider::findOne(['code' => 'kp']);
    $providerMobleaders = Provider::findOne(['code' => 'mobleaders']);
    $currencyConvert = ArrayHelper::map(Currency::find()->all(), 'id', 'code');

    // Заглушение KP::getLandingsFromApi()
    Yii::$container->set(KP::class, function () use ($providerKp, $currencyConvert) {
      return Stub::make(KP::class, ['getLandingsFromApi' => $this->getDataKp(), 'getResponse' => $this->getResponse(), 'currencyConvert' => $currencyConvert,
        'providerModel' => $providerKp, 'ignoreCurl' => true]);
    });

    // Заглушение Mobleaders::getLandingsFromApi()
    Yii::$container->set(Mobleaders::class, function () use ($providerMobleaders, $currencyConvert) {
      return Stub::make(Mobleaders::class, ['getLandingsFromApi' => $this->getDataMobleaders(), 'currencyConvert' => $currencyConvert,
        'providerModel' => $providerMobleaders, 'ignoreCurl' => true]);
    });

    $handlerKp = Yii::$container->get(KP::class);
    $handlerKp->auth();
    $handlerKp->syncLandings(1);

    $handlerMobleaders = Yii::$container->get(Mobleaders::class);
    $handlerMobleaders->auth();
    $handlerMobleaders->syncLandings(1);

    $kpLandingOperators = Landing::findOne(['provider_id' => $providerKp->id])->landingOperator;
    $mobleadersLandingOperators = Landing::findOne(['provider_id' => $providerMobleaders->id])->landingOperator;

    $this->assertEquals(count($kpLandingOperators), 1, 'Должен создаться один лендинг-оператор KP');
    $kpLandingOperator = reset($kpLandingOperators);
    $this->assertEquals($kpLandingOperator->buyout_price_eur, 0.127, 'Не верно рассчитана цена выкупа KP');

    $this->assertEquals(count($mobleadersLandingOperators), 1, 'Должен создаться один лендинг-оператор Mobleaders');
    $mobleadersLandingOperator = reset($mobleadersLandingOperators);
    $this->assertEquals($mobleadersLandingOperator->buyout_price_rub, 12, 'Не верно рассчитана цена выкупа Mobleaders');
  }

  /**
   * @param bool $buyoutPrice
   * @return Response
   */
  private function getResponse($buyoutPrice = false)
  {
    $data = $this->getDataKp($buyoutPrice);

    $headers = new HeaderCollection();
    $headers->add('http-code', 200);

    $response = new Response();
    $response->setHeaders($headers);
    $response->client = Yii::createObject(Client::class); // для распарсивания json

    return $response->setContent($data);
  }

  /**
   * @param bool $buyoutPrice
   * @return string
   */
  private function getDataKp($buyoutPrice = false)
  {
    $result = [
      'success' => true,
      'data' => [
        0 => [
          'id' => 7,
          'name' => [
            'ru' => 'Game three',
            'en' => 'Game three',
          ],
          'image' => 'https://kz.rgkmobile.com/uploads/promo/landing/20170220/g7vHXxf2Op.png',
          'category_id' => 2,
          'provider_id' => 2,
          'status' => 2,
          'service_url' => 'http://kz.wellness.energy/',
          'description' => [
            'ru' => '',
            'en' => '',
          ],
          'operators_text' => 'Kcell',
          'code' => 'kz/kino1',
          'to_landing_id' => 1,
          'access_type' => 0,
          'created_at' => 1487607669,
          'updated_at' => 1525669322,
          'operators' => [
            0 => [
              'id' => 2,
              'name' => 'Kcell',
              'status' => 1,
              'rejection_reason' => '',
              'hold' => 1,
              'pay_types' => 'a:1:{i:0;s:1:"3";}',
              'price_default' => 17,
              'price_real' => 3.00,
              'currency' => 'rub',
              'currency_default' => 'kzt',
              'subscription_type_id' => 2,
              'payment_type' => 'reb',
              'incoming' => '50 KZT',
            ]
          ]
        ]
      ]
    ];
    if ($buyoutPrice) {
      $result['data'][0]['operators'][0]['buyout_price'] = 7;
    }
    return Json::encode($result);
  }

  /**
   * @param bool $buyoutPrice
   * @return string
   */
  private function getDataMobleaders($buyoutPrice = false)
  {
    $result = [
      0 => [
        'id' => '8',
        'name' => 'Game three',
        'screen' => $this->getFileName(),
        'category_id' => 2,
        'provider_id' => 1,
        'status' => 'on',
        'service_url' => 'http://kz.wellness.energy/',
        'description' => '',
        'operators_text' => 'Kcell',
        'code' => 'kz/kino1',
        'to_landing_id' => 2,
        'access_type' => 0,
        'created_at' => 1487607669,
        'updated_at' => 1525669322,
        'currency' => 1,
        'profit' => 4.00,
        'operators' => [
          0 => [
            'operator_id' => 2,
            'name' => 'Kcell',
            'status' => 1,
            'rejection_reason' => '',
            'hold' => 1,
            'pay_types' => 'a:1:{i:0;s:1:"3";}',
            'price_default' => 17,
            'currency' => 'rub',
            'subscription_type_id' => 2,
            'payment_type' => 'reb',
            'incoming' => '50 KZT',
          ]
        ]
      ]
    ];
    if ($buyoutPrice) {
      $result[0]['operators'][0]['buyout_price'] = 5;
    }
    return Json::encode($result);
  }

  private function getDir()
  {
    return __DIR__ . '/../../../../../web/uploads/promo/landing/sync/';
  }

  private function getFileAddr()
  {
    return $this->getDir() . $this->getFileName();
  }

  private function getFileName()
  {
    return 'image.png';
  }
}
