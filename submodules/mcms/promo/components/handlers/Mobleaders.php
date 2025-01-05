<?php

namespace mcms\promo\components\handlers;

use mcms\promo\components\ProviderSync;
use mcms\promo\components\ProviderSyncInterface;
use mcms\common\helpers\curl\Curl;
use mcms\common\helpers\ArrayHelper;
use mcms\promo\models\Country;
use mcms\promo\models\Landing;
use mcms\promo\models\Operator;
use mcms\promo\models\OperatorIp;
use yii\helpers\BaseHtml;
use yii\helpers\Console;
use yii\helpers\FileHelper;
use Yii;

/**
 * Class Mobleaders
 * @package components\handlers
 */
class Mobleaders extends ProviderSync implements ProviderSyncInterface
{

  const ML_LAND_STATUS_OK = 'on';

  public $mobleadersUserId;
  public $apiUrl;

  /**
   * @inheritdoc
   */
  public function auth()
  {
    $settings = json_decode($this->providerModel->settings, true);

    $this->mobleadersUserId = $settings['mobleaders_user_id'];
    $this->apiUrl = rtrim($settings['api_url'], '/');

    if (empty($this->mobleadersUserId)) {
      $this->log('Error: Mobleaders User Id not exist!' . "\n", [Console::FG_RED]);
      return false;
    }

    return true;
  }


  /**
   * @inheritdoc
   */
  public function syncCountries($checkUpdateTime, $deleteInsteadDeactivate)
  {
    // На всех продах сделали чтоб крон делали полный синк. А для МЛ лучше пока не делать этого.
    // Решение временное пока не реализовали другой способ проверки на то что ленд изменился.
    $checkUpdateTime = 1;

    $countriesStr = $this->getCountriesFromApi();

    if (empty($countriesStr)) {
      $this->error('Sync countries failed, response is empty. Response:' . $countriesStr);
      return;
    }

    $countries = json_decode($countriesStr, true);
    if (!is_array($countries)) {
      $this->error('Sync countries failed, response is incorrect. Response:' . $countriesStr);
      return;
    }
    if (empty($countries)) {
      $this->warning('Sync countries is empty. Response:' . $countriesStr);
      return;
    }

    /** @noinspection PhpParamsInspection */
    $oldIds = ArrayHelper::getColumn(Country::find()->each(), 'id');

    $actualIds = [];
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

      if ($checkUpdateTime &&
        !$countryModel->isNewRecord &&
        (int)$countryModel->sync_updated_at >= (int)ArrayHelper::getValue($country, 'updated_at')
      ) {
        $actualIds[] = $country['id'];
        continue;
      }

      $countryModel->setAttributes([
        'name' => $country['name'],
        'code' => $country['iso'],
        'status' => $countryModel->isNewRecord ? Country::STATUS_ACTIVE : $countryModel->status,
        'sync_updated_at' => (int)ArrayHelper::getValue($country, 'updated_at')
      ]);
      $countryModel->id = $country['id'];
      if (!$countryModel->save()) {
        $this->error('Country save failed. Model:' . print_r($countryModel, true));
        return; // лучше выйти из метода на всякий случай, т.к. ниже идет деактивация стран
      }

      $actualIds[] = $country['id'];
    }

    $deactivateIds = array_diff($oldIds, $actualIds);

    if ($deleteInsteadDeactivate) {
      Country::deleteAll([
        'id' => $deactivateIds
      ]);
    } else {
      Country::updateAll([
        'status' => Country::STATUS_INACTIVE
      ], [
        'id' => $deactivateIds
      ]);
    }

  }
  /**
   * @inheritdoc
   */
  public function syncOperators($checkUpdateTime, $deleteInsteadDeactivate)
  {
    // На всех продах сделали чтоб крон делали полный синк. А для МЛ лучше пока не делать этого.
    // Решение временное пока не реализовали другой способ проверки на то что ленд изменился.
    $checkUpdateTime = 1;

    $operatorsStr = $this->getOperatorsFromApi();

    if (empty($operatorsStr)) {
      $this->error('Sync operators failed, response is empty. Response:' . $operatorsStr);
      return;
    }

    $operators = json_decode($operatorsStr, true);

    if (!is_array($operators)) {
      $this->error('Sync operators failed, response is incorrect. Response:' . $operatorsStr);
      return;
    }
    if (empty($operators)) {
      $this->warning('Sync operators is empty. Response:' . $operatorsStr);
      return;
    }

    /** @noinspection PhpParamsInspection */
    $oldIds = ArrayHelper::getColumn(Operator::find()->where(['status' => Operator::STATUS_ACTIVE])->each(), 'id');

    $actualIds = [];
    $newIds = [];
    foreach ($operators as $operator) {
      if ((int)$operator['id'] === 0) {
        continue;
      }
      $operatorModel = Operator::findOne($operator['id']) ?: new Operator();

      if ($checkUpdateTime &&
        !$operatorModel->isNewRecord &&
        (int)$operatorModel->sync_updated_at >= (int)ArrayHelper::getValue($operator, 'updated_at')
      ) {
        $actualIds[] = $operator['id'];
        continue;
      }

      $operatorModel->setAttributes([
        'name' => $operator['name'],
        'country_id' => $operator['country_id'],
        'created_by' => $this->getRootUserId(),
        'status' => $operatorModel->isNewRecord ? Operator::STATUS_ACTIVE : $operatorModel->status,
        'sync_updated_at' => (int)ArrayHelper::getValue($operator, 'updated_at'),
        'is_3g' => (int)ArrayHelper::getValue($operator, 'is_3g')
      ]);
      $operatorModel->id = $operator['id'];
      if (!$operatorModel->save()) {
        $this->error('Operator save failed. Model:' . print_r($operatorModel, true));
        return; // лучше выйти из метода на всякий случай, т.к. ниже идет деактивация операторов
      }

      $actualIds[] = $operator['id'];
      $newIds[] = $operator['id'];

      $this->syncOperatorIps($operator['id']);
    }

    $deactivateIds = array_diff($oldIds, $actualIds);

    if ($deleteInsteadDeactivate) {
      Operator::deleteAll(['id' => $deactivateIds]);
    } else {
      Operator::updateAll([
        'status' => Operator::STATUS_INACTIVE
      ], [
        'id' => $deactivateIds
      ]);
    }

    if (!empty($newIds) || !empty($deactivateIds)) {
      $this->clearCacheKeys[] = self::OPERATORS_CACHE_KEY;
    }
  }

  /**
   * @param $operatorId
   */
  private function syncOperatorIps($operatorId)
  {
    $operator = Operator::findOne($operatorId);

    if (!$operator || (int)$operator->status !== Operator::STATUS_ACTIVE) {
      return;
    }

    $operatorIpsStr = $this->sendPost($this->apiUrl . '/partners/api/get-operator-ips/', [
      'operator_id' => $operatorId,
      'id' => $this->mobleadersUserId
    ]);
    $objects = json_decode($operatorIpsStr, true);


    if (!is_array($objects) || empty($objects)) {
      $this->warning('Sync operator ' . $operatorId . ' ips empty');
      return;
    }

    $actualIps = [];
    foreach ($objects as $obj) {
      $fromIp = ArrayHelper::getValue($obj, 'from_ip');
      $mask = ArrayHelper::getValue($obj, 'mask');
      $toIp = ArrayHelper::getValue($obj, 'to_ip');

      $ipModel = new OperatorIp([
        'operator_id' => $operator->id,
        'from_ip' => long2ip($fromIp),
        'mask' => $mask,
        'to_ip' => long2ip($toIp)
      ]);

      if (!$ipModel->validate()) {
        $this->error("Can't synchronize ip of operator with id #{$operator->id}. Wrong data. " . PHP_EOL .
          'Errors OperatorIp model: ' . json_encode($ipModel->getErrors()) . PHP_EOL);
        continue;
      }

      $actualIps[] = [$operator->id, $fromIp, $mask, $toIp, 0];
    }

    if (!$actualIps) {
      OperatorIp::deleteAll(['operator_id' => $operator->id]);
      return;
    }

    OperatorIp::updateAll(['should_delete' => 1], ['operator_id' => $operator->id]);

    $db = Yii::$app->db;
    $sql = $db->queryBuilder->batchInsert(OperatorIp::tableName(), ['operator_id', 'from_ip', 'mask', 'to_ip', 'should_delete'], $actualIps);
    $db->createCommand($sql . ' ON DUPLICATE KEY UPDATE should_delete=0')->execute();

    OperatorIp::deleteAll(['operator_id' => $operator->id, 'should_delete' => 1]);

    $this->clearCacheKeys[] = self::OPERATORS_IPS_CACHE_KEY;
  }

  /**
   * @inheritdoc
   */
  public function syncLandings($checkUpdateTime)
  {
    $landingsStr = $this->getLandingsFromApi();

    $landings = json_decode($landingsStr, true);

    if (!is_array($landings) || empty($landings)) {
      $this->error('Sync landings failed, response is incorrect. Response:' . $landingsStr);
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

      if (!$landing && $landingObj['status'] !== self::ML_LAND_STATUS_OK) {
        $this->log('-> Landing is off, but not exist in DB -- skip' . PHP_EOL);
        continue;
      }

      if (!$landing) {
        $this->log('-> Landing with id: ' . $landingObj['id'] . ' not found. Creating new instance of Landing model' . PHP_EOL);
        $landing = new Landing();
      }

      if ($checkUpdateTime &&
        !$landing->isNewRecord &&
        (int)$landing->sync_updated_at >= (int)ArrayHelper::getValue($landingObj, 'updated_at')
      ) {
        $this->log('-> Checking update time -- skip' . PHP_EOL);
        continue;
      }

      $landing->scenario = Landing::SCENARIO_SYNC;

      $imgSrc = $landingUploadDir . $syncDir . '/' . $landingObj['screen'];

      if (!$this->ignoreCurl && ($landing->image_src !== $imgSrc || !file_exists($dir . $landingObj['screen']))) {
        $this->log('-> Landing img is empty -- downloading' . PHP_EOL);
        $curl = new Curl([
          'url' => $this->apiUrl . '/statics/uploads/sites/' . $landingObj['screen']
        ]);

        $file = $curl->getResult();

        if (ArrayHelper::getValue($curl->curlInfo, 'http_code') !== 200) {
          $this->warning('-> Downloading landing image -- image not found: http status: ' . ArrayHelper::getValue($curl->curlInfo, 'http_code') . PHP_EOL);
          continue; // пропускаем ленд если не получилось обновить картинку
        }
        file_put_contents($dir . $landingObj['screen'], $file);

        $landing->image_src = $imgSrc;
        $this->log('-> Downloading landing image -- image saved' . PHP_EOL);
      }

      $this->log('-> Setting landing attributes' . PHP_EOL);

      $promoMaterialsPath = ArrayHelper::getValue($landingObj, 'promo');

      $landing->setAttributes([
        'provider_id' => $this->providerModel->id,
        'name' => $landingObj['name'],
        'category_id' => $landingObj['category_id'],
        'created_by' => $this->getRootUserId(),
        'description' => $landingObj['description'],
        'send_id' => $landingObj['id'],
        'rating' => self::DEFAULT_LANDING_RATING,
        'sync_updated_at' => (int)ArrayHelper::getValue($landingObj, 'updated_at'),
        'rebill_period' => (int)ArrayHelper::getValue($landingObj, 'rebill_period'),
        'service_url' => ArrayHelper::getValue($landingObj, 'service_url'),
        'promo_materials' => $promoMaterialsPath
          ? $this->apiUrl . $promoMaterialsPath
          : null
      ]);

      if ($landingObj['status'] !== self::ML_LAND_STATUS_OK && !$landing->allow_sync_status) {
        $landing->allow_sync_status = 1;
      }

      if ($landing->isNewRecord || (!$landing->isNewRecord && $landing->allow_sync_status)) {
        // Если разрешена перезапись поля при синхронизации. Или если новый ленд
        $landing->status = $landingObj['status'] === self::ML_LAND_STATUS_OK ? Landing::STATUS_ACTIVE : Landing::STATUS_INACTIVE;
      }

      if ($landing->isNewRecord || (!$landing->isNewRecord && $landing->allow_sync_access_type)) {
        // Если разрешена перезапись поля при синхронизации. Или если новый ленд
        $landing->access_type = $landingObj['access_type'];
      }
      $toLandingId = ArrayHelper::getValue($landingObj, 'to_site_id');
      if ($toLandingId) {
        $landing->setAttribute('to_landing_id', $this->convertToLandingId($toLandingId));
      }
      if (($operatorsText = ArrayHelper::getValue($landingObj, 'operators_text')) && !empty($operatorsText)) {
        $landing->setAttribute('operators_text', $operatorsText);
      }
      $landing->platformIds = $this->convertPlatforms($landingObj);
      $landing->forbiddenTrafficTypeIds = $this->convertForbiddenTrafficTypes($landingObj);

      // landingsOperators
      $operators = ArrayHelper::getValue($landingObj, 'operators', []);

      $currency = ArrayHelper::getValue($landingObj, 'currency');

      if (!$currency || !isset($this->currencyConvert[$currency])) {
        $this->error('Error: landing#' . $landingObj['id'] . ' currency is_incorrect. LO:' . print_r($operators, true));
        continue; // переходим к другому ленду
      }

      $currencyCode = $this->currencyConvert[$currency];

      $landingOperatorData = [];

      $rebillPrice = $landingObj['profit'];

      foreach ($operators as $operator) {
        $buyoutPrice = $this->getBuyoutPrice($rebillPrice);

        $landingOperator = [
          'operator_id' => $operator['operator_id'],
          'default_currency_id' => $currency,
          'default_currency_rebill_price' => $rebillPrice,
          'local_currency_id' => $currency,
          'local_currency_rebill_price' => $rebillPrice,
          'rebill_price_usd' => $currencyCode === 'usd' ? $rebillPrice : 0,
          'rebill_price_eur' => $currencyCode === 'eur' ? $rebillPrice : 0,
          'rebill_price_rub' => $currencyCode === 'rub' ? $rebillPrice : 0,
          'subscription_type_id' => $this->convertSubType($operator),
          'payTypeIds' => $this->convertPayTypes($operator),
          'cost_price' => ArrayHelper::getValue($operator, 'cost_price'),
          'days_hold' => $this->isForcePartnerHold()
            ? $this->getForcedPartnerHoldDays()
            : (int)ArrayHelper::getValue($operator, 'days_hold'),
          'is_deleted' => 0,
        ];

        if ($landing->isNewRecord ||
          (!$landing->isNewRecord && $landing->allow_sync_buyout_prices)
        ) {
          // Если разрешена перезапись цен на выкуп при синхронизации. Или если новый ленд
          $landingOperator['buyout_price_usd'] = $currencyCode === 'usd' ? $buyoutPrice : 0;
          $landingOperator['buyout_price_eur'] = $currencyCode === 'eur' ? $buyoutPrice : 0;
          $landingOperator['buyout_price_rub'] = $currencyCode === 'rub' ? $buyoutPrice : 0;
        }

        $landingOperatorData[] = $landingOperator;
      }

      $landing->loadOperators(['LandingOperator' => $landingOperatorData]);

      if ($landing->isNewRecord) {
        $landingListId[$this->providerModel->code][] = $landing->send_id;
      }
      if (!$landing->save()) {
        $this->error('landing save fail: ' . BaseHtml::errorSummary($landing) . PHP_EOL);
        continue;
      }
      $this->log('-> Landing saved id: ' . $landing->id . PHP_EOL);
      $this->log('  ---' . PHP_EOL);
    }

    return $landingListId;

    // TRICKY: отключили, т.к. теперь все ленды приходят из МЛ, даже которые отключены MCMS-235
    /*$updateCount = Landing::updateAll(['status' => Landing::STATUS_INACTIVE], [
      'not in', 'id', ArrayHelper::getColumn($landings, 'id')
    ]);

    if ($updateCount) {
      $this->clearCacheKeys[] = self::LANDINGS_CACHE_KEY;
      $this->clearCacheKeys[] = self::LANDINGS_COUNT_CACHE_KEY;
    }*/
  }

  /**
   * @inheritdoc
   */
  public function syncRating()
  {
    $landingsStr = $this->getLandingsFromApi();

    $landings = json_decode($landingsStr, true);

    if (!is_array($landings) || empty($landings)) {
      $this->error('Sync landings rating failed, response is incorrect. Response:' . $landingsStr);
      return [];
    }

    $landingsRatingByCategory = [];
    $landingsLastSubByCategory = [];

    foreach ($landings as $landingObj) {
      $landing = Landing::find()
        ->joinWith('provider')
        ->where([
          'providers.code' => $this->providerModel->code,
          'send_id' => (int)$landingObj['send_id'],
          'landings.status' => Landing::STATUS_ACTIVE,
          'access_type' => Landing::ACCESS_TYPE_NORMAL,
        ])
        ->one();

      if (!$landing || $landingObj['status'] !== self::ML_LAND_STATUS_OK || (int)$landingObj['access_type'] !== Landing::ACCESS_TYPE_NORMAL) {
        $this->log('-> Landing is no active' . PHP_EOL);
        continue;
      }

      /* @var array $operators landingsOperators */
      $operators = ArrayHelper::getValue($landingObj, 'operators', []);

      foreach ($operators as $operator) {
        $landingsRatingByCategory[$landing->category_id][$operator['operator_id']][$landing->id] = (float)ArrayHelper::getValue($landingObj, 'rate', 0);
        $landingsLastSubByCategory[$landing->category_id][$operator['operator_id']][$landing->id] = (int)ArrayHelper::getValue($landingObj, 'sub_last_date');
      }
    }

    $this->updateTopLandings($landingsRatingByCategory, $landingsLastSubByCategory);
  }

  /**
   * @inheritdoc
   */
  public function syncExternalProviders(){}

  /**
   * @inheritdoc
   */
  public function syncCap(){}

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
      $this->apiUrl . '/partners/api/get-countries/',
      ['id' => $this->mobleadersUserId]
    );
  }

  /**
   * @inheritdoc
   */
  public function getOperatorsFromApi()
  {
    return $this->sendPost(
      $this->apiUrl . '/partners/api/get-operators/',
      ['id' => $this->mobleadersUserId]
    );
  }

  /**
   * @inheritdoc
   */
  public function getLandingsFromApi()
  {
    return $this->sendPost(
      $this->apiUrl . '/partners/api/get-sites/',
      ['id' => $this->mobleadersUserId]
    );
  }

  /**
   * @inheritdoc
   */
  public function getExternalProvidersFromApi()
  {
    return '';
  }

  /**
   * @inheritdoc
   */
  public function getCapFromApi()
  {
    return '';
  }

  /**
   * @inheritdoc
   */
  public function syncServices() {}

  /**
   * @inheritdoc
   */
  public function getServicesFromApi()
  {
    return '';
  }
}
