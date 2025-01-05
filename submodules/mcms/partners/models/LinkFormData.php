<?php

namespace mcms\partners\models;

use mcms\api\models\OfferCategory;
use mcms\common\helpers\MultiLangSort;
use mcms\promo\components\api\GetCachedOfferCategories;
use mcms\promo\components\LandingOperatorPrices;
use mcms\promo\models\Operator;
use Yii;
use yii\base\Object;
use mcms\common\exceptions\ParamRequired;
use mcms\common\helpers\ArrayHelper;
use mcms\promo\models\LandingOperator;
use mcms\promo\models\Country;
use mcms\promo\models\Landing;
use mcms\promo\models\LandingUnblockRequest;

/**
 * Класс для получения вспомогательных данных для добавления ссылок в партнерке
 * @property \mcms\common\module\Module $modulePromo
 * @property array $countries
 * @property \mcms\promo\models\Operator $activeOperator
 * @property \yii\data\ActiveDataProvider $streams
 * @property \yii\data\ActiveDataProvider $domains
 * @property array $domainsItems
 * @property array $domainsGroupedItems
 * @property array $domainsGroupedOptions
 * @property array $landingCategories
 * @property array $offerCategories
 * @property \yii\data\ActiveDataProvider $trafficbackTypes
 * @property int $rebillValue
 * @property int $buyoutValue
 * @property int $accessByRequestValue
 * @property int $unblockedRequestStatusModerationValue
 * @property int $unblockedRequestStatusUnlockedValue
 * @property int $trafficbackTypeStaticValue
 * @property int $trafficbackTypeDynamicValue
 * @property array $countryPayTypes
 * @property array $operatorPayTypes
 * @property array $payTypes
 * @property array $countryOfferCategories
 * @property array $operatorOfferCategories
 * @property array $adsNetworks
 * @property array $adsNetworksItems
 * @property array $adsNetworksOptions
 * @property int $ipFormatRange
 * @property int $isSystemKeySystem
 * @property int $isSystemKeyParked
 */
class LinkFormData extends Object
{

  public $userId;
  private $_modulePromo;
  private $_countries;
  private $_activeOperator;
  private $_streams;
  private $_domains;
  private $_landingCategories;
  private $_offerCategories;
  private $_trafficbackTypes;
  private $_cachedPayTypesObject;
  private $_cachedOfferCategoriesObject;
  private $_payTypes;
  private $_adsNetworks;
  private $_isSystemKeySystem;
  private $_isSystemKeyParked;

  public function init()
  {
    parent::init();

    if (empty($this->userId)) {
      $exception = new ParamRequired();
      $exception->setParamField('userId');
      throw $exception;
    }
  }

  public function getModulePromo()
  {
    return $this->_modulePromo = ($this->_modulePromo ?: Yii::$app->getModule('promo'));
  }

  public function getCountries()
  {
    return $this->_countries = (
      $this->_countries
        ?: $this->modulePromo->api('cachedCountries', ['userId' => Yii::$app->user->id])->getResult()
    );
  }

  public function getActiveOperator()
  {
    if ($this->_activeOperator) {
      return $this->_activeOperator;
    }

    foreach ($this->countries as $country) {
      if ($country->activeLandingsCount > 0) {
        foreach ($country->activeOperator as $operator) {
          if ($operator->activeLandingsCount > 0 && !$operator->isTrafficBlocked()) {
            $this->_activeOperator = $operator;
            break;
          }
        }
      }

      if ($this->_activeOperator !== null) {
        break;
      }
    }

    return $this->_activeOperator;
  }

  public function getStreams()
  {
    return $this->_streams = ($this->_streams ?: $this->modulePromo->api('streams', [
      'conditions' => [
        'user_id' => $this->userId,
      ],
      'sort' => ['defaultOrder' => ['name' => SORT_ASC]]
    ])->getResult());
  }

  public function getDomains()
  {
    return $this->_domains ?: ($this->_domains = $this->modulePromo->api('domains', [
      'conditions' => [
        'user_id' => $this->userId,
        'system' => true,
        'onlyPartnerVisible' => true,
      ],
      'sort' => ['defaultOrder' => ['url' => SORT_ASC]]
    ])->setResultTypeDataProvider()->getResult()->getModels());
  }

  public function getIsSystemKeySystem()
  {
    return $this->_isSystemKeySystem !== null
      ? $this->_isSystemKeySystem
      : ($this->_isSystemKeySystem = $this->modulePromo->api('domains')->getIsSystemKeySystem());
  }

  public function getIsSystemKeyParked()
  {
    return $this->_isSystemKeyParked !== null
      ? $this->_isSystemKeyParked
      : ($this->_isSystemKeyParked = $this->modulePromo->api('domains')->getIsSystemKeyParked());
  }

  public function getDomainsItems()
  {
    return ArrayHelper::map($this->domains, 'id', 'url');
  }

  public function getDomainsGroupedItems()
  {
    /*
     * Сперва должны выводиться системные домены, затем - припаркованные
     * Если для припаркованных доменов в массиве отсутствует ключ, то группа не выводится и JS падает
     */
    return array_merge([$this->isSystemKeySystem => [], $this->isSystemKeyParked => []],
      ArrayHelper::map($this->domains, 'id', 'url', function ($domain) {
        return $domain->isSystemKey();
      }));
  }

  public function getLandingCategories()
  {
    return $this->_landingCategories = (
    $this->_landingCategories
      ?: MultiLangSort::sort(ArrayHelper::map($this->modulePromo->api('cachedLandingCategories')->getResult(), 'id', 'name'))
    );
  }

  public function getOfferCategories()
  {
    if (!$this->_offerCategories) {
      $offerCategoryIds = $this->getCachedOfferCategoriesObject()->getOfferCategories();

      $this->_offerCategories = MultiLangSort::sort(
        OfferCategory::getDropdownItems(true, $offerCategoryIds)
      );
    }

    return $this->_offerCategories;
  }

  /** @return array */
  public function getCountryOfferCategories()
  {
    return $this->getCachedOfferCategoriesObject()->getCountryOfferCategories();
  }

  /** @return array */
  public function getOperatorOfferCategories()
  {
    return $this->getCachedOfferCategoriesObject()->getOperatorOfferCategories();
  }

  /** @return GetCachedOfferCategories */
  private function getCachedOfferCategoriesObject()
  {
    return $this->_cachedOfferCategoriesObject = ($this->_cachedOfferCategoriesObject ?: $this->_modulePromo->api('cachedOfferCategories')->getResult());
  }

  public function getTrafficbackTypes()
  {
    return $this->_trafficbackTypes = ($this->_trafficbackTypes ?: $this->modulePromo->api('trafficbackTypes')->getResult());
  }

  public function getRebillValue()
  {
    return $this->modulePromo->api('profitTypes', ['type' => 'rebill'])->getResult();
  }

  public function getBuyoutValue()
  {
    return $this->modulePromo->api('profitTypes', ['type' => 'buyout'])->getResult();
  }

  public function getAccessByRequestValue()
  {
    return $this->modulePromo->api('landingAccessTypes', ['type' => 'request'])->getResult();
  }

  public function getUnblockedRequestStatusModerationValue()
  {
    return $this->modulePromo->api('landingUnblockRequestStatuses', ['status' => 'moderation'])->getResult();
  }

  public function getUnblockedRequestStatusUnlockedValue()
  {
    return $this->modulePromo->api('landingUnblockRequestStatuses', ['status' => 'unlocked'])->getResult();
  }

  public function getTrafficbackTypeStaticValue()
  {
    return $this->modulePromo->api('trafficbackTypes', ['type' => 'static'])->getResult();
  }

  public function getTrafficbackTypeDynamicValue()
  {
    return $this->modulePromo->api('trafficbackTypes', ['type' => 'dynamic'])->getResult();
  }

  private function getCachedPayTypesObject()
  {
    return $this->_cachedPayTypesObject = ($this->_cachedPayTypesObject ?: $this->_modulePromo->api('cachedLandingPayTypes')->getResult());
  }

  public function getCountryPayTypes()
  {
    return $this->getCachedPayTypesObject()->getCountryPayTypes();
  }

  public function getOperatorPayTypes()
  {
    return $this->getCachedPayTypesObject()->getOperatorPayTypes();
  }

  public function getPayTypes()
  {
    if ($this->_payTypes !== null) return $this->_payTypes;
    $payTypesWithContent = $this->getCachedPayTypesObject()->getPayTypes();

    return $this->_payTypes = array_filter($this->modulePromo->api('payTypes', [])->getResult(), function ($value) use (&$payTypesWithContent) {
      return in_array($value['id'], $payTypesWithContent);
    });
  }

  public function getAdsNetworks()
  {
    return $this->_adsNetworks = ($this->_adsNetworks ?: $this->_modulePromo->api('adsNetworks')->setResultTypeArray()->getResult());
  }

  public function getAdsNetworksItems()
  {
    return ArrayHelper::map($this->adsNetworks, 'id', 'name');
  }

  public function getIpFormatRange()
  {
    return $this->_modulePromo->api('ipList')->getIpFormatRange();
  }

  public function getIpFormatCidr()
  {
    return $this->_modulePromo->api('ipList')->getIpFormatCidr();
  }

  /**
   * Получаем активные типы оплаты лендингов для переданных моделей LandingOperator
   * @param array $landings
   * @return array
   */
  public function getLandingPayTypes($landings = [])
  {
    $payTypes = [];
    $landingOperatorIds = [];
    foreach ($landings as $landing) {
      $landingOperatorIds[] = ['landing_id' => $landing->landing_id, 'operator_id' => $landing->operator_id];
    }
    $landingOperators = LandingOperator::findActivePayTypes($landingOperatorIds);
    foreach ($landingOperators as $landingOperator) {
      $payTypes[$landingOperator->landing_id . '_' . $landingOperator->operator_id] = $landingOperator->activePayTypes;
    }
    return $payTypes;
  }

  /**
   * Получает активных операторов стран и активных лендингов этих операторов, в том числе и тех которые были разблокированы по запросу
   * @return array
   */
  public function getCountriesOperatorsActiveLandingsCount()
  {
    /** @var \mcms\partners\Module $partnersModule */
    $partnersModule = Yii::$app->getModule('partners');
    $isMergeLandings = $partnersModule->isMergeLandings();
    $countryIds = ArrayHelper::getColumn($this->countries, 'id');

//    $allowedLandings = LandingUnblockRequest::find()->where([
//      LandingUnblockRequest::tableName() . '.user_id' => Yii::$app->user->id,
//      LandingUnblockRequest::tableName() . '.status' => LandingUnblockRequest::STATUS_UNLOCKED
//    ])->select('landing_id');

//    $countries = Country::find()
//      ->with('activeOperator')
//      ->joinWith(['activeOperator.activeLandings' => function (\yii\db\ActiveQuery $query) use ($allowedLandings) {
//        $query->where([
//          'or',
//          ['!=', Landing::tableName() . '.access_type', Landing::ACCESS_TYPE_HIDDEN],
//          [Landing::tableName() . '.id' => $allowedLandings]
//        ]);
//      }],
//        true,
//        'INNER JOIN')
//      ->where([
//        Country::tableName() . '.id' => $countryIds,
//      ])
//      ->all();



    $userCurrency = Yii::$app->getModule('payments')
        ->api('getUserCurrency', ['userId' => Yii::$app->user->id])
        ->getResult()
      ;

    $landingOperatorsQuery = LandingOperator::find()
      ->joinWith([
          'operator',
          'operator.country',
          'landing',
          'landing.landingUnblockRequestCurrentUser' => function($q) {
            $q->onCondition(['user_id' => Yii::$app->user->getId()]);
          },
      ])
      ->where([
        Operator::tableName(). '.status' => Operator::STATUS_ACTIVE,
        Country::tableName() . '.status' => Country::STATUS_ACTIVE,
        Landing::tableName() . '.status' => Landing::STATUS_ACTIVE,
        'is_deleted' => 0,
        Country::tableName() . '.id' => $countryIds
      ])
      ->andWhere([
        'or',
        ['!=', Landing::tableName().'.access_type', Landing::ACCESS_TYPE_HIDDEN],
        LandingUnblockRequest::tableName() . '.landing_id is not null'
      ])
    ;

    $identicalOperatorLandings = [];
    $countriesOperatorsActiveLandingsCount = [];
    /** @var LandingOperator $landingOperator */
    foreach ($landingOperatorsQuery->each() as $landingOperator) {
      $prices = LandingOperatorPrices::create($landingOperator, Yii::$app->user->id);

      $operatorLandings[$landingOperator->operator_id][$landingOperator->landing_id] = [
        'days_hold' => $landingOperator->days_hold,
        'default_currency_id' => $landingOperator->default_currency_rebill_price,
        'buyout_price' => $prices->getCpaPrice($userCurrency),
        'rebill_price_usd' => $isMergeLandings ? $prices->getRebillPrice('usd') : $landingOperator->rebill_price_usd,
        'rebill_price_eur' => $isMergeLandings ? $prices->getRebillPrice('eur') : $landingOperator->rebill_price_eur,
        'rebill_price_rub' => $isMergeLandings ? $prices->getRebillPrice('rub') : $landingOperator->rebill_price_rub,
        'cost_price' => $landingOperator->cost_price,
        'subscription_type_id' => $landingOperator->subscription_type_id,
        'is_deleted' => $landingOperator->is_deleted,
      ];

      $key = $operatorLandings[$landingOperator->operator->id][$landingOperator->landing_id];
      $key['is_deleted'] = $key['is_deleted'] ? 1 : 0;
      $key = implode('_', $key);

      if (!isset($identicalOperatorLandings[$landingOperator->operator_id][$key])) {
        $identicalOperatorLandings[$landingOperator->operator_id][$key] = 1;
      } else {
        $identicalOperatorLandings[$landingOperator->operator_id][$key]++;
      }

      $countryId = $landingOperator->operator->country_id;
      if (!isset($countriesOperatorsActiveLandingsCount[$countryId][$landingOperator->operator_id])) {
        $countriesOperatorsActiveLandingsCount[$countryId][$landingOperator->operator_id] = 1;
      } else {
        $countriesOperatorsActiveLandingsCount[$countryId][$landingOperator->operator_id]++;
      }
    }

    foreach ($countriesOperatorsActiveLandingsCount as $countryId => $operators) {
      if (count($operators) === 1) {
        $countriesOperatorsActiveLandingsCount[$countryId]['hideOss'] = true;
        continue;
      }

      if (array_sum($operators) / count($operators) !== current($operators)) continue;

      $first = null;

      // проверить на совпадение
      foreach ($operators as $operatorId => $cnt) {
        $current = array_keys($identicalOperatorLandings[$operatorId]);
        if ($first === null) {
          $first = $current;
          continue;
        }

        if ($first !== $current) continue 2;
      }

      $countriesOperatorsActiveLandingsCount[$countryId]['hideOss'] = true;
    }

    return $countriesOperatorsActiveLandingsCount;


    $countriesOperatorsActiveLandingsCount = [];
    foreach ($countries as $country) {
      /* @var Country $country */
      $operatorLandings = [];
      foreach ($country->activeOperator as $operator) {
        /* @var Operator $operator */
        //TRICKY просто количество активных лендов нельзя доставать(есть активные, но скрытие ленды)
        //нужно проверять заявки на разблокировку для скрытых лендов
        $activeLandingsCount = $operator->getActiveLandingsCount(true);
        if ($activeLandingsCount > 0) {
          foreach ($operator->getActiveLandingsQuery(true)->each() as $landingOperator) {
            /* @var $landingOperatorModel $landingOperatorModel */
            $landingOperatorModel = LandingOperator::findOne([
              'landing_id' => $landingOperator['landing_id'],
              'operator_id' => $landingOperator['operator_id']
            ]);

            $prices = LandingOperatorPrices::create($landingOperatorModel, Yii::$app->user->id);

            $operatorLandings[$operator->id][$landingOperator['landing_id']] = [
              'days_hold' => $landingOperator['days_hold'],
              'default_currency_id' => $landingOperator['default_currency_rebill_price'],
              'buyout_price' => $prices->getCpaPrice($userCurrency),
              'rebill_price_usd' => $partnersModule->isMergeLandings() ? $prices->getRebillPrice('usd') : $landingOperator['rebill_price_usd'],
              'rebill_price_eur' => $partnersModule->isMergeLandings() ? $prices->getRebillPrice('eur') : $landingOperator['rebill_price_eur'],
              'rebill_price_rub' => $partnersModule->isMergeLandings() ? $prices->getRebillPrice('rub') : $landingOperator['rebill_price_rub'],
              'cost_price' => $landingOperator['cost_price'],
              'subscription_type_id' => $landingOperator['subscription_type_id'],
              'is_deleted' => $landingOperator['is_deleted'],
            ];
          }

          $countriesOperatorsActiveLandingsCount[$country->id][$operator->id] = $activeLandingsCount;
        }
      }

      if ($partnersModule->isMergeLandings()) {
        //если лендинги одинаковые скрываем блок ОСС
        $hideOss = true;
        foreach ($operatorLandings as $operatorId => $landings) {
          foreach ($landings as $landingId => $landing) {
            foreach ($country->activeOperator as $operator) {
              if (!isset($countriesOperatorsActiveLandingsCount[$country->id][$operator->id])) {
                continue;
              }
              if ($operatorId === $operator->id) {
                continue;
              }
              $compareLanding = ArrayHelper::getValue(ArrayHelper::getValue($operatorLandings, $operator->id), $landingId);
              if (!$compareLanding) {
                $hideOss = false;
                continue;
              }
              if ($compareLanding !== $landing) {
                $hideOss = false;
              }
            }
          }
        }


        if ($hideOss && isset($countriesOperatorsActiveLandingsCount[$country->id])) {
          $countriesOperatorsActiveLandingsCount[$country->id]['hideOss'] = true;
        }
      }
    }

    return $countriesOperatorsActiveLandingsCount;
  }
}
