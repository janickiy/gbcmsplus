<?php

namespace mcms\promo\components;

use mcms\common\helpers\curl\Curl;
use mcms\promo\components\handlers\KP;
use mcms\promo\components\provider_instances_sync\api_drivers\ApiDriverInterface;
use mcms\promo\components\provider_instances_sync\KpApiClient;
use mcms\promo\components\provider_instances_sync\requests\AuthRequest;
use mcms\promo\models\Provider;
use mcms\promo\models\ProviderSettingsKp;
use mcms\promo\Module;
use Yii;

class KpProviderTester
{
  /** @var Provider */
  private $provider;

  const TDS_SEARCH_PHRASE = 'redirect';

  /**
   * KpProviderTester constructor.
   * @param Provider $provider
   */
  public function __construct(Provider $provider)
  {
    $this->provider = $provider;
  }

  /**
   * @return bool|false|int
   * @throws \mcms\common\helpers\curl\CurlInitException
   * @throws \mcms\common\helpers\curl\CurlMandatoryUrlException
   */
  public function isTdsWorking()
  {
    $url = $this->provider->url;
    $separator = empty(parse_url($url, PHP_URL_QUERY)) ? '?' : '&';

    $curlResult = (new Curl([
      'url' => $url . $separator . 'debug=yes',
    ]))->getResult();

    if ($curlResult === false) {
      return false;
    }

    return preg_match('/' . self::TDS_SEARCH_PHRASE . '/', $curlResult);
  }

  /**
   * @return bool
   */
  public function isLandingsApiWorking()
  {
    $providerHandler = new KP($this->provider);
    if (!$providerHandler->auth()) {
      return false;
    }

    return $providerHandler->getLandingsFromApi() !== null;
  }

  /**
   * @param $streamId
   * @return bool
   * @throws \yii\base\InvalidConfigException
   * @throws \yii\di\NotInstantiableException
   */
  public function isPostbackWorking()
  {
    /** @var ProviderSettingsKp $settings */
    $settings = $this->provider->getSettings();

    /** @var ApiDriverInterface $apiDriver */
    $apiDriver = Yii::$container->get(ApiDriverInterface::class, [
      'instance' => $settings->api_url,
    ]);

    $api = new KpApiClient($apiDriver);

    /** @var $promoModule Module*/
    $promoModule = Yii::$app->getModule('promo');

    $authRequest = new AuthRequest();
    $authRequest->email = $promoModule->getUserAuthEmail();
    $authRequest->hash = $promoModule->getUserAuthHash();

    if (!$api->auth($authRequest)) {
      return false;
    }

    return $api->testPostback($settings->streamId);
  }

}