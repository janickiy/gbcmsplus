<?php

namespace mcms\statistic\components;

use mcms\promo\models\Source;
use mcms\promo\models\Stream;
use mcms\statistic\components\exception\UnknownCurrencyException;
use mcms\statistic\models\mysql\StatFilter;
use mcms\statistic\Module;
use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use yii\data\BaseDataProvider;
use yii\db\Query;
use mcms\common\helpers\ArrayHelper;
use DateTime;

/**
 * Class AbstractStatistic
 * @package mcms\statistic\components
 * @property array $group
 */
abstract class  AbstractStatistic extends Model
{

  public $requestData;

  public $start_date;
  public $end_date;
  /* @var bool за месяц ли статистика */
  public $isMonthly;

  protected $shouldOpenFilters = false;

  private $userCurrency;
  public $currency;
  protected $allCurrencies;

  private $_group;
  /** @var bool Чекбокс в фильтре статистики 'По уникам' */
  public $isRatioByUniques;
  /** @var  \mcms\promo\Module */
  protected $promoModule;
  /** @var  \mcms\statistic\Module */
  protected $statModule;
  protected $_landingsByCountry;
  protected $_operatorsByCountry;
  protected $_countries;
  protected $_webmasterSources;
  protected $_sources;
  protected $_streams;
  protected $_arbitraryLinks;
  protected $_arbitraryLinksByStreams = [];
  protected $_operators;
  protected $_platforms;
  protected $_landings;

  /** @var string Выбранный вариант профита (Все | Revshare | CPA) */
  public $revshareOrCPA;

  /**
   * @var bool группировка по валюте
   */
  public $groupByCurrency = false;

  const ALL = 'all';
  const REVSHARE = 'revshare';
  const CPA = 'cpa';
  const SOLD = 'sold';
  const REJECTED = 'rejected';

  /**
   * @var  int|null
   * Айди юзера, от которого извлекается стата. например можно вытащить какую стату видит реселлер.
   * По-умолчанию если не задано, равно текущему юзеру.
   * TODO: вообще не факт что такой хак сработает на всех полях. надо тестить.
   */
  public $viewerId;

  private static $_checkAccess = [];

  public function formName()
  {
    return 'statistic';
  }

  public function init()
  {
    parent::init();

    $this->promoModule = Yii::$app->getModule('promo');
    $this->statModule = Yii::$app->getModule('statistic');

    $this->allCurrencies = $this->promoModule
      ->api('mainCurrencies')
      ->setResultTypeMap()
      ->setMapParams(['code', 'id'])
      ->getResult()
    ;

    $this->start_date = Yii::$app->formatter->asDate(strtotime('-6 days'), 'php:Y-m-d');
    $this->end_date = Yii::$app->formatter->asDate(time(), 'php:Y-m-d');

    $this->load($this->requestData, $this->formName());

    $this->userCurrency = Yii::$app
      ->getModule('payments')
      ->api('getUserCurrency', [
        'userId' => $this->getViewerId(),
      ])
      ->getResult()
    ;

    $this->currency = $this->currency ? : $this->promoModule->api('mainCurrenciesWidget')->getSelectedCurrency();
  }

  public function fields()
  {
    $fields = $this->attributes();
    $fields = array_combine($fields, $fields);
    $fields['group'] = 'group';

    return $fields;
  }

  public function rules()
  {
    return [
      [['start_date', 'end_date'], 'required'],
      [['start_date', 'end_date'], 'date'],
      [['isMonthly'], 'boolean'],
      ['start_date', 'validateDate'],
      [['group', 'currency', 'viewerId', 'groupByCurrency'], 'safe']
    ];
  }

  public function setGroup($field)
  {
    $this->_group = $field;
  }

  public function getGroup()
  {
    return $this->_group;
  }

  /**
   * Валидация периода выборки
   * @param $attribute
   */
  public function validateDate($attribute)
  {
    if ($this->start_date > $this->end_date) {
      $this->addError($attribute, Yii::_t('statistic.incorrect_period'));
    }
  }

  public function mergeStatisticArrays()
  {
    $result = [];
    $arrays = func_get_args();
    $keys = [];

    if ($this->groupByCurrency) {
      $currencies = [];
      foreach ($arrays as $array) {
        $currencies = array_merge($currencies, array_keys($array));
      }

      $currencies = array_unique($currencies);

      foreach ($arrays as $array) {
        foreach ($currencies as $currency) {
          $keys = array_merge($keys, isset($array[$currency]) ? array_keys($array[$currency]) : []);
        }
      }

      $keys = array_unique($keys);

      foreach ($currencies as $currency) {
        foreach ($keys as $key) {
          $result[$currency][$key] = call_user_func_array('array_merge', array_map(function($arrayItem) use ($currency, $key) {
            return isset($arrayItem[$currency][$key]) ? $arrayItem[$currency][$key] : []; // ArrayHelper вываливает Exception
          }, $arrays));
        }
      }

      return $result;
    } else {
      foreach ($arrays as $array) {
        $keys = array_merge($keys, array_keys($array));
      }

      $keys = array_unique($keys);

      foreach ($keys as $key) {
        $result[$key] = call_user_func_array('array_merge', array_map(function($arrayItem) use ($key) {
          return isset($arrayItem[$key]) ? $arrayItem[$key] : []; // ArrayHelper вываливает Exception
        }, $arrays));
      }

      return $result;
    }
  }

  public function getUserCurrency()
  {
    return $this->userCurrency;
  }

  public function canViewColumnByCurrency($currency)
  {
    if (!in_array($currency, array_keys($this->allCurrencies))) {
      throw (new UnknownCurrencyException)->setCurrency($currency);
    }

    if ($this->canFilterByCurrency()) {
      return $currency == $this->currency;
    }
    return $this->getUserCurrency() == $currency;
  }

  /**
   * проверяем право и кладем в $_checkAccess для кэширования
   * @param $permission
   * @return bool
   */
  protected function checkPermission($permission)
  {
    $userAccess = isset(self::$_checkAccess[$this->getViewerId()])
      ? self::$_checkAccess[$this->getViewerId()]
      : []
    ;

    if (isset($userAccess[$permission])) { // есть в кэше
      return $userAccess[$permission];
    }

    // нет в кэше, достаем
    $hasPermission = Yii::$app->authManager->checkAccess($this->getViewerId(), $permission);

    self::$_checkAccess[$this->getViewerId()][$permission] = $hasPermission;
    return $hasPermission;
  }


  public function shouldOpenFilters()
  {
    return $this->shouldOpenFilters;
  }

  /**
   * Выбираем значение аттрибута колонки в гриде
   * @param $label
   * @return mixed
   */
  public function getGridColumnLabel($label)
  {
    return ArrayHelper::getValue($this->gridColumnLabels(), $label);
  }

  public function handleOpenCloseFilters()
  {

    $vars = get_object_vars($this);
    /** Такой способ исключает возможность использования геттеров в проверке на свойства объекта */

    foreach ($this->getFilterFields() as $filterField) {
      if (!ArrayHelper::getValue($vars, $filterField)) continue;

      $this->shouldOpenFilters = true;
      break;
    }
  }

  /**
   * Возвращает масстив с колонками фильтра
   * @return array
   */
  abstract function getFilterFields();

  /**
   * Перевод для колонок грида
   * @return array
   */
  abstract function gridColumnLabels();

  /**
   * Добавляет фильтрацию к запросу
   * @param Query $query
   * @return void
   */
  abstract function handleFilters(Query &$query);

  /**
   * Получение стран по которым есть статистика
   * @return ArrayDataProvider
   */
  private function getStatisticCountryIds()
  {
    return (new Query())
      ->select('country_id')
      ->from(['st' => 'hits_day_group'])
      ->andWhere('st.user_id = :userId')
      ->addParams([':userId' => $this->getViewerId()])
      ->groupBy('country_id')
      ->column();
  }

  /**
   * @return BaseDataProvider
   */
  public function getStatisticGroup() {}

  function canViewSoldPrice()
  {
    return $this->checkPermission('StatisticViewSoldPrice');
  }

  function canViewAdminProfit()
  {
    return $this->checkPermission('StatisticViewAdminProfit');
  }

  function canViewResellerProfit()
  {
    return $this->checkPermission('StatisticViewResellerProfit');
  }

  function canViewPartnerProfit()
  {
    return $this->checkPermission('StatisticViewPartnerProfit');
  }

  function canViewAdditionalStatistic()
  {
    return $this->checkPermission('StatisticViewAdditionalStatistic');
  }

  function canViewVisibleSubscriptions()
  {
    return $this->checkPermission('StatisticViewVisibleSubscriptions');
  }

  public function getAdminProfit(array $gridRow, $currency) {}
  public function getResellerProfit(array $gridRow, $currency) {}
  public function getPartnerProfit(array $gridRow, $currency) {}

  public function findOne($recordId) {}

  public function formatDateDB($date)
  {
    return date('Y-m-d', strtotime($date));
  }

  /**
   * @param array $select
   * @return Query
   */
  public function getQuery(array $select = []) {}

  public function getPartnerProfitLabel()
  {
    return Yii::_t('statistic.statistic.sum_profit_' . $this->getUserCurrency());
  }

  public function getResellerProfitLabel()
  {
    return Yii::_t('statistic.statistic.sum_reseller_profit_rub');
  }
  public function getAdminProfitLabel()
  {
    return Yii::_t('statistic.statistic.sum_real_profit_rub');
  }

  public function canViewPhone()
  {
    return $this->checkPermission('StatisticViewPhone');
  }

  public function canViewFullPhone()
  {
    return $this->checkPermission('StatisticViewFullPhone');
  }

  public function canViewIp()
  {
    return $this->checkPermission('StatisticViewIp');
  }

  public function canViewUser()
  {
    return $this->checkPermission('StatisticViewUser');
  }

  public function canViewStream()
  {
    return $this->checkPermission('StatisticViewStream');
  }

  public function canViewSource()
  {
    return $this->checkPermission('StatisticViewSource');
  }

  public function canViewCountry()
  {
    return $this->checkPermission('StatisticViewCountry');
  }

  public function canViewOperator()
  {
    return $this->checkPermission('StatisticViewOperator');
  }

  public function canViewPlatform()
  {
    return $this->checkPermission('StatisticViewPlatform');
  }

  public function canViewLanding()
  {
    return $this->checkPermission('StatisticViewLanding');
  }

  public function canViewProfit()
  {
    return $this->checkPermission('StatisticViewProfitSum');
  }

  public function canViewReferrer()
  {
    return $this->checkPermission('StatisticViewReferrer');
  }

  public function canViewUserAgent()
  {
    return $this->checkPermission('StatisticViewUserAgent');
  }

  public function canViewSubid()
  {
    return $this->checkPermission('StatisticViewSubid');
  }

  public function canViewCid()
  {
    return $this->checkPermission('StatisticViewCid');
  }

  public function canGroupByLandings()
  {
    return $this->checkPermission('StatisticGroupByLandings');
  }

  public function canGroupBySources()
  {
    return $this->checkPermission('StatisticGroupBySources');
  }

  public function canGroupByStreams()
  {
    return $this->checkPermission('StatisticGroupByStreams');
  }

  public function canGroupByPlatforms()
  {
    return $this->checkPermission('StatisticGroupByPlatforms');
  }

  public function canGroupByOperators()
  {
    return $this->checkPermission('StatisticGroupByOperators');
  }

  public function canGroupByCountries()
  {
    return $this->checkPermission('StatisticGroupByCountries');
  }

  protected function canGroupByProviders()
  {
    return $this->checkPermission('StatisticGroupByProviders');
  }

  protected function canGroupByLandingPayTypes()
  {
    return $this->checkPermission('StatisticGroupByLandingPayTypes');
  }

  protected function canGroupByUsers()
  {
    return $this->checkPermission('StatisticGroupByUsers');
  }

  /**
   * Есть ли право группировать по менеджерам
   * @return bool
   */
  protected function canGroupByManagers()
  {
    return $this->checkPermission('StatisticGroupByManagers');
  }

  protected function canFilterByLandings()
  {
    return $this->checkPermission('StatisticFilterByLandings');
  }

  /**
   * Просмотр фильтров по категориям лендов
   * @return bool
   */
  protected function canFilterByLandingCategories()
  {
    return $this->checkPermission('StatisticFilterByLandingCategories');
  }

  protected function canFilterBySources()
  {
    return $this->checkPermission('StatisticFilterBySources');
  }

  protected function canFilterByStreams()
  {
    return $this->checkPermission('StatisticFilterByStreams');
  }

  protected function canFilterByPlatform()
  {
    return $this->checkPermission('StatisticFilterByPlatforms');
  }

  protected function canFilterByOperators()
  {
    return $this->checkPermission('StatisticFilterByOperators');
  }

  protected function canFilterByCountries()
  {
    return $this->checkPermission('StatisticFilterByCountries');
  }

  protected function canFilterByBanners()
  {
    return $this->checkPermission('StatisticFilterByBanners');
  }

  protected function canFilterByProviders()
  {
    return $this->checkPermission('StatisticFilterByProviders');
  }

  protected function canFilterByUsers()
  {
    return $this->checkPermission('StatisticFilterByUsers');
  }

  protected function canFilterByRoles()
  {
    return $this->checkPermission('StatisticFilterByRoles');
  }

  protected function canFilterByLandingPayTypes()
  {
    return $this->checkPermission('StatisticFilterByLandingPayTypes');
  }

  public function canFilterByCurrency()
  {
    return $this->checkPermission('StatisticFilterByCurrency');
  }

  /**
   * Может ли пользователь видеть невидимые для партнера солды
   * @return bool
   */
  public function canViewHiddenSoldSubscriptions()
  {
    return $this->checkPermission('StatisticViewHiddenSoldSubscriptions');
  }

  public function canViewHiddenOnetimeSubscriptions()
  {
    return $this->checkPermission('StatisticViewHiddenOnetimeSubscriptions');
  }


  public function canViewRealProfit()
  {
    return $this->checkPermission('StatisticViewRealProfit');
  }

  public function canViewDetailStatistic()
  {
    return $this->checkPermission('StatisticViewDetailStatistic');
  }

  public function canViewComplainsStatistic()
  {
    return $this->checkPermission('StatisticViewComplains');
  }

  public function canFilterByFakeRevshare()
  {
    return $this->checkPermission('StatisticFilterByFakeRevshare');
  }

  public function canViewTotalCountScopeOffs()
  {
    return $this->checkPermission('StatisticViewInvestorSubscriptionOffs');
  }

  /**
   * Список типов оплаты - модуль промо
   * @return mixed
   */
  public function getLandingPayTypes()
  {

    return $this->promoModule
      ->api('payTypes', [
        'conditions' => [
          'id' => [],
        ],
        'statFilters' => true,
      ])
      ->setResultTypeMap()
      ->setMapParams(['id', 'name'])
      ->getResult()
      ;
  }

  /**
   * Список провадеров - модуль промо
   * @return mixed
   */
  public function getProviders()
  {
    return $this->promoModule
      ->api('providers', [
        'conditions' => [
          'id' => [],
        ],
        'statFilters' => true,
      ])
      ->setResultTypeMap()
      ->setMapParams(['id', 'name'])
      ->getResult()
      ;
  }

  /**
   * Список операторов - модуль промо
   * @return mixed
   */
  public function getOperators()
  {
    if (isset($this->_operators)) {
      return $this->_operators;
    }

    $this->_operators = $this->promoModule
      ->api('operators', ['conditions' => ['onlyActiveCountries' => true]])
      ->setResultTypeMap()
      ->setMapParams(['id', 'name'])
      ->getResult();

    return $this->_operators;
  }

  /**
   * Список операторов по странам с трафиком - модуль промо
   * @return mixed
   */
  public function getOperatorsByCountry()
  {
    if (isset($this->_operatorsByCountry)) {
      return $this->_operatorsByCountry;
    }

    return $this->_operatorsByCountry = $this->promoModule
        ->api('operators', [
          'conditions' => [
            'country_id' => StatFilter::getCountriesIdList(),
          ],
          'statFilters' => true,
        ])
        ->setResultTypeMap()
        ->setMapParams(['id', 'name', 'country.name'])
        ->getResult()
    ;
  }

  /**
   * Список лендингов по странам
   * @return mixed
   */
  public function getLandingsByCountry()
  {
    if (isset($this->_landingsByCountry)) {
      return $this->_landingsByCountry;
    }
    return $this->_landingsByCountry = $this->promoModule
      ->api('landingOperators', [
        'conditions' => [
          'onlyActiveCountries' => true
        ],
        'statFilters' => true,
      ])
      ->setResultTypeMap()
      ->setMapParams([ 'landing.id', 'landing.name', 'operator.country.name'])
      ->getResult()
    ;
  }

  /**
   * Cписок стран - модуль промо
   * @param string|null $currency
   * @return mixed
   */
  public function getCountries($currency = null)
  {
    if (isset($this->_countries)) {
      return $this->_countries;
    }

    return $this->_countries = $this->promoModule
      ->api('countries', [
        'conditions' => [
          'id' => [],
        ],
        'sort' => ['defaultOrder' => ['name' => SORT_ASC]],
        'statFilters' => true,
        'currency' => $currency
      ])
      ->setResultTypeMap()
      ->setMapParams(['id', 'name'])
      ->getResult()
      ;
  }

  /**
   * Cписок Баннеров - модуль промо
   * @return mixed
   */
  public function getBanners()
  {
    return $this->promoModule
      ->api('banners')
      ->getMap()
      ;
  }

  /**
   * Cписок стран с валютой пользователя
   * @return mixed
   */
  public function getCountriesByCurrency()
  {
    $query = (new Query())
      ->select(['countries.id', 'countries.name'])
      ->from('countries')
      ->innerJoin('operators', 'operators.country_id = countries.id')
      ->innerJoin('landing_operators', 'landing_operators.operator_id = operators.id')
      ->innerJoin('currencies', 'landing_operators.default_currency_id = currencies.id')
      ->where(['in', 'countries.id', StatFilter::getCountriesIdList()])
      ->andWhere(['currencies.code' => $this->getUserCurrency()])
      ->all();

    return ArrayHelper::map($query, 'id', 'name');
  }

  /**
   * Cписок источников - модуль промо
   * @return mixed
   */
  public function getSources()
  {
    if (isset($this->_sources)) {
      return $this->_sources;
    }

    $this->_sources =  $this->promoModule
      ->api('sources')
      ->setResultTypeMap()
      ->setMapParams(['id', 'name'])
      ->getResult();

    return $this->_sources;
  }

  public function getWebmasterSources($pagination = ['pageSize' => 1000])
  {
    if (isset($this->_webmasterSources)) {
      return $this->_webmasterSources;
    }
    $module = $this->promoModule;

    $this->_webmasterSources =  $module
      ->api('sources', [
        'conditions' => ['source_type' => Source::SOURCE_TYPE_WEBMASTER_SITE],
        'pagination' => $pagination
      ])
      ->setResultTypeMap()
      ->setMapParams(['id', 'name'])
      ->getResult();

    return $this->_webmasterSources;
  }

  public function getArbitraryLinks()
  {
    if (isset($this->_arbitraryLinks)) {
      return $this->_arbitraryLinks;
    }
    $module = $this->promoModule;

    $this->_arbitraryLinks = $module
      ->api('sources', ['conditions' => ['source_type' => [Source::SOURCE_TYPE_LINK, Source::SOURCE_TYPE_SMART_LINK]]])
      ->setResultTypeMap()
      ->setMapParams(['id', 'name'])
      ->getResult();

    return $this->_arbitraryLinks;
  }

  /**
   *
   * @param array $pagination
   * @param string $group Группировка
   * @return mixed
   */
  public function getArbitraryLinksByStreams($pagination = ['pageSize' => 1000], $group = 'stream.name')
  {
    $key = md5(serialize($pagination) . serialize($group));
    $result = ArrayHelper::getValue($this->_arbitraryLinksByStreams, $key);

    if (isset($result)) {
      return $result;
    }
    $module = $this->promoModule;

    $this->_arbitraryLinksByStreams[$key] = $module
      ->api('sources', [
        'conditions' => ['source_type' => [Source::SOURCE_TYPE_LINK, Source::SOURCE_TYPE_SMART_LINK]],
        'pagination' => $pagination
      ])
      ->setResultTypeMap()
      ->setMapParams(['id', 'name', $group])
      ->getResult();

    return ArrayHelper::getValue($this->_arbitraryLinksByStreams, $key);
  }

  /**
   * Ссылки сгруппированные по источникам.
   * В отличии от getArbitraryLinksByStreams этот метод возвращает данные об источнике
   * @param array $pagination
   * @return array
   */
  public function getArbitraryLinksByStreamsData($pagination = ['pageSize' => 1000])
  {
    $linksByStream = $this->getArbitraryLinksByStreams($pagination, 'stream.id');
    $streams = Stream::find()->select(['id', 'name'])->andWhere(['id' => array_keys($linksByStream)])->asArray()->all();
    $streamsMapped = ArrayHelper::map($streams, 'id', 'name');

    $result = [];
    foreach ($linksByStream as $streamId => $links) {
      $result[] = [
        'id' => $streamId,
        'name' => $streamsMapped[$streamId],
        'links' => $linksByStream[$streamId],
      ];
    }

    return $result;
  }

  /**
   * Список потоков - модуль промо
   * @return mixed
   */
  public function getStreams()
  {
    if (isset($this->_streams)) {
      return $this->_streams;
    }
    $this->_streams = $this->promoModule
      ->api('streams')
      ->setResultTypeMap()
      ->setMapParams(['id', 'name'])
      ->getResult();

    return $this->_streams;
  }

  /**
   * Список лендингов - модуль промо
   * @return mixed
   */
  public function getLandings()
  {
    if (isset($this->_landings)) {
      return $this->_landings;
    }

    $this->_landings = $this->promoModule
      ->api('landings', ['conditions' => ['onlyActiveOperators' => true, 'onlyActiveCountries' => true]])
      ->setResultTypeMap()
      ->setMapParams(['id', 'name'])
      ->getResult();

    return $this->_landings;
  }

  /**
   * Список категорий лендингов - модуль промо
   * @return mixed
   */
  public function getLandingCategories()
  {
    $result = [];
    $cachedLandingCategories = $this->promoModule->api('cachedLandingCategories')->getResult();
    foreach ($cachedLandingCategories as $category) {
      $result[$category->id] = (string)$category->name;
    }

    return $result;
  }


  /**
   * Список платформ - модуль промо
   * @return mixed
   */
  public function getPlatforms()
  {
    if (isset($this->_platforms)) {
      return $this->_platforms;
    }
    $this->_platforms = $this->promoModule
      ->api('platforms', [
        'conditions' => [
          'id' => [],
        ],
        'statFilters' => true,
      ])
      ->setResultTypeMap()
      ->setMapParams(['id', 'name'])
      ->getResult();

    return $this->_platforms;
  }

  /**
   * Список пользователей
   * @return mixed
   */
  public function getUsers()
  {
    return Yii::$app->getModule('users')
      ->api('user')
      ->setResultTypeMap()
      ->setMapParams(['id', 'username'])
      ->getResult()
      ;
  }

  /**
   * @param array $row
   * @param null $type
   * @param bool $allowUnique разрешаем подсчет принятого по уникам
   * Используется в подсчете ратио в ПП, при включенной настройке в модуля
   * @return int
   */
  public function getAcceptedValue(array $row, $type = null, $allowUnique = false)
  {
    $type = $type == self::REVSHARE || $type == self::CPA ? $type . '_' : '';

    if ($this->isRatioUniquesEnabled() && $allowUnique) {
      $hits = (int)ArrayHelper::getValue($row, $type . 'count_uniques', 0);
      $tb = (int)ArrayHelper::getValue($row, $type . 'count_unique_tb', 0);
    } else {
      $hits = (int)ArrayHelper::getValue($row, $type . 'count_hits', 0);
      $tb = (int)ArrayHelper::getValue($row, $type . 'count_tb', 0);
    }

    return $hits - $tb;
  }

  /**
   * @return bool
   */
  public function isCPA()
  {
    return $this->revshareOrCPA == self::CPA;
  }

  /**
   * @return bool
   */
  public function isRevshare()
  {
    return $this->revshareOrCPA == self::REVSHARE;
  }

  /**
   * @param array $row
   * @param string $format
   * @param bool $useFormatter Использовать asPercent для форматирования процентов
   * @return string
   */
  public function getRevshareRatio(array $row, $format = '1:%d', $useFormatter = false)
  {
    $rightPart = (int) ArrayHelper::getValue($row, 'count_ons', 0);
    $accepted = $this->getAcceptedValue($row, self::REVSHARE, true);

    $rightRatioValue = $rightPart == 0 ? 0 : round($accepted / $rightPart, 1);

    $crValue = $accepted > 0 // исключаем возможность деления на ноль
      ? round(ArrayHelper::getValue($row, 'count_ons', 0) / $accepted * 100, 2)
      : 0
    ;

    if ($useFormatter) $crValue = Yii::$app->formatter->asPercent($crValue / 100, 2);

    return sprintf($format, $rightRatioValue, $crValue);
  }

  /**
   * @param array $row
   * @param string $format
   * @param bool $useFormatter Использовать asPercent для форматирования процентов
   * @return string
   */
  public function getCPARatio(array $row, $format = '1:%d', $useFormatter = false)
  {
    $rightPart = $this->getCPACount($row);
    $accepted = $this->getAcceptedValue($row, self::CPA, true);
    $rightRatioValue = $rightPart == 0 ? 0 : round($accepted / $rightPart, 1);

    $crValue = $accepted > 0  // исключаем возможность деления на ноль
      ? round($this->getCPACount($row) / $accepted * 100, 2)
      : 0
    ;

    if ($useFormatter) $crValue = Yii::$app->formatter->asPercent($crValue / 100, 2);

    return sprintf($format, $rightRatioValue, $crValue);
  }

  /**
   * @param array $row
   * @param $currency
   * @return float|null
   */
  public function getECPM(array $row, $currency)
  {
    $profit = $this->getCPASum($row, $currency);

    $accepted = $this->getAcceptedValue($row, self::CPA);

    if (!$accepted) return 0;

    return $profit / ($accepted / 1000);
  }

  public function getCPR(array $row, $currency)
  {
    $profit = ArrayHelper::getValue($row,
      ($this->statModule->settings->getValueByKey(Module::SETTINGS_CPR_CALC_THROUGTH_PRICE)
        ? 'sold_price_'
        : 'sold_profit_')
      . $currency, 0);
    $countSold = (int)ArrayHelper::getValue($row, 'count_sold', 0);

    if ($countSold == 0) {
      return 0;
    }

    return $profit / $countSold;
  }

  public function getVisibleSubscriptions(array $row)
  {
    return ArrayHelper::getValue($row, 'sold_visible_subscriptions', 0) + ArrayHelper::getValue($row, 'onetime_visible_subscriptions', 0);
  }

  public function getChargeRatio(array $row)
  {
    $ons = (int) ArrayHelper::getValue($row, 'count_ons', 0);

    if ($ons == 0) {
      return 0;
    }

    $chargesOnDate = (int) ArrayHelper::getValue($row, 'count_longs_date_by_date', 0);

    return $chargesOnDate / $ons;
  }

  public function getRevSub(array $row, $currency)
  {
    $ons = (int) ArrayHelper::getValue($row, 'count_ons', 0);

    if ($ons == 0) {
      return null;
    }

    $sumOnDate = ArrayHelper::getValue($row, 'sum_profit_' . $currency . '_date_by_date', 0);

    return $sumOnDate / $ons;
  }

  public function getRoiOnDate(array $row, $currency)
  {
    $investorBuyout = (float)ArrayHelper::getValue($row, 'buyout_investor_price_' . $currency, 0);
    $sumOnDate = (float)ArrayHelper::getValue($row, 'sum_profit_' . $currency . '_date_by_date', 0);

    if ($investorBuyout == 0) {
      return null;
    }

    return (($sumOnDate / $investorBuyout) - 1) * 100;
  }

  /**
   * @param array $row
   * @return int
   */
  public function getCPACount(array $row)
  {
    return (int) ArrayHelper::getValue($row, 'count_sold', 0) + (int) ArrayHelper::getValue($row, 'count_onetime', 0);
  }

  /**
   * @return string
   */
  public function getRevshareOrCPAPrefix()
  {
    return in_array($this->revshareOrCPA, [self::REVSHARE, self::CPA]) ? $this->revshareOrCPA . '_' : '';
  }

  public function isExportRequest()
  {
    return !empty(Yii::$app->request->post('export_type'));
  }

  /**
   * @return int|string
   */
  public function getViewerId()
  {
    return $this->viewerId ?: Yii::$app->user->id;
  }

  /**
   * @param $id
   */
  public function setViewerId($id)
  {
    $this->viewerId = $id;
  }

  /**
   * Включена ли настройка и передан ли параметр, разрешающий расчет именно по уникам.
   * @return bool
   */
  public function isRatioUniquesEnabled()
  {
    return $this->isRatioByUniques && $this->statModule->isRatioByUniquesEnabled();
  }

  /**
   * Определить даты начала и конца месяца при группировке в гриде статы
   * @param string $date
   * @param int $monthNumber Номер недели без ведущего нуля
   * @return DateTime[]
   */
  public function getMonthPeriod($date, $monthNumber)
  {
    /** Определение дат начала и конца месяца */
    // Определение начала
    $dateWeekBegin = new DateTime($date);
    $dateWeekBegin->modify('first day of');

    // Определение окончания
    $dateWeekEnd = new DateTime($date);
    $dateWeekEnd->modify('last day of');

    return $this->correctMonthOrWeekPeriod($dateWeekBegin, $dateWeekEnd, $monthNumber);
  }

  /**
   * Определить даты начала и конца недели при группировке в гриде статы
   * @param string $date
   * @param int $weekNumber Номер недели без ведущего нуля
   * @return DateTime[]
   */
  public function getWeekPeriod($date, $weekNumber)
  {
    /** Определение дат начала и конца недели */
    // Определение начала недели
    $dateWeekBegin = new DateTime($date);
    // Если день выданный БД не понедельник, то передвигаем на понедельник
    if ($dateWeekBegin->format('D') != 'Mon') $dateWeekBegin->modify('last monday');

    // Определение окончания недели
    $dateWeekEnd = new DateTime($date);
    // Если день выданный БД не воскресенье, то передвигаем на воскресенье
    if ($dateWeekEnd->format('D') != 'Sun') $dateWeekEnd->modify('sunday');

    return $this->correctMonthOrWeekPeriod($dateWeekBegin, $dateWeekEnd, $weekNumber);
  }

  /**
   * Корректировка начальной/конечной даты месяца/недели для отображения в гриде
   * @param DateTime $dateWeekBegin Начало недели/месяца
   * @param DateTime $dateWeekEnd Конец недели/месяца
   * @param int $weekOrMonthNumber Номер недели/месяца
   * @return array
   */
  protected function correctMonthOrWeekPeriod($dateWeekBegin, $dateWeekEnd, $weekOrMonthNumber)
  {
    /**
     * Корректировка месяца/недели распологающегося одновременно в двух годах так как это делает MySQL при группировке.
     * Месяц/неделя разбитый на два года считается разными периодами
     */
    // Если месяц/неделя первый в году, начало месяц/неделя в одном году, а конец в другом,
    // ограничиваем начало месяц/неделя началом текущего года
    $crossYears = $dateWeekBegin->format('Y') != $dateWeekEnd->format('Y');
    if ($weekOrMonthNumber == 1 && $crossYears) {
      $dateWeekBegin->modify('next year first day of january');
    }

    // Если месяц/неделя последняя в году, начало месяца/недели в одном году, а конец в другом,
    // ограничиваем конец месяца/недели концом текущего года
    // TRICKY Если убрать $crossYears, то все месяцы/недели кроме первого будут считаться последними
    if ($weekOrMonthNumber != 1 && $crossYears) {
      $dateWeekEnd->modify('previous year last day of december');
    }

    /** Корректировка в зависимости от дат указанных в фильтре */
    $filterDateBegin = new DateTime($this->formatDateDB($this->start_date));
    $filterDateEnd = new DateTime($this->formatDateDB($this->end_date));
    if ($dateWeekBegin < $filterDateBegin) $dateWeekBegin = $filterDateBegin;
    if ($dateWeekEnd > $filterDateEnd) $dateWeekEnd = $filterDateEnd;

    return [$dateWeekBegin, $dateWeekEnd];
  }

  /**
   * При нескольких группировках, в одном запросе может быть
   * добавлено несколько джоинов к одной и той же таблице запрос упадёт
   *
   * Данный метод служит проверкой и добавляет к локальному кэшу таблицу
   * к которой уже был джоин в текущем запросе
   *
   * Если такой джоин уже добавлен - вернет false
   *
   * Если передать таблицу с алиасом, будет проверен именно этот алиас
   * Если только таблицу - все вхождения этой таблицы
   *
   * @param string $tableAlias Таблица/таблица + алиас через пробел
   * @param Query $query
   * @return bool
   */
  public function addJoinGroupTable($tableAlias, $query)
  {
    $joinTables = [];
    if (is_array($query->join)) {
      $joinTablesWithOutAliases = array_map(function ($data) {
        $tableName = explode(' ', $data[1]);
        return strtok($data[1], ' ');
      }, $query->join);

      $joinTablesWithAliases = array_map(function ($data) {
        return $data[1];
      }, $query->join);

      $joinTables = array_unique(
        array_merge($joinTablesWithAliases, $joinTablesWithOutAliases)
      );
    }

    if (in_array($tableAlias, $joinTables)) {
      return false;
    }

    return true;
  }
}
