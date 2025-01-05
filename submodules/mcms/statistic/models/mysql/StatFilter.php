<?php

namespace mcms\statistic\models\mysql;

use mcms\promo\components\api\CountryList;
use mcms\promo\models\Country;
use mcms\user\models\User;
use mcms\user\Module as UserModule;
use Yii;
use yii\base\Model;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\db\Query;
use kak\clickhouse\Query as ClickhouseQuery;
use yii\helpers\ArrayHelper;

class StatFilter extends Model
{
  public $user_id;
  public $landing_id;
  public $operator_id;
  public $platform_id;
  public $landing_pay_type_id;
  public $provider_id;
  public $source_id;
  public $stream_id;
  public $country_id;

  const TABLE = 'stat_filters';

  public function rules()
  {
    return [
      [['user_id', 'landing_id', 'country_id', 'operator_id', 'platform_id', 'landing_pay_type_id', 'provider_id', 'source_id', 'stream_id'], 'default' => 0],
      [['user_id', 'landing_id', 'country_id', 'operator_id', 'platform_id', 'landing_pay_type_id', 'provider_id', 'source_id', 'stream_id'], 'number']
    ];
  }


  public function getStatFilters()
  {
    $query = (new ClickhouseQuery())
      ->from(['sf' => self::TABLE])
    ;

    $query->andFilterWhere([
      'user_id' => $this->user_id,
      'landing_id' => $this->landing_id,
      'operator_id' => $this->operator_id,
      'country_id' => $this->country_id,
      'platform_id' => $this->platform_id,
      'landing_pay_type_id' => $this->landing_pay_type_id,
      'provider_id' => $this->provider_id,
      'source_id' => $this->source_id,
      'stream_id' => $this->stream_id,
    ])
    ->orderBy(new Expression('NULL'));

    $query->groupBy(array_keys($this->attributes));

    Yii::beginProfile('getData', self::class);
    $res = $query->all(Yii::$app->clickhouse);
    Yii::endProfile('getData', self::class);
    return $this->indexResult($res);
  }

  private function indexResult(array $rawResult)
  {
    Yii::beginProfile('indexResult', self::class);
    $result = array_map(function($attribute) use ($rawResult) {
      return array_filter(array_column($rawResult, $attribute));
    }, array_combine($this->attributes(), $this->attributes()));
    Yii::endProfile('indexResult', self::class);
    return $result;
  }

  /**
   * @param string $columnName поле статфильтра в бд
   * @param null $userId
   * @param null|string $currency
   * @return ClickhouseQuery
   */
  private static function createListQuery($columnName, $userId = null, $currency = null)
  {
    $additionalConditions = [];
    if ($userId) {
      $additionalConditions['t.user_id'] = $userId;
    }

    //если передали валюту достаем записи только со странами с этой валютой указанными в конфиге

    $addCountryIds = self::getCountryIdsFilteredByCurrency($currency);

    $additionalConditions['t.country_id'] = !empty($addCountryIds) ? $addCountryIds : null;

    $columnNameWithPrefix = 't.' . $columnName;

    return (new ClickhouseQuery())
      ->distinct()
      ->select($columnNameWithPrefix)
      ->where(sprintf('%s > 0', $columnNameWithPrefix))
      ->andFilterWhere($additionalConditions)
      ->from([self::TABLE . ' t'])
      ->indexBy($columnName);
  }

  /**
   * TRICKY такой же код в @see CountryList
   * Получаем список айдишников стран для валюты.
   * Сюда входят страны у которых непосредственно указана нужная валюта
   * А также страны, у которых эта валюта была когда-то задана (смотрем по таблице country_currency_log)
   * Пример где это понадобилось: в фильтрах статы надо было показать BY и KZ  обеих валютах: в руб и евро
   * @param $currency
   * @return int[]
   */
  private static function getCountryIdsFilteredByCurrency($currency)
  {
    if (!$currency) {
      return [];
    }
    $currentCurrencyCountryIds = Country::find()
      ->select('id')
      ->andWhere(['currency' => $currency])
      ->column();

    $oldCurrencyCountryId = (new Query())
      ->select('country_id', 'DISTINCT')
      ->from('country_currency_log')
      ->andWhere(['currency' => $currency])
      ->column();

    return ArrayHelper::merge($currentCurrencyCountryIds, $oldCurrencyCountryId);
  }

  private static function getList($columnName, $userId = null, $currency = null)
  {
    return self::createListQuery($columnName, $userId, $currency)->createCommand(Yii::$app->clickhouse)->queryColumn();
  }

  public static function getCountriesIdList()
  {
    return static::getList('country_id');
  }

  public static function getProvidersIdList()
  {
    return static::getList('provider_id');
  }

  public static function getUsersIdList()
  {
    return static::getList('user_id');
  }

  public static function getLandingPayTypesIdList()
  {
    return static::getList('landing_pay_type_id');
  }

  public static function getLandingsId()
  {
    return static::getList('landing_id');
  }

  public static function getFilteredLandingIds($userIds = [])
  {
    $query = static::createListQuery('landing_id', $userIds);

//    if ($userIds) {
//      // фильтруем
//      $query->innerJoin(['f' => self::TABLE], ['t.landing_id' => new Expression('f.landing_id'), 'f.user_id' => $userIds]);
//    }
//
    return $query->column(Yii::$app->clickhouse);
  }

  /**
   * @param string $currency
   * @return array
   */
  public static function getOperatorIdList($currency = null)
  {
    return static::getList('operator_id', null, $currency);
  }

  public static function getPlatformIdList()
  {
    return static::getList('platform_id');
  }

  /**
   * @param ActiveQuery $query
   */
  public static function filterSources(ActiveQuery &$query, $userId = null)
  {
    static::filterQuery($query, 'source_id', 'sources.id', $userId);
  }

  /**
   * @param ActiveQuery $query
   */
  public static function filterStreams(ActiveQuery &$query, $userId = null)
  {
    static::filterQuery($query, 'stream_id', 'streams.id', $userId);
  }

  /**
   * @param ActiveQuery $query
   */
  public static function filterCountries(ActiveQuery &$query, $userId = null)
  {
    static::filterQuery($query, 'country_id', 'countries.id', $userId);
  }

  /**
   * @param ActiveQuery $query
   */
  public static function filterLandings(ActiveQuery &$query, $userId = null)
  {
    static::filterQuery($query, 'landing_id', 'landings.id', $userId);
  }

  /**
   * @param ActiveQuery $query
   */
  public static function filterOperators(ActiveQuery &$query, $userId = null)
  {
    static::filterQuery($query, 'operator_id', 'operators.id', $userId);
  }

  /**
   * @param ActiveQuery $query
   */
  public static function filterProviders(ActiveQuery &$query, $userId = null)
  {
    static::filterQuery($query, 'provider_id', 'providers.id', $userId);
  }

  /**
   * @param ActiveQuery $query
   */
  public static function filterPayType(ActiveQuery &$query, $userId = null)
  {
    static::filterQuery($query, 'landing_pay_type_id', 'landing_pay_types.id', $userId);
  }

  /**
   * @param ActiveQuery $query
   */
  public static function filterLandingOperator(ActiveQuery &$query, $userId = null)
  {
    $filterQuery = (new ClickhouseQuery())
      ->distinct()
      ->select(['landing_id', 'operator_id'])
      ->where(['and', ['>', 'landing_id', 0], ['>', 'operator_id', 0]])
      ->from(self::TABLE);

    if ($userId) {
      $filterQuery->andWhere(['user_id' => $userId]);
    }
  
    $filter = array_map(function ($val) {
      return [
        'landing_operators.landing_id' => $val['landing_id'],
        'landing_operators.operator_id' => $val['operator_id']
      ];
    }, $filterQuery->all(Yii::$app->clickhouse));
  
    $query->andWhere(['in', ['landing_operators.landing_id', 'landing_operators.operator_id'], $filter]);
  }

  /**
   * @param ActiveQuery $query
   */
  public static function filterPlatforms(ActiveQuery &$query, $userId = null)
  {
    static::filterQuery($query, 'platform_id', 'platforms.id', $userId);
  }

  /**
   * @param ActiveQuery $query
   */
  public static function filterUsers(ActiveQuery &$query)
  {
    static::filterQuery($query, 'user_id', 'users.id');

    // доп фильтрация, если пользователь не может управлять всеми пользователями (оптимизация)
    if (!Yii::$app->user->can(UserModule::PERMISSION_CAN_MANAGE_ALL_USERS)) {
      /** @var User $identity */
      $identity = Yii::$app->user->identity;
      $filterAvailable = $identity->getAvailableUsers()->select('id');
      $query->andWhere(['in', 'users.id', $filterAvailable]);
    }
  }

  /**
   * @param ActiveQuery $query
   * @param $filterField
   * @param $idField
   */
  private static function filterQuery(ActiveQuery &$query, $filterField, $idField, $userId = null)
  {
    $filterQuery = (new ClickhouseQuery())
      ->distinct()
      ->select($filterField)
      ->where(sprintf('%s > 0', $filterField))
      ->from(self::TABLE);

    if ($userId) {
      $filterQuery->andWhere(['user_id' => $userId]);
    }

    $query->andWhere(['in', $idField, $filterQuery->column(Yii::$app->clickhouse)]);
  }
}