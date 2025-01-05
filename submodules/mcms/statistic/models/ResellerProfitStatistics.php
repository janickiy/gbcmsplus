<?php

namespace mcms\statistic\models;

use mcms\common\helpers\ArrayHelper;
use mcms\payments\models\UserBalanceInvoice;
use mcms\payments\models\UserPayment;
use mcms\promo\models\Source;
use mcms\statistic\components\AbstractStatistic;
use mcms\common\helpers\Html;
use Yii;
use mcms\common\module\api\join\Query as JoinQuery;
use yii\data\ArrayDataProvider;
use Yii\db\BatchQueryResult;
use yii\db\Expression;
use yii\db\Query;

/**
 * Модель для отображения статы реселлера по доходам
 * Свойства для фильтрации задаются в init методе родителя из GET параметров
 */
class ResellerProfitStatistics extends AbstractStatistic
{
  /**
   * @var array лендинги для фильтрации
   */
  public $landings;
  /**
   * @var array источники для фильтрации
   */
  public $sources;
  /**
   * @var array операторы для фильтрации
   */
  public $operators;
  /**
   * @var array платформы для фильтрации
   */
  public $platforms;
  /**
   * @var array потоки для фильтрации
   */
  public $streams;
  /**
   * @var array провайдеры для фильтрации
   */
  public $providers;
  /**
   * @var array страны для фильтрации
   */
  public $countries;
  /**
   * @var array партнеры для фильтрации
   */
  public $users;
  /**
   * @var array типы оплаты для фильтрации
   */
  public $landing_pay_types;
  /**
   * @var string начальная дата фильтрации
   */
  public $start_date;
  /**
   * @var string конечная дата фильтрации
   */
  public $end_date;
  /**
   * @var string валюта
   */
  public $currency;
  public $group = 'date';

  protected $groupIndexBy = [
    'landings' => 'landing_id',
    'arbitraryLinks' => 'source_id',
    'webmasterSources' => 'source_id',
    'streams' => 'stream_id',
    'platforms' => 'platform_id',
    'operators' => 'operator_id',
    'countries' => 'country_id',
    'providers' => 'provider_id',
    'users' => 'user_id',
    'landing_pay_types' => 'group',
    'hour' => 'group',
    'date' => 'group',
    'month_number' => 'group',
    'week_number' => 'group',
  ];

  protected $groupFormat = [
    'landings' => '#landing_id. group',
    'arbitraryLinks' => 'group',
    'webmasterSources' => 'group',
    'streams' => 'group',
    'platforms' => 'group',
    'operators' => 'group (country_name)',
    'countries' => 'group',
    'providers' => 'group',
    'users' => '#user_id. group',
    'landing_pay_types' => 'group',
    'hour' => 'group',
    'date' => 'group',
    'month_number' => 'group',
    'week_number' => 'group',
  ];


  protected $groupFieldsMap = [
    'landings' => 'landing_id',
    'arbitraryLinks' => 'source_id',
    'webmasterSources' => 'source_id',
    'streams' => 'stream_id',
    'platforms' => 'platform_id',
    'operators' => 'operator_id',
    'countries' => 'country_id',
    'providers' => 'provider_id',
    'users' => 'user_id',
    'landing_pay_types' => 'landing_pay_type_id',
    'hour' => 'group',
    'date' => 'group',
  ];

  /**
   * кэш статистики
   * @var
   */
  private $_statData;
  /** @var  array кэш для каждой ячейки в строке Итого, чтобы не ситать каждый раз заново */
  private $_fieldResults;

  const STATISTIC_NAME = 'RESELLER_PROFIT_STATISTIC';
  const GROUP_TYPE_GROUP = 'group';
  const GROUP_TYPE_INCOME = 'income';
  const GROUP_TYPE_CONSUMPTION = 'consumption';
  const GROUP_TYPE_CORRECTIONS = 'corrections';
  const GROUP_TYPE_TOTAL = 'total';

  /**
   * Возвращает название таблицы
   * @return string
   */
  public function tableName()
  {
    return 'reseller_profit_statistics';
  }

  /**
   * @return array
   */
  public function rules()
  {
    return array_merge(parent::rules(), [
      [['group', 'landings', 'sources', 'operators', 'platforms', 'streams', 'providers', 'countries', 'users', 'landing_pay_types'], 'safe'],
    ]);
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'group' => Yii::_t('statistic.statistic.group'),
      'start_date' => Yii::_t('statistic.statistic.start_date'),
      'end_date' => Yii::_t('statistic.statistic.end_date'),
      'streams' => Yii::_t('statistic.statistic.streams'),
      'sources' => Yii::_t('statistic.statistic.sources'),
      'landings' => Yii::_t('statistic.statistic.landings'),
      'countries' => Yii::_t('statistic.statistic.countries'),
      'operators' => Yii::_t('statistic.statistic.operators'),
      'providers' => Yii::_t('statistic.statistic.providers'),
      'platforms' => Yii::_t('statistic.statistic.platforms'),
      'users' => Yii::_t('statistic.statistic.users'),
      'landing_pay_types' => Yii::_t('statistic.statistic.landing_pay_types'),
      'date' => Yii::_t('statistic.statistic.date'),
    ];
  }

  /**
   * @inheritdoc
   */
  public function getStatisticGroup()
  {
    // todo сделать норма валидацию
    $this->start_date = Yii::$app->formatter->asDate($this->start_date, 'php:Y-m-d');
    $this->end_date = Yii::$app->formatter->asDate($this->end_date, 'php:Y-m-d');


    $data = $this->getProfitsData();
    if ($this->isShowCorrections()) {
      $data = $this->mergeStatisticArrays($data, $this->getInvoicesData());
    }

    $this->_statData = $data;
    $dataProvider = new ArrayDataProvider([
      'allModels' => $this->_statData,
      'sort' => false,
      'pagination' => false,
    ]);

    return $dataProvider;
  }

  /**
   * Получить данные по профитам
   * @return array
   */
  public function getProfitsData()
  {
    $query = (new Query())
      ->select([
        'revshareIncome' => 'SUM(rebills_reseller_profit)',
        'cpaIncome' => 'SUM(sold_profit)',
        'onetimeIncome' => 'SUM(onetime_reseller_profit)',

        'revshareConsumption' => 'SUM(rebills_profit)',
        'cpaConsumption' => 'SUM(sold_real_profit)',
        'onetimeConsumption' => 'SUM(onetime_profit)',
      ])
      ->from(['st' => $this->tableName()])
    ;
    $this->handleFilters($query);
    $this->filterByCurrency($query, 'st.currency_id');
    $this->addJoinByGroupField($query);

    return $this->indexBy($query->each());
  }

  /**
   * Получить данные по штрафам/компенсациям
   * @return array
   */
  public function getInvoicesData()
  {
    $query = (new Query())
      ->select([
        'resCompensations' => new Expression(
          'ABS(SUM(IF(type = :typeCompensation AND user_id = :resellerId, amount, 0)))'
        ),
        'partCompensations' => new Expression(
          'ABS(SUM(IF(type = :typeCompensation AND user_id <> :resellerId, amount, 0)))'
        ),
        'resPenalties' => new Expression(
          'ABS(SUM(IF(type = :typePenalty AND user_id = :resellerId, amount, 0)))'
        ),
        'partPenalties' => new Expression(
          'ABS(SUM(IF(type = :typePenalty AND user_id <> :resellerId, amount, 0)))'
        ),
      ])
      ->from(['st' => UserBalanceInvoice::tableName()])
      ->params([
        'resellerId' => UserPayment::getResellerId(),
        'typeCompensation' => UserBalanceInvoice::TYPE_COMPENSATION,
        'typePenalty' => UserBalanceInvoice::TYPE_PENALTY,
      ])
    ;

    $this->handleFilters($query);
    $this->filterByCurrency($query, 'st.currency', true);
    $this->addJoinByGroupField($query);

    return $this->indexBy($query->each());
  }

  /**
   * @inheritdoc
   */
  public function handleFilters(Query &$query)
  {
    /** @var $query Query */
    !$this->isGroupingByHour()
      ? $query
      ->andFilterWhere(['>=', 'st.date', $this->formatDateDB($this->start_date)])
      ->andFilterWhere(['<=', 'st.date', $this->formatDateDB($this->end_date)])
      : $query->andFilterWhere(['=', 'st.date', $this->formatDateDB($this->end_date)])
    ;

    if ($this->canFilterByLandings()) {
      $query->andFilterWhere(['st.landing_id' => $this->landings]);
    }

    if ($this->canFilterByOperators()) {
      $query->andFilterWhere(['st.operator_id' => $this->operators]);
    }

    if ($this->canFilterBySources()) {
      $query->andFilterWhere(['st.source_id' => $this->sources]);
    }

    if ($this->canFilterByPlatform()) {
      $query->andFilterWhere(['st.platform_id' => $this->platforms]);
    }

    if ($this->canFilterByStreams()) {
      $query->andFilterWhere(['st.stream_id' => $this->streams]);
    }

    if ($this->canFilterByCountries()) {
      $query->andFilterWhere(['st.country_id' => $this->countries]);
    }

    if ($this->canFilterByUsers()) {
      $query->andFilterWhere(['st.user_id' => $this->users]);
    } else {
      $query->andWhere(['st.user_id' => $this->getViewerId()]);
    }

    if ($this->canFilterByProviders()) {
      $query->andFilterWhere(['st.provider_id' => $this->providers]);
    }

    if ($this->canFilterByLandingPayTypes()) {
      $query->andFilterWhere(['st.landing_pay_type_id' => $this->landing_pay_types]);
    }

    // нужны только партнеры и реселлер (рес нужен для компенсаций/штрафов). Инвесторские компенсы/штрафы не нужны
    // но при группировке по партнерам, реса тоже надо прятать (его компенсы/штрафы).
    $rolesToHide = [];
    if ($this->group === 'users') {
      $rolesToHide[] = 'reseller';
    }

    $notAvailableUserIds = [];

    if ($rolesToHide) {
      $notAvailableUserIds = ArrayHelper::getColumn(
        Yii::$app->getModule('users')->api('usersByRoles', $rolesToHide)->getResult(),
        'id'
      );
    }

    if ($notAvailableUserIds) {
      $query->andWhere(['not in', 'st.user_id', $notAvailableUserIds]);
    }
  }

  /**
   * Добавляем джойны для сгруппированных данных
   * @param Query $query
   */
  protected function addJoinByGroupField(Query &$query)
  {
    /** @var Query $query */
    // TRICKY При изменении формата конката month_number и week_number, надо изменить парсинг в mcms/partners/themes/basic/statistic/index.php:63 и убедиться, что сортировка в партнерской статистике работает корректно
    switch ($this->group) {
      case 'month_number':
        $query->addSelect(['group' => 'CONCAT(YEAR(`date`), ".", LPAD(MONTH(`date`), 2, "0"))', 'date']);
        $query->addGroupBy('group');
        $query->orderBy(new Expression('NULL'));
        break;
      case 'week_number':
        $query->addSelect(['group' => 'CONCAT(YEAR(`date`), ".", LPAD(WEEK(`date`, 1) + 1, 2, "0"))', 'date']);
        $query->addGroupBy('group');
        $query->orderBy(new Expression('NULL'));
        break;
      case 'week_range':
        $query->addSelect([
          'group' => new Expression('FLOOR((DATEDIFF(st.`date`, :startDate) / 7)) + 1', [':startDate' => $this->start_date]),
          'date'
        ]);
        $query->addGroupBy('group');
        $query->orderBy(new Expression('NULL'));
        break;
      case 'date':
        $query->addSelect(['group' => 'st.date']);
        break;
      case 'hour':
        $query->addSelect(['group' => 'st.hour']);
        break;
      case 'landings':
        if (!$this->canGroupByLandings()) return ;
        /** @var \mcms\promo\components\api\LandingList $api */
        $api = $this->promoModule->api('landings');
        $api->join(
          new JoinQuery(
            $query,
            'st',
            ['LEFT JOIN', 'st.landing_id', '=', 'landing'],
            [
              'group' => 'landing.name',
              'landing_id' => 'landing.id'
            ]
          )
        );
        break;
      case 'webmasterSources':
      case 'arbitraryLinks':
        if (!$this->canGroupBySources()) return ;
        /** @var \mcms\promo\components\api\Source $api */
        $api = $this->promoModule->api('source');
        $api->join(
          new JoinQuery(
            $query,
            'st',
            [
              'INNER JOIN',
              'st.source_id',
              '=',
              'source'
            ],
            [
              'group' => 'source.name',
              'source_id' => 'source.id'
            ]
          )
        );
        break;
      case 'streams':
        if (!$this->canGroupByStreams()) return ;
        /** @var \mcms\promo\components\api\StreamList $api */
        $api = $this->promoModule->api('streams');
        $api->join(
          new JoinQuery(
            $query,
            'st',
            [
              'INNER JOIN',
              'st.stream_id',
              '=',
              'streams'
            ],
            [
              'group' => 'streams.name',
              'stream_id' => 'streams.id'
            ]
          )
        );
        break;
      case 'platforms':
        if (!$this->canGroupByPlatforms()) return ;
        /** @var \mcms\promo\components\api\PlatformList $api */
        $api = $this->promoModule->api('platforms');
        $api->join(
          new JoinQuery(
            $query,
            'st',
            ['LEFT JOIN', 'st.platform_id', '=', 'pl'],
            [
              'group' => 'pl.name',
              'platform_id' => 'pl.id'
            ]
          )
        );
        break;
      case 'operators':
        if (!$this->canGroupByOperators()) return ;
        /** @var \mcms\promo\components\api\OperatorList $api */
        $api = $this->promoModule->api('operators');
        $api->join(
          new JoinQuery(
            $query,
            'st',
            ['LEFT JOIN', 'st.operator_id', '=', 'op'],
            [
              'group' => 'op.name',
              'operator_id' => 'op.id'
            ]
          )
        );
        $api = $this->promoModule->api('countries');
        $api->join(
          new JoinQuery(
            $query,
            'st',
            ['LEFT JOIN', 'st.country_id', '=', 'co'],
            [
              'country_name' => 'co.name'
            ]
          )
        );
        break;
      case 'countries':
        if (!$this->canGroupByCountries()) return ;
        /** @var \mcms\promo\components\api\CountryList $api */
        $api = $this->promoModule->api('countries');
        $api->join(
          new JoinQuery(
            $query,
            'st',
            ['LEFT JOIN', 'st.country_id', '=', 'co'],
            [
              'group' => 'co.name',
              'country_id' => 'co.id'
            ]
          )
        );
        break;
      case 'providers':
        if (!$this->canGroupByProviders()) return ;
        /** @var \mcms\promo\components\api\ProviderList $api */
        $api = $this->promoModule->api('providers');
        $api->join(
          new JoinQuery(
            $query,
            'st',
            ['LEFT JOIN', 'st.provider_id', '=', 'prov'],
            [
              'group' => 'prov.name',
              'provider_id' => 'prov.id'
            ]
          )
        );
        break;
      case 'users':
        if (!$this->canGroupByUsers()) return ;
        /** @var \mcms\user\components\Api\User $api */
        $api = Yii::$app->getModule('users')->api('user');
        $api->join(
          new JoinQuery(
            $query,
            'st',
            ['LEFT JOIN', 'st.user_id', '=', 'user'],
            [
              'group' => 'user.username',
              'user_id' => 'user.id'
            ]
          )
        );
        break;
      case 'landing_pay_types':
        if (!$this->canGroupByLandingPayTypes()) return ;
        /** @var \mcms\promo\components\api\LandingPayTypeList $api */
        $api = $this->promoModule->api('payTypes');
        $api->join(
          new JoinQuery(
            $query,
            'st',
            ['LEFT JOIN', 'st.landing_pay_type_id', '=', 'pt'],
            ['group' => 'pt.name']
          )
        );
        break;
    }
    if ($this->group == 'webmasterSources') {
      $query->andWhere(['source.source_type' => Source::SOURCE_TYPE_WEBMASTER_SITE]);
    }
    if ($this->group == 'arbitraryLinks') {
      $query->andWhere(['source.source_type' => Source::SOURCE_TYPE_LINK]);
    }
    if ($groupField = $this->getMappedGroupField()) {
      $query->groupBy($groupField);
    }
  }

  /**
   * @param Query $query
   * @param $field
   * @param bool $iso Валюта фильтруется по строковым значениям (true) или по айди валюты (false)
   */
  protected function filterByCurrency(Query &$query, $field, $iso = false)
  {
    $query->andFilterWhere([
      $field => $iso ? $this->currency : $this->allCurrencies[$this->currency],
    ]);
  }

  /**
   * Подписи для группировок
   * @param null $group
   * @param null $filter
   * @return array|mixed
   */
  public function getGroups($group = null, $filter = null)
  {
    $res = [
      'date' => Yii::_t('statistic.statistic.dates'),
      'month_number' => Yii::_t('statistic.statistic.by_month_number'),
      'week_number' => Yii::_t('statistic.statistic.by_week_number'),
      'landings' => $this->canGroupByLandings() ? Yii::_t('statistic.statistic.landings') : false,
      'webmasterSources' => $this->canGroupBySources() ? Yii::_t('statistic.statistic.webmaster_sources') : false,
      'arbitraryLinks' => $this->canGroupBySources() ? Yii::_t('statistic.statistic.arbitrary_links') : false,
      'streams' => $this->canGroupByStreams() ? Yii::_t('statistic.statistic.streams') : false,
      'platforms' => $this->canGroupByPlatforms() ? Yii::_t('statistic.statistic.platforms') : false,
      'operators' => $this->canGroupByOperators() ? Yii::_t('statistic.statistic.operators') : false,
      'countries' => $this->canGroupByCountries() ? Yii::_t('statistic.statistic.countries') : false,
      'providers' => $this->canGroupByProviders() ? Yii::_t('statistic.statistic.providers') : false,
      'users' => $this->canGroupByUsers() ? Yii::_t('statistic.statistic.users') : false,
      'landing_pay_types' => $this->canGroupByLandingPayTypes() ? Yii::_t('statistic.statistic.landing_pay_types') : false,
    ];

    $res = array_filter($res);

    if (is_array($filter)) foreach ($res as $field => $label) {
      if (!in_array($field, $filter)) unset($res[$field]);
    }

    return !empty($group) ? ArrayHelper::getValue($res, $group) : $res;
  }

  /**
   * Возвращает массив полей для группировки
   * @param null $group
   * @param null $filter
   * @return array|string
   */
  public function getGroupFields($group = null, $filter = null)
  {
    $res = [
      'date' => 'date',
      'month_number' => 'month_number',
      'week_number' => 'week_number',
      'landings' => $this->canGroupByLandings() ? 'landing_id' : false,
      'webmasterSources' => $this->canGroupBySources() ? 'source_id' : false,
      'arbitraryLinks' => $this->canGroupBySources() ? 'source_id' : false,
      'streams' => $this->canGroupByStreams() ? 'stream_id' : false,
      'platforms' => $this->canGroupByPlatforms() ? 'platform_id' : false,
      'operators' => $this->canGroupByOperators() ? 'operator_id' : false,
      'countries' => $this->canGroupByCountries() ? 'country_id' : false,
      'providers' => $this->canGroupByProviders() ? 'provider_id' : false,
      'users' => $this->canGroupByUsers() ? 'user_id' : false,
      'landing_pay_types' => $this->canGroupByLandingPayTypes() ? 'landing_pay_type_id' : false,
    ];

    $res = array_filter($res);

    if (is_array($filter)) foreach ($res as $field => $label) {
      if (!in_array($field, $filter)) unset($res[$field]);
    }

    return !empty($group) ? ArrayHelper::getValue($res, $group) : $res;
  }

  /**
   * @inheritdoc
   */
  public function getFilterFields()
  {
    return [
      'landings',
      'sources',
      'operators',
      'platforms',
      'streams',
      'providers',
      'countries',
      'users',
      'landing_pay_types',
    ];
  }

  /**
   * @inheritdoc
   */
  public function gridColumnLabels()
  {
    return [
      'revshareIncome' => Yii::_t('statistic.reseller_income.revshare'),
      'cpaIncome' => Yii::_t('statistic.reseller_income.cpa'),
      'onetimeIncome' => Yii::_t('statistic.reseller_income.onetime'),
      'revshareConsumption' => Yii::_t('statistic.reseller_income.revshare'),
      'cpaConsumption' => Yii::_t('statistic.reseller_income.cpa'),
      'onetimeConsumption' => Yii::_t('statistic.reseller_income.onetime'),

      'resCompensations' => Yii::_t('statistic.reseller_income.res_compensations'),
      'partCompensations' => Yii::_t('statistic.reseller_income.part_compensations'),
      'resPenalties' => Yii::_t('statistic.reseller_income.res_penalties'),
      'partPenalties' => Yii::_t('statistic.reseller_income.part_penalties'),

      'totalIncome' => Yii::_t('statistic.reseller_income.income'),
      'totalConsumption' => Yii::_t('statistic.reseller_income.consumption'),
      'totalCorrections' => Yii::_t('statistic.reseller_income.corrections'),
      'totalProfit' => Yii::_t('statistic.reseller_income.profit'),
      'totalNetProfit' => Yii::_t('statistic.reseller_income.net_profit'),
    ];
  }

  /**
   * Группы для столбцов
   * @param null $group
   * @return array|mixed
   */
  public function getHeaderGroups($group = null)
  {
    $groups = [
      self::GROUP_TYPE_INCOME => Yii::_t('statistic.reseller_income.group_type_income'),
      self::GROUP_TYPE_CONSUMPTION => Yii::_t('statistic.reseller_income.group_type_consumption'),
      self::GROUP_TYPE_CORRECTIONS => Yii::_t('statistic.reseller_income.group_type_corrections'),
      self::GROUP_TYPE_TOTAL => Yii::_t('statistic.reseller_income.group_type_total')
    ];
    return $group ? ArrayHelper::getValue($groups, $group) : $groups;
  }

  /**
   * Группировка для шапки таблицы
   * @param array $columns
   * @return string
   */
  public function getBeforeHeader(array $columns)
  {
    $groups = [];
    foreach (array_keys($this->getHeaderGroups()) as $key) {
      $groups[$key] = 0;
    }

    $header = [];

    // Группируемые колонки
    foreach($columns as $column) {
      $groupType = ArrayHelper::getValue($column, 'groupType');
      if (!($groupType) ||
        !ArrayHelper::getValue($column, 'visible', true)
      ) continue;

      if ($groupType === self::GROUP_TYPE_GROUP) {
        $header[] = Html::tag('th', $column['label'], ['rowspan' => 2]);
      } else {
        $groups[$groupType]++;
      }
    }

    // Категории данных
    foreach (array_filter($groups) as $groupKey => $count) {
      $header[] = Html::tag('td', $this->getHeaderGroups($groupKey), ['colspan' => $count]);
    }

    return implode('', $header);
  }

  /**
   * Строка ИТОГО в футере
   * @param $field
   * @return mixed|string
   */
  public function getResultValue($field)
  {
    if (isset($this->_fieldResults[$field])) return $this->_fieldResults[$field];

    if (!$this->_statData) $this->getStatisticGroup();

    $sum = 0;
    foreach ($this->_statData as $row) {
      switch ($field) {
        case 'totalIncome':
        case 'totalConsumption':
        case 'totalCorrections':
        case 'totalProfit':
        case 'totalNetProfit':
          $sum += $this->getTotalValue($row, $field);
          break;
        default:
          $sum += floatval(ArrayHelper::getValue($row, $field, 0));
      }
    }

    return $this->_fieldResults[$field] = Yii::$app->formatter->asDecimal($sum);
  }

  /**
   * Столбцы ИТОГО
   * @param array $row
   * @param string $field
   * @return float|int
   */
  public function getTotalValue($row, $field)
  {
    switch ($field) {
      case 'totalIncome':
        return $this->safeField($row, 'revshareIncome')
          + $this->safeField($row, 'cpaIncome')
          + $this->safeField($row, 'onetimeIncome');
        break;
      case 'totalConsumption':
        return $this->safeField($row, 'revshareConsumption')
          + $this->safeField($row, 'cpaConsumption')
          + $this->safeField($row, 'onetimeConsumption');
        break;
      case 'totalCorrections':
        return $this->safeField($row, 'resCompensations')
          - $this->safeField($row, 'partCompensations')
          - $this->safeField($row, 'resPenalties')
          + $this->safeField($row, 'partPenalties');
        break;
      case 'totalProfit':
        return $this->getTotalValue($row, 'totalIncome') - $this->getTotalValue($row, 'totalConsumption');
        break;
      case 'totalNetProfit':
        return $this->getTotalValue($row, 'totalProfit') + $this->getTotalValue($row, 'totalCorrections');
        break;
    }
    return 0;
  }

  /**
   * Достаём поле в виде чистого float. Null превратится в float
   * @param array $row
   * @param string $field
   * @return float
   */
  protected function safeField($row, $field)
  {
    return (float) ArrayHelper::getValue($row, $field, 0);
  }

  /**
   * @param array|BatchQueryResult $statArray
   * (данный флаг нужно поставить в false, если запрос статистики не зависит от валюты)
   * @return array
   */
  protected function indexBy($statArray)
  {
    return ArrayHelper::index($statArray, function ($statRecord) {
      return strtr($this->groupIndexBy[$this->group], $statRecord);
    });
  }

  /**
   * @return bool
   */
  public function isGroupingByHour()
  {
    return $this->group == 'hour';
  }

  /**
   * @return bool
   */
  public function isGroupingByDate()
  {
    return $this->group == 'date';
  }

  /**
   * @return bool
   */
  public function isGroupingByMonth()
  {
    return $this->group == 'month_number';
  }

  /**
   * @return bool
   */
  public function isGroupingByWeek()
  {
    return $this->group == 'week_number';
  }

  /**
   * Показать столбцы с корректировками (штрафы/компенсы)
   * @return bool
   */
  public function isShowCorrections()
  {
    if (!in_array($this->group, ['date', 'month_number', 'week_number', 'users'])) {
      return false;
    }

    if (
      !empty($this->landings)
      || !empty($this->landing_pay_types)
      || !empty($this->operators)
      || !empty($this->countries)
      || !empty($this->sources)
      || !empty($this->providers)
      || !empty($this->streams)
      || !empty($this->platforms)
    ) {
      return false;
    }

    return true;
  }

  /**
   * Возвращает отформатирование значение в соответствии с группировкой
   * @param $row
   * @return string
   */
  public function formatGroup($row)
  {
    switch ($this->group) {
      case 'landings':
        $link = $this->promoModule->api('landingById', [
          'landingId' => ArrayHelper::getValue($row, 'landing_id')
        ])->getUrlParam();
        return Html::a(
          sprintf('#%s. %s', ArrayHelper::getValue($row, 'landing_id'), ArrayHelper::getValue($row, 'group')),
          $link,
          ['data-pjax' => 0, 'target' => '_blank'],
          [],
          false
        );
        break;
      case 'webmasterSources':
      case 'arbitraryLinks':
        $link = $this->promoModule->api('sourceById', [
          'source_id' => ArrayHelper::getValue($row, 'source_id')
        ])->getUrlParam();
        return Html::a(
          sprintf('#%s. %s', ArrayHelper::getValue($row, 'source_id'), ArrayHelper::getValue($row, 'group')),
          $link,
          ['data-pjax' => 0, 'target' => '_blank'],
          [],
          false
        );
        break;
      case 'streams':
        $link = $this->promoModule->api('stream', [
          'streamId' => ArrayHelper::getValue($row, 'stream_id')
        ])->getGridViewUrlParam();
        return Html::a(
          sprintf('#%s. %s', ArrayHelper::getValue($row, 'stream_id'), ArrayHelper::getValue($row, 'group')),
          $link,
          ['data-pjax' => 0, 'target' => '_blank'],
          [],
          false
        );
        break;
      case 'platforms':
        $link = $this->promoModule->api('platformId', [
          'platformId' => ArrayHelper::getValue($row, 'platform_id')
        ])->getGridViewUrlParam();
        return Html::a(
          sprintf('#%s. %s', ArrayHelper::getValue($row, 'platform_id'), ArrayHelper::getValue($row, 'group')),
          $link,
          ['data-pjax' => 0, 'target' => '_blank'],
          [],
          false
        );
        break;
      case 'users':
        $link = Yii::$app->getModule('users')->api('getOneUser', [
          'user_id' => ArrayHelper::getValue($row, 'user_id')
        ])->getUrlParam();
        return Html::a(
          sprintf('#%s. %s', ArrayHelper::getValue($row, 'user_id'), ArrayHelper::getValue($row, 'group')),
          $link,
          ['data-pjax' => 0, 'target' => '_blank'],
          [],
          false
        );
        break;
    }
    return strtr($this->groupFormat[$this->group], $row);
  }

  /**
   * @return string
   */
  private function getMappedGroupField()
  {
    return ArrayHelper::getValue($this->groupFieldsMap, $this->group);
  }
}