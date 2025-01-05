<?php

namespace mcms\promo\components\handlers;

use mcms\currency\components\PartnerCurrenciesProvider;
use mcms\promo\components\ProviderSync;
use mcms\promo\components\ProviderSyncInterface;
use mcms\common\helpers\curl\Curl;
use mcms\common\helpers\ArrayHelper;
use mcms\promo\models\Cap;
use mcms\promo\models\Country;
use mcms\promo\models\ExternalProvider;
use mcms\promo\models\Landing;
use mcms\promo\models\LandingOperator;
use mcms\promo\models\OfferCategory;
use mcms\promo\models\Operator;
use mcms\promo\models\OperatorIp;
use mcms\promo\models\Service;
use yii\helpers\BaseHtml;
use yii\helpers\Console;
use yii\helpers\FileHelper;
use Yii;
use yii\helpers\Json;
use yii\httpclient\Client;
use yii\httpclient\CurlTransport;
use yii\httpclient\Response;

/**
 * Class Mobleaders
 * @package components\handlers
 */
class KP extends ProviderSync implements ProviderSyncInterface
{
  const KP_LAND_STATUS_ACTIVE = 2;

  public $apiUrl;
  public $language;
  public $accessToken;

  /**
   * @inheritdoc
   */
  public function auth()
  {
    $settings = json_decode($this->providerModel->settings, true);
    $this->apiUrl = rtrim($settings['api_url'], '/') . '/api/v1/';
    $this->language = $settings['language'];

    $response = $this->sendPost(
      $this->apiUrl . 'user/auth',
      ['email' => $settings['email'], 'hash' => $settings['hash']]
    );
    $response = json_decode($response, true);
    $this->accessToken = ArrayHelper::getValue(ArrayHelper::getValue($response, 'data'), 'access_token');

    if (!$this->accessToken) {
      $this->error('Error: Auth ' . $this->providerModel->code . ' fail');
      return false;
    }

    return true;
  }

  /**
   * @inheritdoc
   */
  public function syncCountries($checkUpdateTime, $deleteInsteadDeactivate)
  {
    $countriesStr = $this->getCountriesFromApi();

    if ($countriesStr === null) {
      return;
    }

    $countries = json_decode($countriesStr, true);

    if (!is_array($countries) || empty($countries)) {
      $this->error('Sync ' . $this->providerModel->code . ' countries failed, response is incorrect. Response:' . $countriesStr);
      return;
    }

    $countries = ArrayHelper::getValue($countries, 'data');

    if (!is_array($countries)) {
      $this->error('Sync ' . $this->providerModel->code . ' countries failed, response is incorrect. Response:' . $countriesStr);
      return;
    }

    if (empty($countries)) {
      $this->warning('Sync ' . $this->providerModel->code . ' countries response empty:' . $countriesStr);
      return;
    }


    foreach ($countries as $country) {
      if ((int)$country['id'] === 0) {
        continue;
      }
      $countryModel = Country::findOne($country['id']) ?: new Country();

      if ($countryModel->isNewRecord) {
        $countryModel->currency = 'eur';
        $countryModel->local_currency = 'eur';
        Yii::error(
          'New country imported from provider=' . $this->providerModel->code . '. Currency and local currency 
          is set to `eur`',
          __METHOD__
        );
      }

      $countryModel->setAttributes([
        'name' => $countryModel->isNewRecord ? $country['name'] : $countryModel->name,
        'code' => $countryModel->isNewRecord ? $country['code'] : $countryModel->code,
        'status' => $countryModel->isNewRecord ? Country::STATUS_ACTIVE : $countryModel->status,
        'sync_updated_at' => (int)ArrayHelper::getValue($country, 'updated_at')
      ]);
      $countryModel->id = $country['id'];
      if (!$countryModel->save()) {
        $this->error('Country save failed. Model:' . print_r($countryModel, true));
      }
    }
  }

  /**
   * @inheritdoc
   */
  public function syncOperators($checkUpdateTime, $deleteInsteadDeactivate)
  {
    $operatorsStr = $this->getOperatorsFromApi();

    if ($operatorsStr === null) {
      return;
    }

    $operators = json_decode($operatorsStr, true);

    if (!is_array($operators) || empty($operators)) {
      $this->error('Sync' . $this->providerModel->code . ' operators failed, response is incorrect. Response:' . $operatorsStr);
      return;
    }

    $operators = ArrayHelper::getValue($operators, 'data');

    if (!is_array($operators)) {
      $this->error('Sync ' . $this->providerModel->code . ' operators failed, response is incorrect. Response:' . $operatorsStr);
      return;
    }

    if (empty($operators)) {
      $this->warning('Sync' . $this->providerModel->code . ' operators empty. Response:' . $operatorsStr);
      return;
    }

    foreach ($operators as $operator) {
      if ((int)$operator['id'] === 0) {
        continue;
      }
      $operatorModel = Operator::findOne($operator['id']) ?: new Operator();

      if ((int)$operator['country_id'] !== $operatorModel->country_id) {
        Yii::error(
          'Operator imported from provider=' . $this->providerModel->code . ' has wrong country',
          __METHOD__
        );
      }

      $operatorModel->setAttributes([
        'name' => $operatorModel->isNewRecord ? $operator['name'] : $operatorModel->name,
        'country_id' => $operatorModel->isNewRecord ? $operator['country_id'] : $operatorModel->country_id,
        'created_by' => $this->getRootUserId(),
        'status' => $operatorModel->isNewRecord ? Operator::STATUS_ACTIVE : $operatorModel->status,
        'sync_updated_at' => (int)ArrayHelper::getValue($operator, 'updated_at'),
        'is_3g' => (int)ArrayHelper::getValue($operator, 'is_3g')
      ]);
      $operatorModel->id = $operator['id'];
      if (!$operatorModel->save()) {
        $this->error('Operator save failed. Model:' . print_r($operatorModel, true));
        continue;
      }

      $this->syncOperatorIps($operator['id'], $operator['network']);
    }
  }

  /**
   * @param $operatorId
   * @param $operatorIps
   */
  private function syncOperatorIps($operatorId, $operatorIps)
  {
    $operator = Operator::findOne($operatorId);

    if (!$operator || (int)$operator->status !== Operator::STATUS_ACTIVE) {
      return;
    }

    if (!is_array($operatorIps) || empty($operatorIps)) {
      $this->warning('Sync operator ' . $operatorId . ' ips empty');
      return;
    }

    foreach ($operatorIps as $obj) {
      $fromIp = ArrayHelper::getValue($obj, 'from_ip');
      $mask = ArrayHelper::getValue($obj, 'mask');
      $toIp = ArrayHelper::getValue($obj, 'to_ip');

      $ipModel = new OperatorIp([
        'operator_id' => $operator->id,
        'from_ip' => $fromIp,
        'mask' => $mask,
        'to_ip' => $toIp
      ]);

      if (!$ipModel->validate()) {
        $this->error("Can't synchronize ip of operator with id #{$operator->id}. Wrong data. " . PHP_EOL .
          'Errors OperatorIp model: ' . json_encode($ipModel->getErrors()) . PHP_EOL);
        continue;
      }
    }

  }

  /**
   * TODO раскостылить, переделать на нормальную архитектуру
   * Обработчик данных -> провайдер данных -> получатель данных
   *
   * @param $checkUpdateTime
   * @return int[]
   */
  public function syncLandings($checkUpdateTime)
  {
    $page = 1;
    $totalPageCount = 1;
    $landingListId = [];

    while ($totalPageCount >= $page) {
      $response = $this->getResponse(
        $this->apiUrl . 'landings?' . http_build_query(['access-token' => $this->accessToken, 'page' => $page])
      );

      if ($response === null) {
        break;
      }

      /** @see ProviderSync::sendPost() */
      if (!$response->isOk) {
        Yii::error('Api ' . $this->providerModel->code . ' failed: http_code=' . $response->statusCode . '; response=' . $response, __METHOD__);
        break;
      }

      $response->setFormat(Client::FORMAT_JSON);

      if (empty($response->data)) {
        Yii::error('Api ' . $this->providerModel->code . ' failed: response is empty', __METHOD__);
        break;
      }

      $landingListId = array_merge($landingListId, $this->syncLandingsInternal($response->data, $checkUpdateTime));

      $totalPageCount = $response->headers->get('X-Pagination-Page-Count', 1);
      $page++;
    };

    return $landingListId;
  }

  /**
   * @inheritdoc
   */
  protected function syncLandingsInternal($landingsResponse, $checkUpdateTime)
  {
    if ($landingsResponse === null) {
      return [];
    }

    if (!is_array($landingsResponse) || empty($landingsResponse)) {
      $this->error('Sync ' . $this->providerModel->code . ' landings failed, response is incorrect. Response:' . $landingsResponse);
      return [];
    }

    $landings = ArrayHelper::getValue($landingsResponse, 'data');

    if (!is_array($landings)) {
      $this->log('Sync landings failed, response is incorrect. Response:' . $landingsResponse);
      return [];
    }

    $landingUploadDir = '/uploads/promo/landing/';
    $syncDir = 'sync';

    $dir = __DIR__ . '/../../../../../web' . $landingUploadDir . $syncDir . '/';
    FileHelper::createDirectory($dir);

    $landingListId = [];
    foreach ($landings as $landingObj) {
      $this->log('Processing landing with id: ' . $landingObj['id'] . PHP_EOL);
      $landing = Landing::find()
        ->joinWith('provider')
        ->where(['providers.code' => $this->providerModel->code, 'send_id' => $landingObj['id']])
        ->one();

      if (!$landing && $landingObj['status'] !== self::KP_LAND_STATUS_ACTIVE) {
        $this->log('-> Landing is off, but not exist in DB -- skip' . PHP_EOL);
        continue;
      }

      if (!$landing) {
        $this->log('-> Landing with id: ' . $landingObj['id'] . ' not found. Creating new instance of Landing model' . PHP_EOL);
        $landing = new Landing();
        $landing->offer_category_id = OfferCategory::DEFAULT_CATEGORY_ID;
      }

      if ($checkUpdateTime &&
        !$landing->isNewRecord &&
        (int)$landing->sync_updated_at >= (int)ArrayHelper::getValue($landingObj, 'updated_at')
      ) {
        $this->log('-> Checking update time -- skip' . PHP_EOL);
        continue;
      }

      $landing->scenario = Landing::SCENARIO_SYNC;

      $baseImgName = basename($landingObj['image']);

      $imgSrc = $landingUploadDir . $syncDir . '/' . $baseImgName;

      if (!$this->ignoreCurl && ($landing->image_src !== $imgSrc || !file_exists($dir . $baseImgName))) {
        $this->warning('-> Landing img is empty -- downloading' . PHP_EOL);
        $curl = new Curl([
          'url' => $landingObj['image']
        ]);

        $file = $curl->getResult();

        if (ArrayHelper::getValue($curl->curlInfo, 'http_code') !== 200) {
          $this->log('-> Downloading landing image -- image not found: http status: ' . ArrayHelper::getValue($curl->curlInfo, 'http_code') . PHP_EOL);
          continue; // пропускаем ленд если не получилось обновить картинку
        }
        file_put_contents($dir . basename($landingObj['image']), $file);

        $landing->image_src = $imgSrc;
        $this->log('-> Downloading landing image -- image saved' . PHP_EOL);
      }

      $this->log('-> Setting landing attributes' . PHP_EOL);

      $landing->setAttributes([
        'provider_id' => $this->providerModel->id,
        'name' => $landingObj['name'][$this->language],
        'category_id' => $landingObj['category_id'],
        'created_by' => $this->getRootUserId(),
        'description' => $landingObj['description'][$this->language],
        'send_id' => (string)$landingObj['id'],
        'rating' => self::DEFAULT_LANDING_RATING,
        'sync_updated_at' => (int)ArrayHelper::getValue($landingObj, 'updated_at'),
        'service_url' => ArrayHelper::getValue($landingObj, 'service_url'),
      ]);

      if ($landingObj['status'] !== self::KP_LAND_STATUS_ACTIVE && !$landing->allow_sync_status) {
        $landing->allow_sync_status = 1;
      }

      if ($landing->isNewRecord || (!$landing->isNewRecord && $landing->allow_sync_status)) {
        // Если разрешена перезапись поля при синхронизации. Или если новый ленд
        $landing->status = $landingObj['status'] === self::KP_LAND_STATUS_ACTIVE ? Landing::STATUS_ACTIVE : Landing::STATUS_INACTIVE;
      }

      if ($landing->isNewRecord || (!$landing->isNewRecord && $landing->allow_sync_access_type)) {
        // Если разрешена перезапись поля при синхронизации. Или если новый ленд
        $landing->access_type = $landingObj['access_type'];
      }

      $toLandingId = ArrayHelper::getValue($landingObj, 'to_landing_id');

      if ($toLandingId) {
        $landing->setAttribute('to_landing_id', $this->convertToLandingId($toLandingId));
      }
      if (($operatorsText = ArrayHelper::getValue($landingObj, 'operators_text')) && !empty($operatorsText)) {
        $landing->setAttribute('operators_text', $operatorsText);
      }

      $landing->platformIds = unserialize(ArrayHelper::getValue($landingObj, 'allowed_platforms'));
      $landing->forbiddenTrafficTypeIds = $this->convertForbiddenTrafficTypes($landingObj);

      /* @var array $operators landingsOperators */
      $operators = ArrayHelper::getValue($landingObj, 'operators', []);

      $landingOperatorData = [];

      foreach ($operators as $operator) {
        $localCurrencyCode = ArrayHelper::getValue($operator, 'currency_default');
        $localCurrency = array_search(strtolower($localCurrencyCode), $this->currencyConvert, null);

        if (!$localCurrency) {
          $this->error('Error: landing#' . $landingObj['id'] . ' local currency is incorrect. LO:' . print_r($operator, true));
          continue; // переходим к другому ленду
        }

        $partnerCurrenciesProvider = PartnerCurrenciesProvider::getInstance();

        $currencyProvider = $partnerCurrenciesProvider
          ->getCurrencies()
          ->getCurrencyById($localCurrency);


        $localRebillPrice = $operator['price_default'];
        $localBuyoutPrice = $this->getBuyoutPrice($localRebillPrice);

        $rebillPriceEur = $currencyProvider->convertToEur($localRebillPrice);
        $buyoutPriceEur = $currencyProvider->convertToEur($localBuyoutPrice);

        $landingOperator = [
          'operator_id' => $operator['id'],
          'default_currency_id' => LandingOperator::DEFAULT_CURRENCY_ID,
          'default_currency_rebill_price' => $rebillPriceEur,
          'local_currency_id' => $localCurrency,
          'local_currency_rebill_price' => $localRebillPrice,
          'rebill_price_usd' => 0,
          'rebill_price_eur' => $rebillPriceEur,
          'rebill_price_rub' => 0,
          'subscription_type_id' => $this->convertSubType(['subscr_type' => $operator['subscription_type_id']]),
          'payTypeIds' => $this->convertPayTypes($operator),
          'cost_price' => ArrayHelper::getValue($operator, 'incoming'),
          'days_hold' => $this->isForcePartnerHold()
            ? $this->getForcedPartnerHoldDays()
            : (int)ArrayHelper::getValue($operator, 'hold'),
          'is_deleted' => $operator['status'] === 1 ? 0 : 1,
        ];

        if ($landing->isNewRecord ||
          (!$landing->isNewRecord && $landing->allow_sync_buyout_prices)
        ) {
          // Если разрешена перезапись цен на выкуп при синхронизации. Или если новый ленд
          $landingOperator['buyout_price_usd'] = $localCurrencyCode === 'usd' ? $localBuyoutPrice : 0;

          //Если дефолтная валюта евро и локальная не рубли и баксы записываем выкуп в евро, в другом случае в локальной
          $landingOperator['buyout_price_eur'] = !in_array($localCurrencyCode, ['rub', 'usd'], true)
            ? $buyoutPriceEur
            : 0;

          $landingOperator['buyout_price_rub'] = $localCurrencyCode === 'rub' ? $localBuyoutPrice : 0;
        }

        $landingOperatorData[] = $landingOperator;
      }


      $landing->loadOperators(['LandingOperator' => $landingOperatorData]);


      if ($landing->isNewRecord) {
        $landingListId[$this->providerModel->code][] = $landing->send_id;
      }
      if (!$landing->save()) {
        $this->error('Landing save fail: ' . BaseHtml::errorSummary($landing));
        continue;
      }
      $this->log('-> Landing saved id: ' . $landing->id . PHP_EOL);
      $this->log('  ---' . PHP_EOL);
    }

    //лендинги для отключения
    $landingsToDisable = Landing::find()
      ->andWhere(['status' => Landing::STATUS_ACTIVE, 'provider_id' => $this->providerModel->id])
      ->andWhere(['not in', 'send_id', ArrayHelper::getColumn($landings, 'id')]);

    foreach ($landingsToDisable->each() as $landingToDisable) {
      $this->log('Disable landing  id: ' . $landingToDisable->id . PHP_EOL);
      $landingToDisable->scenario = Landing::SCENARIO_SYNC;
      $landingToDisable->status = Landing::STATUS_INACTIVE;
      $landingToDisable->save();
    }

    if ($landingsToDisable->count()) {
      $this->clearCacheKeys[] = self::LANDINGS_CACHE_KEY;
      $this->clearCacheKeys[] = self::LANDINGS_COUNT_CACHE_KEY;
    }

    return $landingListId;
  }

  /**
   * @inheritdoc
   */
  public function syncRating()
  {
    $landingsStr = $this->getLandingsFromApi();

    if ($landingsStr === null) {
      return [];
    }

    $landings = json_decode($landingsStr, true);

    if (!is_array($landings) || empty($landings)) {
      $this->error('Sync ' . $this->providerModel->code . ' landings rating failed, response is incorrect. Response:' . $landingsStr);
      return [];
    }

    $landings = ArrayHelper::getValue($landings, 'data');

    if (!is_array($landings)) {
      $this->log('Sync landings rating failed, response is incorrect. Response:' . $landingsStr);
      return [];
    }

    if (empty($landings)) {
      $this->log('Sync landings rating empty. Response:' . $landingsStr);
      return [];
    }

    $landingsRatingByCategory = [];
    $landingsLastSubByCategory = [];

    foreach ($landings as $landingObj) {
      $landing = Landing::find()
        ->joinWith('provider')
        ->where([
          'providers.code' => $this->providerModel->code,
          'send_id' => $landingObj['id'],
          'landings.status' => Landing::STATUS_ACTIVE,
          'access_type' => Landing::ACCESS_TYPE_NORMAL
        ])
        ->one();

      if (!$landing || $landingObj['status'] !== self::KP_LAND_STATUS_ACTIVE || $landingObj['access_type'] !== Landing::ACCESS_TYPE_NORMAL) {
        $this->log('-> Landing is not active' . PHP_EOL);
        continue;
      }

      /* @var array $operators landingsOperators */
      $operators = ArrayHelper::getValue($landingObj, 'operators', []);

      foreach ($operators as $operator) {
        $landingsRatingByCategory[$landing->category_id][$operator['id']][$landing->id] = (float)ArrayHelper::getValue($landingObj, 'rate', 0);
        $landingsLastSubByCategory[$landing->category_id][$operator['id']][$landing->id] = (int)ArrayHelper::getValue($landingObj, 'sub_last_date');
      }
    }

    $this->updateTopLandings($landingsRatingByCategory, $landingsLastSubByCategory);
  }

  /**
   * @inheritdoc
   */
  public function syncExternalProviders()
  {
    $time = time();
    $externalProvidersStr = $this->getExternalProvidersFromApi();

    if ($externalProvidersStr === null) {
      return;
    }

    $externalProviders = Json::decode($externalProvidersStr);

    if (!is_array($externalProviders) || empty($externalProviders)) {
      $this->error('Sync' . $this->providerModel->code . ' external providers failed, response is incorrect. Response:' . $externalProvidersStr);
      return;
    }

    $externalProviders = ArrayHelper::getValue($externalProviders, 'data');

    if (!is_array($externalProviders)) {
      $this->error('Sync ' . $this->providerModel->code . ' external providers failed, response is incorrect. Response:' . $externalProvidersStr);
      return;
    }

    if (empty($externalProviders)) {
      $this->warning('Sync' . $this->providerModel->code . ' external providers empty. Response:' . $externalProvidersStr);
      return;
    }

    foreach ($externalProviders as $externalProvider) {
      if ((int)$externalProvider['id'] === 0) {
        continue;
      }
      $externalProviderModel = ExternalProvider::find()->andWhere([
        'external_id' => $externalProvider['id'],
        'provider_id' => $this->providerModel->id,
      ])->one() ?: new ExternalProvider();

      // Определяем страну по первому попавшемуся оператору провайдера
      $externalProviderOperator = Operator::findOne(['id' => ArrayHelper::getValue($externalProvider, 'operatorIds')]);

      $externalProviderModel->setAttributes([
        'external_id' => $externalProvider['id'],
        'country_id' => $externalProviderOperator ? $externalProviderOperator->country_id : null,
        'name' => $externalProvider['name'],
        'url' => $externalProvider['url'],
        'status' => ExternalProvider::STATUS_ACTIVE,
        'provider_id' => $this->providerModel->id,
        'sync_at' => time(),
      ]);

      if (!$externalProviderModel->save()) {
        $this->error('External provider save failed. Model:' . print_r($externalProviderModel, true));
        continue;
      }
    }

    //провайдеры для отключения
    $providersToDisable = ExternalProvider::find()
      ->andWhere(['status' => ExternalProvider::STATUS_ACTIVE, 'provider_id' => $this->providerModel->id])
      ->andWhere(['<', 'sync_at', $time]);

    foreach ($providersToDisable->all() as $providerToDisable) {
      $providerToDisable->status = ExternalProvider::STATUS_INACTIVE;
      $providerToDisable->save();
    }
  }

  /**
   * @inheritdoc
   */
  public function syncServices()
  {
    $time = time();
    $servicesStr = $this->getServicesFromApi();

    if ($servicesStr === null) {
      return;
    }

    $services = Json::decode($servicesStr);

    if (!is_array($services) || empty($services)) {
      $this->error('Sync' . $this->providerModel->code . ' Services failed, response is incorrect. Response:' . $servicesStr);
      return;
    }

    $services = ArrayHelper::getValue($services, 'data');

    if (!is_array($services)) {
      $this->error('Sync ' . $this->providerModel->code . ' Services failed, response is incorrect. Response:' . $servicesStr);
      return;
    }

    if (empty($services)) {
      $this->warning('Sync ' . $this->providerModel->code . ' Services empty. Response:' . $servicesStr);
      return;
    }

    foreach ($services as $service) {
      if ((int)$service['id'] === 0) {
        continue;
      }
      $serviceModel = Service::find()->andWhere([
        'external_id' => $service['id'],
        'provider_id' => $this->providerModel->id,
      ])->one() ?: new Service();

      $serviceModel->setAttributes([
        'external_id' => $service['id'],
        'name' => $service['name'],
        'url' => $service['url'],
        'provider_id' => $this->providerModel->id,
        'status' => Service::STATUS_ACTIVE,
        'sync_at' => time(),
      ]);

      if (!$serviceModel->save()) {
        $this->error('Service save failed. Model:' . print_r($serviceModel, true));
        continue;
      }
    }

    // сервисы для отключения
    $servicesToDisable = Service::find()
      ->andWhere(['status' => Service::STATUS_ACTIVE, 'provider_id' => $this->providerModel->id])
      ->andWhere(['<', 'sync_at', $time]);

    foreach ($servicesToDisable->all() as $serviceToDisable) {
      $serviceToDisable->status = Service::STATUS_INACTIVE;
      $serviceToDisable->save();
    }
  }

  /**
   * @inheritdoc
   */
  public function syncCap()
  {
    $time = time();
    $capStr = $this->getCapFromApi();

    if ($capStr === null) {
      return;
    }

    $caps = Json::decode($capStr);

    if (!is_array($caps) || empty($caps)) {
      $this->error('Sync' . $this->providerModel->code . ' CAP failed, response is incorrect. Response:' . $capStr);
      return;
    }

    $caps = ArrayHelper::getValue($caps, 'data');

    if (!is_array($caps)) {
      $this->error('Sync ' . $this->providerModel->code . ' CAP failed, response is incorrect. Response:' . $capStr);
      return;
    }

    if (empty($caps)) {
      $this->warning('Sync ' . $this->providerModel->code . ' CAP empty. Response:' . $capStr);
      return;
    }

    foreach ($caps as $cap) {
      if ((int)$cap['id'] === 0) {
        continue;
      }
      $capModel = Cap::find()->andWhere([
        'external_id' => $cap['id'],
        'provider_id' => $this->providerModel->id,
      ])->one() ?: new Cap();

      $externalProvider = ExternalProvider::find()->andWhere([
        'external_id' => $cap['provider_id'],
        'provider_id' => $this->providerModel->id,
      ])->one();

      $service = Service::find()->andWhere([
        'external_id' => $cap['service_id'],
        'provider_id' => $this->providerModel->id,
      ])->one();

      if (!$externalProvider && $cap['provider_id']) {
        $this->warning('External provider not found. Data: ' . print_r($cap, true));
        continue;
      }

      if (!$service && $cap['service_id']) {
        $this->warning('Service not found. Data: ' . print_r($cap, true));
        continue;
      }

      $capModel->setAttributes([
        'external_id' => $cap['id'],
        'day_limit' => $cap['day_limit'],
        'external_provider_id' => $externalProvider ? $externalProvider->id : null,
        'operator_id' => $cap['operator_id'],
        'service_id' => $service ? $service->id : null,
        'landing_id' => $cap['landing_id'],
        'active_from' => $cap['active_from'],
        'is_blocked' => $cap['is_blocked'],
        'status' => Cap::STATUS_ACTIVE,
        'provider_id' => $this->providerModel->id,
        'sync_at' => time(),
      ]);

      if (!$capModel->save()) {
        $this->error('CAP save failed. Model:' . print_r($capModel, true));
        continue;
      }
    }

    //CAP для отключения
    $capsToDisable = Cap::find()
      ->andWhere(['status' => Cap::STATUS_ACTIVE, 'provider_id' => $this->providerModel->id])
      ->andWhere(['<', 'sync_at', $time]);

    foreach ($capsToDisable->all() as $capToDisable) {
      $capToDisable->status = Cap::STATUS_INACTIVE;
      $capToDisable->save();
    }
  }

  /**
   * @inheritdoc
   */
  public function error($text)
  {
    $this->log($text . PHP_EOL, [Console::FG_RED]);
    Yii::error('Provider ' . $this->providerModel->code . ' sync error: ' . $text, __METHOD__);
  }

  /**
   * @inheritdoc
   */
  public function warning($text)
  {
    $this->log($text . PHP_EOL, [Console::FG_YELLOW]);
    Yii::warning('Provider ' . $this->providerModel->code . ' sync warning: ' . $text, __METHOD__);
  }

  /**
   * @inheritdoc
   */
  public function getCountriesFromApi()
  {
    return $this->sendPost(
      $this->apiUrl . 'countries?' . http_build_query(['access-token' => $this->accessToken])
    );
  }

  /**
   * @inheritdoc
   */
  public function getOperatorsFromApi()
  {
    return $this->sendPost(
      $this->apiUrl . 'operators?' . http_build_query(['access-token' => $this->accessToken])
    );
  }

  /**
   * @inheritdoc
   */
  public function getLandingsFromApi()
  {
    return $this->sendPost(
      $this->apiUrl . 'landings?' . http_build_query(['access-token' => $this->accessToken])
    );
  }

  /**
   * @inheritdoc
   */
  public function getExternalProvidersFromApi()
  {
    return $this->sendPost(
      $this->apiUrl . 'providers?' . http_build_query(['access-token' => $this->accessToken])
    );
  }

  /**
   * @inheritdoc
   */
  public function getCapFromApi()
  {
    return $this->sendPost(
      $this->apiUrl . 'cap?' . http_build_query(['access-token' => $this->accessToken])
    );
  }

  /**
   * @inheritdoc
   */
  public function getServicesFromApi()
  {
    return $this->sendPost(
      $this->apiUrl . 'services?' . http_build_query(['access-token' => $this->accessToken])
    );
  }

  /**
   * @param $url
   * @param string $method
   * @param array $data
   * @return Response
   */
  protected function getResponse($url, $method = 'post', $data = [])
  {
    $client = Yii::createObject(Client::class);
    $client->setTransport(CurlTransport::class);

    $request = $client->createRequest()
      ->setMethod($method)
      ->setUrl($url)
      ->setOptions([
        'timeout' => ArrayHelper::getValue(Yii::$app->params, 'sync_curl_timeout', self::DEFAULT_CURL_TIMEOUT),
      ]);

    $data && $request->setData($data);

    try {
      $response = $request->send();
    } catch (\Exception $e) {
      return null;
    }

    return $response;
  }
}
