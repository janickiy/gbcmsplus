<?php
namespace mcms\statistic\models\mysql;

use mcms\common\helpers\Html;
use mcms\common\module\api\join\Query as JoinQuery;
use mcms\promo\models\Source;
use mcms\statistic\components\DatePeriod;
use mcms\statistic\models\Complain;
use mcms\statistic\models\Cr;
use mcms\user\models\User as UserModel;
use mcms\statistic\components\AbstractStatistic;
use mcms\statistic\components\StatisticQuery;
use mcms\statistic\Module;
use Yii;

use yii\base\Exception;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\data\ArrayDataProvider;
use yii\db\Query;

/**
 * TRICKY параметры фильтрации не указывать напрямую, т.к. будет проблема с присвоением дат в parent::init().
 * Вкраце: там делается `$this->load($this->requestData, $this->formName());`
 * Надо использовать вот так:
 * ```
 * new MainAdminStatistic(['requestData' => [
 *   'statistic' => ['currency' => 'rub', 'group' => 'hour', 'start_date' => '2018-01-17', 'end_date' => '2018-01-19']
 *  ]]);
 * ```
 * @property array $group
 */
class MainAdminStatistic extends AbstractStatistic
{
  const REVSHARE = 'revshare';
  const CPA = 'cpa';
  const ONETIME = 'onetime';
  const SELL_TB = 'sellTb';

  const GROUP_SEPARATOR = '_';
  const DATE_HOUR_SEPARATOR = self::GROUP_SEPARATOR;
  /** максимальный диапазон для показа статистики по часам */
  const MAX_DATE_HOUR_SPAN = 172800;

  const GROUP_BY_DATE_CO_OP_US = 'date_co_op_us';
  const GROUP_BY_CO_OP_US = 'co_op_us';

  const STATISTIC_NAME = 'main';

  private $_group = ['date'];

  public $groupFields;
  public $groupJoinTables = [];

  public $landings;
  public $landingCategories;
  public $sources;
  public $operators;
  public $platforms;
  public $streams;
  public $providers;
  public $countries;
  public $users;
  public $landing_pay_types;
  public $webmasterSources;
  public $arbitraryLinks;
  public $isFake;
  public $month_number;
  public $week_number;
  /** @var string Период отображения статистики
   * TRICKY Если указан этот параметр, параметры start_date и end_date автоматически перезаписываются на нужный период
   * @see DatePeriod */
  public $period;

  public $ignoreCurrencyFilter = false;
  public $excludeUserIds = [];
  /** @var Module $statisticModule */
  public $statisticModule;
  public $revshareOrCPA;

  /**
   * кэш статистики
   * @var
   */
  protected $_statData;

  /** @var  array кэш для каждой ячейки в строке Итого, чтобы не ситать каждый раз заново */
  private $_fieldResults;



  protected $groupFieldsMap = [
    'landings' => 'landing_id',
    'sources' => 'source_id',
    'arbitraryLinks' => 'source_id',
    'webmasterSources' => 'source_id',
    'streams' => 'stream_id',
    'platforms' => 'platform_id',
    'operators' => 'st.operator_id',
    'countries' => 'country_id',
    'providers' => 'provider_id',
    'users' => 'user_id',
    'from_users' => 'from_user_id',
    'landing_pay_types' => 'landing_pay_type_id',
    'hour' => 'group',
    'date_hour' => ['date', 'hour'],
    self::GROUP_BY_DATE_CO_OP_US => ['date', 'st.country_id', 'st.operator_id', 'user_id'],
    self::GROUP_BY_CO_OP_US => ['st.country_id', 'st.operator_id', 'user_id'],
    'date' => 'group',
    'managers' => 'manager_id',
  ];

  protected $groupIndexBy = [
    'landings' => 'landing_id',
    'sources' => 'source_id',
    'arbitraryLinks' => 'source_id',
    'webmasterSources' => 'source_id',
    'streams' => 'stream_id',
    'platforms' => 'platform_id',
    'operators' => 'operator_id',
    'countries' => 'country_id',
    'providers' => 'provider_id',
    'users' => 'user_id',
    'from_users' => 'user_id',
    'landing_pay_types' => 'group',
    'hour' => 'group',
    'date' => 'group',
    'month_number' => 'group',
    'week_number' => 'group',
    'date_hour' => 'group',
    self::GROUP_BY_DATE_CO_OP_US => 'group',
    self::GROUP_BY_CO_OP_US => 'group',
    'week_range' => 'group',
    'managers' => 'manager_id',
  ];

  protected $groupFormat = [
    'landings' => '#landing_id. group',
    'sources' => 'group',
    'arbitraryLinks' => 'group',
    'webmasterSources' => 'group',
    'streams' => 'group',
    'platforms' => 'group',
    'operators' => 'group (country_name)',
    'countries' => 'group',
    'providers' => 'group',
    'users' => '#user_id. group',
    'from_users' => '#user_id. group',
    'landing_pay_types' => 'group',
    'hour' => 'group',
    'date' => 'group',
    'month_number' => 'group',
    'week_number' => 'group',
    'date_hour' => 'group',
    self::GROUP_BY_DATE_CO_OP_US => 'group',
    self::GROUP_BY_CO_OP_US => 'group',
    'week_range' => 'group',
    'managers' => 'group',
  ];

  const HITS_STAT = 'hits';
  const SUBSCRIPTIONS_STAT = 'subs';
  const ONETIME_STAT = 'onetime';
  const SOLD_STAT = 'solds';
  const BUYOUT_STAT = 'buyouts';
  const PARTNER_SOLD_STAT = 'partner_solds';
  const COMPLAINS_STAT = 'complains';
  const SOLD_TD = 'sold_tb';
  const INVESTOR_SCOPE_OFFS = 'investor_scope_offs';
  const INVESTOR_STAT = 'investor_statistic';

  public function init()
  {
    // TODO: Выпилить эту модель, когда убедимся, что она нигде не используется
    throw new Exception('Deprecated statistic model!');

    parent::init();

    if (!empty($this->period)) {
      $periodDates = DatePeriod::getPeriodDates($this->period);
      $this->start_date = $periodDates['from'];
      $this->end_date = $periodDates['to'];
    }

    // С формы может прийти пустая строка, из-за чего происходит ошибка
    if (empty($this->streams)) $this->streams = null;

    !is_array($this->group) && $this->group = [$this->group];
    $this->group = array_unique(array_filter($this->group));

    $this->statisticModule = Yii::$app->getModule('statistic');
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return array_merge(parent::rules(), [
      [['group', 'landings', 'landingCategories', 'sources', 'operators', 'platforms', 'streams', 'providers', 'countries', 'users', 'landing_pay_types', 'webmasterSources', 'arbitraryLinks', 'isFake', 'ignoreCurrencyFilter', 'isRatioByUniques', 'excludeUserIds', 'period', 'revshareOrCPA'], 'safe'],
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
      'landingCategories' => Yii::_t('statistic.statistic.landingCategories'),
      'countries' => Yii::_t('statistic.statistic.countries'),
      'operators' => Yii::_t('statistic.statistic.operators'),
      'providers' => Yii::_t('statistic.statistic.providers'),
      'platforms' => Yii::_t('statistic.statistic.platforms'),
      'users' => Yii::_t('statistic.statistic.users'),
      'landing_pay_types' => Yii::_t('statistic.statistic.landing_pay_types'),
      'date' => Yii::_t('statistic.statistic.date'),
      'isFake' => Yii::_t('statistic.statistic.isFake'),
      'isRatioByUniques' => Yii::_t('statistic.statistic.isRatioByUniques'),
    ];
  }

  /**
   * @param string $field
   * @return mixed
   */
  public function getGroupByFieldByGroup($field = null)
  {
    $field === null && $field = $this->group[0];

    return ArrayHelper::getValue([
      'date' => Yii::_t('statistic.statistic.dates'),
      'month_number' => Yii::_t('statistic.statistic.month_numbers'),
      'week_number' => Yii::_t('statistic.statistic.week_numbers'),
      'hour' => Yii::_t('statistic.statistic.hours'),
      'landings' => Yii::_t('statistic.statistic.landings'),
      'sources' => Yii::_t('statistic.statistic.sources'),
      'webmasterSources' => Yii::_t('statistic.statistic.webmaster_sources'),
      'arbitraryLinks' => Yii::_t('statistic.statistic.arbitrary_links'),
      'streams' => Yii::_t('statistic.statistic.streams'),
      'platforms' => Yii::_t('statistic.statistic.platforms'),
      'operators' => Yii::_t('statistic.statistic.operators'),
      'countries' => Yii::_t('statistic.statistic.countries'),
      'providers' => Yii::_t('statistic.statistic.providers'),
      'users' => Yii::_t('statistic.statistic.users'),
      'from_users' => Yii::_t('statistic.statistic.users'),
      'landing_pay_types' => Yii::_t('statistic.statistic.landing_pay_types'),
      'managers' => Yii::_t('statistic.statistic.managers'),
    ], $field);
  }

  /**
   * Выбираем значение аттрибута колонки в гриде
   * @param $label
   * @return mixed
   */
  public function getGridColumnLabel($label)
  {
    return Yii::_t("statistic.main_statistic.$label");
  }


  /**
   * @inheritdoc
   */
  public function gridColumnLabels()
  {
    return [];
  }

  /**
   * Группировка
   * @param null $group
   * @return array|mixed
   */
  public function getGroups($group = null, $filter = null)
  {
    $res = [
      'date' => Yii::_t('statistic.statistic.dates'),
      'month_number' => Yii::_t('statistic.statistic.by_month_number'),
      'week_number' => Yii::_t('statistic.statistic.by_week_number'),
      'hour' => Yii::_t('statistic.statistic.hours'),
      'landings' => $this->canGroupByLandings() ? Yii::_t('statistic.statistic.landings') : false,
      'sources' => $this->canGroupBySources() ? Yii::_t('statistic.statistic.sources') : false,
      'webmasterSources' => $this->canGroupBySources() ? Yii::_t('statistic.statistic.webmaster_sources') : false,
      'arbitraryLinks' => $this->canGroupBySources() ? Yii::_t('statistic.statistic.arbitrary_links') : false,
      'streams' => $this->canGroupByStreams() ? Yii::_t('statistic.statistic.streams') : false,
      'platforms' => $this->canGroupByPlatforms() ? Yii::_t('statistic.statistic.platforms') : false,
      'operators' => $this->canGroupByOperators() ? Yii::_t('statistic.statistic.operators') : false,
      'countries' => $this->canGroupByCountries() ? Yii::_t('statistic.statistic.countries') : false,
      'providers' => $this->canGroupByProviders() ? Yii::_t('statistic.statistic.providers') : false,
      'users' => $this->canGroupByUsers() ? Yii::_t('statistic.statistic.users') : false,
      'landing_pay_types' => $this->canGroupByLandingPayTypes() ? Yii::_t('statistic.statistic.landing_pay_types') : false,
      'managers' => $this->canGroupByManagers() ? Yii::_t('statistic.statistic.managers') : false,
    ];

    $res = array_filter($res);

    if (is_array($filter)) foreach ($res as $field => $label) {
      if (!in_array($field, $filter)) unset($res[$field]);
    }

    return !empty($group) ? ArrayHelper::getValue($res, $group) : $res;
  }

  /**
   * TODO: зарефакторить с методом [[self::getGroups]], вроде копипаста голимая
   * Параметры группировки
   * @return array
   */
  public function getGroupsBy($filter = null)
  {
    $res = array_filter([
      'date' => Yii::_t('statistic.statistic.by_dates'),
      'month_number' => Yii::_t('statistic.statistic.by_month_number'),
      'week_number' => Yii::_t('statistic.statistic.by_week_number'),
      'hour' => Yii::_t('statistic.statistic.by_hours'),
      'date_hour' => Yii::_t('statistic.statistic.by_hours'),
      'landings' => $this->canGroupByLandings() ? Yii::_t('statistic.statistic.by_landings') : false,
      'sources' => $this->canGroupBySources() ? Yii::_t('statistic.statistic.by_sources') : false,
      'webmasterSources' => $this->canGroupBySources() ? Yii::_t('statistic.statistic.by_webmaster_sources') : false,
      'arbitraryLinks' => $this->canGroupBySources() ? Yii::_t('statistic.statistic.by_arbitrary_links') : false,
      'streams' => $this->canGroupByStreams() ? Yii::_t('statistic.statistic.by_streams') : false,
      'platforms' => $this->canGroupByPlatforms() ? Yii::_t('statistic.statistic.by_platforms') : false,
      'operators' => $this->canGroupByOperators() ? Yii::_t('statistic.statistic.by_operators') : false,
      'countries' => $this->canGroupByCountries() ? Yii::_t('statistic.statistic.by_countries') : false,
      'providers' => $this->canGroupByProviders() ? Yii::_t('statistic.statistic.by_providers') : false,
      'users' => $this->canGroupByUsers() ? Yii::_t('statistic.statistic.by_users') : false,
      'landing_pay_types' => $this->canGroupByLandingPayTypes() ? Yii::_t('statistic.statistic.by_landing_pay_types') : false,
      'managers' => $this->canGroupByManagers() ? Yii::_t('statistic.statistic.by_managers') : false,
    ]);

    if (is_array($filter)) foreach ($res as $field => $label) {
      if (!in_array($field, $filter)) unset($res[$field]);
    }

    return $res;
  }

  /**
   * Есть ли право у юзера, который просмотривает стату видеть столбцы по продажам ТБ
   * @return bool
   */
  public function canViewSellTb()
  {
    return $this->checkPermission('StatisticViewSoldTb');
  }

  /**
   * Получение сгруппированной статистики
   * @return ArrayDataProvider
   */

  public function getStatisticGroup()
  {
    $this->handleOpenCloseFilters();

    // статистика по переходам
    $hitsQuery = (new StatisticQuery())
      ->setId(self::HITS_STAT)
      ->select([
        'count_hits' => 'SUM(st.count_hits)',
        'count_tb' => 'SUM(st.count_tb)',
        'count_uniques' => 'SUM(st.count_uniques)',
        'count_unique_tb' => 'SUM(st.count_unique_tb)',

        self::CPA . '_count_hits' => 'SUM(IF(st.is_cpa = 1, st.count_hits, 0))',
        self::REVSHARE . '_count_hits' => 'SUM(IF(st.is_cpa = 0, st.count_hits, 0))',
        self::ONETIME . '_count_hits' => 'SUM(IF(lst.code=\'onetime\', st.count_hits, 0))',

        self::CPA . '_count_tb' => 'SUM(IF(st.is_cpa = 1, st.count_tb, 0))',
        self::REVSHARE . '_count_tb' => 'SUM(IF(st.is_cpa = 0, st.count_tb, 0))',
        self::ONETIME . '_count_tb' => 'SUM(IF(lst.code=\'onetime\', st.count_tb, 0))',

        self::CPA . '_count_uniques' => 'SUM(IF(st.is_cpa = 1, st.count_uniques, 0))',
        self::REVSHARE . '_count_uniques' => 'SUM(IF(st.is_cpa = 0, st.count_uniques, 0))',
        self::ONETIME . '_count_uniques' => 'SUM(IF(lst.code=\'onetime\', st.count_uniques, 0))',


        self::CPA . '_count_unique_tb' => 'SUM(IF(st.is_cpa = 1, st.count_unique_tb, 0))',
        self::REVSHARE . '_count_unique_tb' => 'SUM(IF(st.is_cpa = 0, st.count_unique_tb, 0))',
        self::ONETIME . '_count_unique_tb' => 'SUM(IF(lst.code=\'onetime\', st.count_unique_tb, 0))',
      ])
      ->leftJoin(
        'landing_operators',
        'landing_operators.landing_id = st.landing_id and landing_operators.operator_id = st.operator_id'
      )
      ->leftJoin(
        'landing_subscription_types lst',
        'landing_operators.subscription_type_id = lst.id'
      )
      ->from(['st' => 'hits_day_group'])
    ;

    if ($this->isFilterByCurrency()) {
      $currencyUsingRange = 'SELECT country_id, from_time, currency, IFNULL(to_time - 1, UNIX_TIMESTAMP()) AS to_time FROM
            (SELECT
               country_id,
               created_at AS from_time,
               currency,
               (SELECT created_at - 1
                FROM country_currency_log inn
                WHERE inn.country_id = ext.country_id AND inn.created_at > ext.created_at
                ORDER BY created_at ASC
                LIMIT 1)  AS to_time
             FROM
               country_currency_log ext) t';

      $dateCondition = '(log.currency IS NULL
        OR (
          st.date >= FROM_UNIXTIME(log.from_time, \'%Y-%m-%d\') 
          AND st.date <= FROM_UNIXTIME(log.to_time, \'%Y-%m-%d\')
        ))';

      $hitsQuery
        ->leftJoin('operators op_for_currency', 'op_for_currency.id = st.operator_id')
        ->leftJoin('(' . $currencyUsingRange . ') log', 'op_for_currency.country_id = log.country_id')
        ->andWhere([
          'or',
          ['st.landing_id' => 0],
          ['log.currency' => null, 'default_currency_id' => $this->allCurrencies[$this->currency]],
          ['log.currency' => $this->currency],
          ['IS', 'landing_operators.landing_id', null]
        ])
        ->andWhere($dateCondition)
      ;
    }

    // статистика по подпискам
    $subscriptionsQuery = (new StatisticQuery())
      ->setId(self::SUBSCRIPTIONS_STAT)
      ->select([
        'count_ons' => 'SUM(count_ons_revshare)',
        self::CPA . '_rejected_count_offs' => 'SUM(count_offs_rejected)',
        self::CPA . '_rejected_count_ons' => 'SUM(count_ons_rejected)',
        self::CPA . '_count_ons' => 'SUM(count_ons_cpa)',
        self::CPA . '_rejected_count_rebills' => 'SUM(count_rebills_rejected)',
        'count_rebills_sold' => 'SUM(count_rebills_sold)',
        'count_offs_sold' => 'SUM(count_offs_sold)',
        'count_offs' => 'SUM(count_offs_revshare)',
        'count_rebills' => 'SUM(count_rebills_revshare)',
        'sum_profit_rub' => 'SUM(partner_revshare_profit_rub)',
        'sum_profit_usd' => 'SUM(partner_revshare_profit_usd)',
        'sum_profit_eur' => 'SUM(partner_revshare_profit_eur)',
        'sold_cpa_profit_rub' => 'SUM(res_sold_profit_rub)',
        'sold_cpa_profit_usd' => 'SUM(res_sold_profit_usd)',
        'sold_cpa_profit_eur' => 'SUM(res_sold_profit_eur)',
        'rejected_cpa_profit_rub' => 'SUM(res_rejected_profit_rub)',
        'rejected_cpa_profit_usd' => 'SUM(res_rejected_profit_usd)',
        'rejected_cpa_profit_eur' => 'SUM(res_rejected_profit_eur)',
        'sum_reseller_profit_rub' => 'SUM(res_revshare_profit_rub)',
        'sum_reseller_profit_usd' => 'SUM(res_revshare_profit_usd)',
        'sum_reseller_profit_eur' => 'SUM(res_revshare_profit_eur)',

        'count_scope_offs' => 'SUM(count_offs24_revshare)',
        self::CPA . '_rejected_count_scope_offs' => 'SUM(count_offs24_rejected)',
        'sold_count_scope_offs' => 'SUM(count_offs24_sold)',

        'count_rebills_date_by_date' => 'SUM(count_rebills24_revshare)',
        self::CPA . '_rejected_count_rebills_date_by_date' => 'SUM(count_rebills24_rejected)',
        'sold_count_rebills_date_by_date' => 'SUM(count_rebills24_sold)',

        'sum_profit_rub_date_by_date' => 'SUM(partner_revshare_profit24_rub)',
        'sum_profit_usd_date_by_date' => 'SUM(partner_revshare_profit24_usd)',
        'sum_profit_eur_date_by_date' => 'SUM(partner_revshare_profit24_eur)',

        self::CPA . '_rejected_sum_profit_date_by_date_rub' => "SUM(partner_rejected_profit24_rub)",
        self::CPA . '_rejected_sum_profit_date_by_date_usd' => "SUM(partner_rejected_profit24_usd)",
        self::CPA . '_rejected_sum_profit_date_by_date_eur' => "SUM(partner_rejected_profit24_eur)",

        'sold_sum_profit_date_by_date_rub' => "SUM(partner_sold_profit24_rub)",
        'sold_sum_profit_date_by_date_usd' => "SUM(partner_sold_profit24_usd)",
        'sold_sum_profit_date_by_date_eur' => "SUM(partner_sold_profit24_eur)",
      ])
      ->from(['st' => 'statistic']);

    // для почасовой статистики используем другие таблицы
    if ($this->isHourTables()) {
      $hitsQuery->from(['st' => 'hits_day_hour_group']);
    }

    if ($this->revshareOrCPA === self::REVSHARE) {
      $hitsQuery->andWhere(['st.is_cpa' => 0]);
    }

    if ($this->revshareOrCPA === self::CPA) {
      $hitsQuery->andWhere(['st.is_cpa' => 1]);
    }

    // статистика по единоразовым подпискам
    $onetimeSubscriptionsQuery = (new StatisticQuery())
      ->setId(self::ONETIME_STAT)
      ->select([
        'count_onetime' => 'COUNT(st.hit_id)',
        'partner_visible_count_onetime' => 'COUNT(IF(st.is_visible_to_partner = 1, 1, NULL))',
        'onetime_profit_rub' => 'SUM(profit_rub)',
        'onetime_profit_usd' => 'SUM(profit_usd)',
        'onetime_profit_eur' => 'SUM(profit_eur)',
        'onetime_reseller_profit_rub' => 'SUM(reseller_profit_rub)',
        'onetime_reseller_profit_eur' => 'SUM(reseller_profit_eur)',
        'onetime_reseller_profit_usd' => 'SUM(reseller_profit_usd)',
        'onetime_real_profit_rub' => 'SUM(real_profit_rub)',
        'onetime_real_profit_eur' => 'SUM(real_profit_eur)',
        'onetime_real_profit_usd' => 'SUM(real_profit_usd)',
        'onetime_visible_subscriptions' => 'SUM(st.is_visible_to_partner)',
      ])
      ->from(['st' => 'onetime_subscriptions']);

    if (!$this->canViewHiddenOnetimeSubscriptions()) {
      $onetimeSubscriptionsQuery->where(['st.is_visible_to_partner' => 1]);
    }


    // статистика по продажам
    $soldSubscriptionsQuery = (new StatisticQuery())
      ->setId(self::SOLD_STAT)
      ->select([
        'count_sold' => 'COUNT(st.hit_id)',
        'partner_visible_count_sold' => 'COUNT(IF(st.is_visible_to_partner = 1, 1, NULL))',
        'sold_profit_rub' => 'SUM(profit_rub)',
        'sold_profit_usd' => 'SUM(profit_usd)',
        'sold_profit_eur' => 'SUM(profit_eur)',
        'sold_price_rub' => 'SUM(price_rub)',
        'sold_price_usd' => 'SUM(price_usd)',
        'sold_price_eur' => 'SUM(price_eur)',
        'sold_reseller_price_rub' => 'SUM(reseller_price_rub)',
        'sold_reseller_price_eur' => 'SUM(reseller_price_eur)',
        'sold_reseller_price_usd' => 'SUM(reseller_price_usd)',
        'sold_investor_price_rub' => 'SUM(real_price_rub)',
        'sold_investor_price_eur' => 'SUM(real_price_eur)',
        'sold_investor_price_usd' => 'SUM(real_price_usd)',
        'sold_visible_subscriptions' => 'SUM(st.is_visible_to_partner)',
      ])
      ->from(['st' => 'sold_subscriptions'])
    ;

    if (!$this->canViewHiddenSoldSubscriptions()) {
      $soldSubscriptionsQuery->where(['st.is_visible_to_partner' => 1]);
    }

    // статистика по покупкам
    $buyoutSubscriptionsQuery = (new StatisticQuery())
      ->setId(self::BUYOUT_STAT)
      ->select([
        'buyout_investor_price_rub' => 'SUM(price_rub)',
        'buyout_investor_price_eur' => 'SUM(price_eur)',
        'buyout_investor_price_usd' => 'SUM(price_usd)',
      ])
      ->from(['st' => 'sold_subscriptions'])
    ;

    // статистика по профиту CPA партнера
    // sold_partner_profit_ переименовать в visible_partner_sold_profit
    $partnerSoldSubscriptionsQuery = (new StatisticQuery())
      ->setId(self::PARTNER_SOLD_STAT)
      ->select([
        'sold_partner_profit_rub' => 'SUM(profit_rub)',
        'sold_partner_profit_eur' => 'SUM(profit_eur)',
        'sold_partner_profit_usd' => 'SUM(profit_usd)',
        'sold_partner_price_rub' => 'SUM(price_rub)',
        'sold_partner_price_eur' => 'SUM(price_eur)',
        'sold_partner_price_usd' => 'SUM(price_usd)',
      ])
      ->from(['st' => 'sold_subscriptions'])
      ->where(['st.is_visible_to_partner' => 1]);

    // статистика по жалобам
    $complainsSubscriptionsQuery = (new StatisticQuery())
      ->setId(self::COMPLAINS_STAT)
      ->select([
        'count_complains' => 'SUM(IF(st.type = :complainText, 1, 0))',
        'count_calls' => 'SUM(IF(st.type = :complainCall, 1, 0))',
        'count_auto24' => 'SUM(IF(st.type = :complainAuto24, 1, 0))',
        'count_auto_moment' => 'SUM(IF(st.type = :complainAutoMoment, 1, 0))',
        'count_auto_duplicate' => 'SUM(IF(st.type = :complainAutoDuplicate, 1, 0))',
        'count_calls_mno' => 'SUM(IF(st.type = :complainCallMno, 1, 0))',
      ])
      ->from(['st' => 'complains'])
      ->addParams([
        ':complainText' => Complain::TYPE_TEXT,
        ':complainCall' => Complain::TYPE_CALL,
        ':complainAuto24' => Complain::TYPE_AUTO_24,
        ':complainAutoMoment' => Complain::TYPE_AUTO_MOMENT,
        ':complainAutoDuplicate' => Complain::TYPE_AUTO_DUPLICATE,
        ':complainCallMno' => Complain::TYPE_CALL_MNO,
      ])
    ;

    if (in_array($this->revshareOrCPA, [self::REVSHARE, self::CPA])) {
      $complainsSubscriptionsQuery->leftJoin('hits', 'st.hit_id = hits.id');
    }

    if ($this->revshareOrCPA === self::REVSHARE) {
      $complainsSubscriptionsQuery->andWhere(['hits.is_cpa' => 0]);
    }

    if ($this->revshareOrCPA === self::CPA) {
      $complainsSubscriptionsQuery->andWhere(['hits.is_cpa' => 1]);
    }

    if ($this->isFilterByCurrency()) {
      $complainsSubscriptionsQuery->leftJoin(
        'landing_operators',
        'landing_operators.landing_id = st.landing_id and landing_operators.operator_id = st.operator_id'
      )->andWhere(['default_currency_id' => $this->allCurrencies[$this->currency]]);
    }

    // Проданный ТБ
    $sellTbHitsQuery = (new StatisticQuery())
      ->setId(self::SOLD_TD)
      ->from(['st' => 'sell_tb_hits_grouped'])
      ->select([
        'sell_tb_accepted' => 'SUM(st.count_hits)',
      ]);

    $soldTbQuery = (new StatisticQuery())
      ->setId(self::SOLD_TD)
      ->from(['st' => 'sold_trafficback'])
      ->select([
        'count_sold_tb' => 'COUNT(st.hit_id)',
        'sold_tb_reseller_profit_rub' => 'SUM(reseller_profit_rub)',
        'sold_tb_reseller_profit_eur' => 'SUM(reseller_profit_eur)',
        'sold_tb_reseller_profit_usd' => 'SUM(reseller_profit_usd)',
      ]);


    $this->handleFilters($hitsQuery);
    $this->addJoinByGroupField($hitsQuery);

    $this->handleFilters($subscriptionsQuery);
    $this->addJoinByGroupField($subscriptionsQuery);
    $this->filterByCurrency($subscriptionsQuery);

    $this->handleFilters($onetimeSubscriptionsQuery);
    $this->addJoinByGroupField($onetimeSubscriptionsQuery);
    $this->filterByCurrency($onetimeSubscriptionsQuery);

    $this->addJoinByGroupField($soldSubscriptionsQuery);
    $this->handleFilters($soldSubscriptionsQuery);
    $this->filterByCurrency($soldSubscriptionsQuery);

    $this->addJoinByGroupField($partnerSoldSubscriptionsQuery);
    $this->handleFilters($partnerSoldSubscriptionsQuery);
    $this->filterByCurrency($partnerSoldSubscriptionsQuery);

    $this->addJoinByGroupField($buyoutSubscriptionsQuery);
    $this->handleFilters($buyoutSubscriptionsQuery);
    $this->filterByCurrency($buyoutSubscriptionsQuery);

    $this->addJoinByGroupField($complainsSubscriptionsQuery);
    $this->handleFilters($complainsSubscriptionsQuery);

    $this->handleFilters($soldTbQuery);
    $this->addJoinByGroupField($soldTbQuery);
    $this->filterByCurrency($soldTbQuery);

    $this->handleFilters($sellTbHitsQuery);
    $this->addJoinByGroupField($sellTbHitsQuery);

    //для получения отписок по проданным подпискам группируем по from_user_id
    if ($this->isGroupingBy('users')) {
      $this->updateGroup('users', 'from_users');
    }
    if ($this->isGroupingBy('from_users')) {
      $this->updateGroup('from_users', 'users');
    }

    if ($this->isGroupingBy('users')) {
      $this->updateGroup('users', 'from_users');
    }
    if ($this->isGroupingBy('from_users')) {
      $this->updateGroup('from_users', 'users');
    }

    $this->filterByCurrency($soldTbQuery, true);

    $hitsData = $this->indexBy($hitsQuery->each(), false);
    $subscriptionsData = $this->revshareOrCPA === self::CPA ? [] : $this->indexBy($subscriptionsQuery->each());

    // tricky проверка на пермишен экшена, чтобы не создавать новый пермишен.
    // Сделано для того, чтоб не запрашивать в стате в ПП

    $onetimeSubscriptionsData = $this->indexBy($onetimeSubscriptionsQuery->each());
    $soldSubscriptionsData = $this->revshareOrCPA === self::REVSHARE ? [] : $this->indexBy($soldSubscriptionsQuery->each());
    $partnerSoldSubscriptionsData = $this->indexBy($partnerSoldSubscriptionsQuery->each());
    $buyoutSubscriptionsData = $this->indexBy($buyoutSubscriptionsQuery->each());
    $complainsSubscriptionsData = $this->canViewComplainsStatistic() ? $this->indexBy($complainsSubscriptionsQuery->each(), false) : [];
    $soldTbData = $this->indexBy($soldTbQuery->each());
    $sellTbHitsData = $this->indexBy($sellTbHitsQuery->each());

    if ($this->groupByCurrency) {
      $data = $this->mergeStatisticArrays(
        $subscriptionsData,
        $onetimeSubscriptionsData,
        $soldSubscriptionsData,
        $partnerSoldSubscriptionsData,
        $buyoutSubscriptionsData,
        $soldTbData,
        $sellTbHitsData
      );
    } else {
      $data = $this->mergeStatisticArrays(
        $hitsData,
        $subscriptionsData,
        $onetimeSubscriptionsData,
        $soldSubscriptionsData,
        $partnerSoldSubscriptionsData,
        $buyoutSubscriptionsData,
        $complainsSubscriptionsData,
        $soldTbData,
        $sellTbHitsData
      );
    }

    $this->_statData = $data;

    $dataProvider = new ArrayDataProvider([
      'allModels' => $data,
      'sort' => false,
      'pagination' => [
        'pageSize' => 1000,
      ],
    ]);

    return $dataProvider;

  }

  public function getGroupFieldName($field)
  {
    return count($this->group) > 1 ? 'group_' . $field : 'group';
  }

  protected function addJoinByGroupField(Query &$query)
  {
    /** @var StatisticQuery $query */
    /** @var \mcms\promo\Module $module */
    $module = Yii::$app->getModule('promo');

    // TRICKY При изменении формата конката month_number и week_number, надо изменить парсинг в mcms/partners/themes/basic/statistic/index.php:63 и убедиться, что сортировка в партнерской статистике работает корректно
    foreach ((array)$this->group as $field) {
      $this->groupFields[$field] = $this->getGroupFieldName($field);
      switch ($field) {
        case 'month_number':
          $query->addSelect([$this->groupFields[$field] => 'CONCAT(YEAR(st.`date`), ".", LPAD(MONTH(st.`date`), 2, "0"))', 'st.date']);
          $query->addGroupBy($this->groupFields[$field]);
          $query->orderBy(new Expression('NULL'));
          break;
        case 'week_number':
          $query->addSelect([$this->groupFields[$field] => 'CONCAT(YEAR(st.`date`), ".", LPAD(WEEK(st.`date`, 1) + 1, 2, "0"))', 'st.date']);
          $query->addGroupBy($this->groupFields[$field]);
          $query->orderBy(new Expression('NULL'));
          break;
        case 'week_range':
          $query->addSelect([
            $this->groupFields[$field] => new Expression(
              'FLOOR((DATEDIFF(st.`date`, :startDate) / 7)) + 1',
              [':startDate' => $this->start_date]
            ),
            'st.date'
          ]);
          $query->addGroupBy($this->groupFields[$field]);
          $query->orderBy(new Expression('NULL'));
          break;
        case 'date':
          $query->addSelect([$this->groupFields[$field] => 'st.date']);
          break;
        case 'hour':
          $query->addSelect([$this->groupFields[$field] => 'st.hour']);
          break;
        case 'date_hour':
          $query->addSelect([$this->groupFields[$field] => 'CONCAT(st.date, \'' . self::DATE_HOUR_SEPARATOR . '\', st.hour)']);
          break;
        case self::GROUP_BY_CO_OP_US:
        case self::GROUP_BY_DATE_CO_OP_US:

        $groupFieldMap = ['st.country_id', 'st.operator_id', 'st.user_id'];
        if ($field === self::GROUP_BY_DATE_CO_OP_US) {
          $query->addSelect(['st.date']);
          $groupFieldMap = array_merge(['date'], $groupFieldMap);
        }

        $query->addSelect([$this->groupFields[$field] => 'CONCAT(' .
            implode(',\'' . self::GROUP_SEPARATOR . '\',', $groupFieldMap) .
            ')']);

          if ($this->addJoinGroupTable('users user', $query)) {
            /** @var \mcms\user\components\Api\User $api */
            $api = Yii::$app->getModule('users')->api('user');
            $api->join(
              new JoinQuery($query, 'st', ['LEFT JOIN', 'st.user_id', '=', 'user'],
                ['user' => 'user.username', 'user_id' => 'user.id']
              )
            );
          }

          if ($this->addJoinGroupTable('operators op', $query)) {
            /** @var \mcms\promo\components\api\OperatorList $api */
            $api = $module->api('operators');
            $api->join(
              new JoinQuery($query, 'st', ['LEFT JOIN', 'st.operator_id', '=', 'op'],
                ['operator' => 'op.name']
              )
            );
          }

          if ($this->addJoinGroupTable('countries co', $query)) {
            $api = $module->api('countries');
            $api->join(new JoinQuery($query, 'st', ['LEFT JOIN', 'st.country_id', '=', 'co'],
                ['country_name' => 'co.name', 'country_code' => 'co.code']
              )
            );
          }
          break;
        case 'landings':
          if (!$this->canGroupByLandings()) return;
          if ($this->addJoinGroupTable('landings landing', $query)) {
            /** @var \mcms\promo\components\api\LandingList $api */
            $api = $module->api('landings');
            $api->join(
              new JoinQuery(
                $query,
                'st',
                ['LEFT JOIN', 'st.landing_id', '=', 'landing'],
                [
                  $this->groupFields[$field] => 'landing.name',
                  'landing_id' => 'landing.id'
                ]
              )
            );
          }
          break;
        case 'sources':
        case 'webmasterSources':
        case 'arbitraryLinks':
          if (!$this->canGroupBySources()) return;
          if ($this->addJoinGroupTable('sources source', $query)) {
            /** @var \mcms\promo\components\api\Source $api */
            $api = $module->api('source');
            $api->join(
              new JoinQuery(
                $query,
                'st',
                [
                  'INNER JOIN',
                  $this->isInvestorQuery($query)
                    ? 'st.from_source_id'
                    : 'st.source_id', // Инвестору джойним по его источникам
                  '=',
                  'source'
                ],
                [
                  $this->groupFields[$field] => 'source.name',
                  'source_id' => 'source.id'
                ]
              )
            );
          }
          break;
        case 'streams':
          if (!$this->canGroupByStreams()) return;
          if ($this->addJoinGroupTable('streams streams', $query)) {
            /** @var \mcms\promo\components\api\StreamList $api */
            $api = $module->api('streams');
            $api->join(
              new JoinQuery(
                $query,
                'st',
                [
                  'INNER JOIN',
                  $this->isInvestorQuery($query)
                    ? 'st.from_stream_id'
                    : 'st.stream_id', // Инвестору джойним по его источникам
                  '=',
                  'streams'
                ],
                [
                  $this->groupFields[$field] => 'streams.name',
                  'stream_id' => 'streams.id'
                ]
              )
            );
          }
          break;
        case 'platforms':
          if (!$this->canGroupByPlatforms()) return;
          if ($this->addJoinGroupTable('platforms pl', $query)) {
            /** @var \mcms\promo\components\api\PlatformList $api */
            $api = $module->api('platforms');
            $api->join(
              new JoinQuery(
                $query,
                'st',
                ['LEFT JOIN', 'st.platform_id', '=', 'pl'],
                [
                  $this->groupFields[$field] => 'pl.name',
                  'platform_id' => 'pl.id'
                ]
              )
            );
          }
          break;
        case 'operators':
          if (!$this->canGroupByOperators()) return;

          if ($this->addJoinGroupTable('operators op', $query)) {
            /** @var \mcms\promo\components\api\OperatorList $api */
            $api = $module->api('operators');
            $api->join(
              new JoinQuery(
                $query,
                'st',
                ['LEFT JOIN', 'st.operator_id', '=', 'op'],
                [
                  $this->groupFields[$field] => 'op.name',
                  'operator_id' => 'op.id'
                ]
              )
            );
          }

          if ($this->addJoinGroupTable('countries co', $query)) {
            $api = $module->api('countries');
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
          }

          break;
        case 'countries':
          if (!$this->canGroupByCountries()) return;
          if ($this->addJoinGroupTable('countries co', $query)) {
            /** @var \mcms\promo\components\api\CountryList $api */
            $api = $module->api('countries');
            $api->join(
              new JoinQuery(
                $query,
                'st',
                ['LEFT JOIN', 'st.country_id', '=', 'co'],
                [
                  $this->groupFields[$field] => 'co.name',
                  'country_name' => 'co.name',
                  'country_id' => 'co.id'
                ]
              )
            );
          } else {
            $query->addSelect([
              $this->groupFields[$field] => 'co.name',
              'country_id' => 'co.id',
            ]);
          }
          break;
        case 'providers':
          if (!$this->canGroupByProviders()) return;
          if ($this->addJoinGroupTable('providers prov', $query)) {
            /** @var \mcms\promo\components\api\ProviderList $api */
            $api = $module->api('providers');
            $api->join(
              new JoinQuery(
                $query,
                'st',
                ['LEFT JOIN', 'st.provider_id', '=', 'prov'],
                [
                  $this->groupFields[$field] => 'prov.name',
                  'provider_id' => 'prov.id'
                ]
              )
            );
          }
          break;
        case 'users':
          if (!$this->canGroupByUsers()) return;
          if ($this->addJoinGroupTable('users user', $query)) {
            /** @var \mcms\user\components\Api\User $api */
            $api = Yii::$app->getModule('users')->api('user');
            $api->join(
              new JoinQuery(
                $query,
                'st',
                ['LEFT JOIN', 'st.user_id', '=', 'user'],
                [
                  $this->groupFields[$field] => 'user.username',
                  'user_id' => 'user.id'
                ]
              )
            );
          }
          break;
        case 'managers':
          if (!$this->canGroupByManagers()) return;
          // tricky: Для группировки по менеджерам используется вспомогательная таблица partners_managers,
          // с помощью которой определяется, какой менеджер был привязан к партнеру в конкретный день.
          // Данные считаются по дням, то есть, если сменить менеджера в середине дня,
          // то вся стата за этот день пойдет новому менеджеру. @see \mcms\statistic\components\cron\handlers\GroupByManagers
          if ($this->addJoinGroupTable('partners_managers pm', $query)) {
            $query->addSelect([
              $this->groupFields[$field] => 'manager.username',
              'manager_id' => 'manager.id'
            ]);
//            $query->leftJoin('users muser', 'st.user_id = muser.id'); // TODO вроде вообще лишний джойн

            $userField = $this->isInvestorQuery($query)
              ? 'st.from_user_id'
              : 'st.user_id';

            $query->leftJoin('partners_managers pm', $userField . ' = pm.user_id AND st.date = pm.date');
            $query->leftJoin('users manager', 'manager.id = pm.manager_id');
          }
          break;
        case 'from_users':
          if (!$this->canGroupByUsers()) return;
          if ($this->addJoinGroupTable('users user', $query)) {
            /** @var \mcms\user\components\Api\User $api */
            $api = Yii::$app->getModule('users')->api('user');
            $api->join(
              new JoinQuery(
                $query,
                'st',
                ['LEFT JOIN', 'st.from_user_id', '=', 'user'],
                [
                  $this->groupFields[$field] => 'user.username',
                  'user_id' => 'st.from_user_id'
                ]
              )
            );
          }
          break;

        case 'landing_pay_types':
          if (!$this->canGroupByLandingPayTypes()) return;
          if ($this->addJoinGroupTable('landing_pay_types pt', $query)) {
            /** @var \mcms\promo\components\api\LandingPayTypeList $api */
            $api = $module->api('payTypes');
            $api->join(
              new JoinQuery(
                $query,
                'st',
                ['LEFT JOIN', 'st.landing_pay_type_id', '=', 'pt'],
                [$this->groupFields[$field] => 'pt.name']
              )
            );
          }
          break;
      }
      $query->having(['IS NOT', $this->groupFields[$field], null]);
    }

    if ($this->isGroupingBy('webmasterSources')) {
      $query->andWhere(['source.source_type' => Source::SOURCE_TYPE_WEBMASTER_SITE]);
    }

    if ($this->isGroupingBy('arbitraryLinks')) {
      $query->andWhere(['source.source_type' => [Source::SOURCE_TYPE_LINK, Source::SOURCE_TYPE_SMART_LINK]]);
    }

    $userRoles = Yii::$app->authManager->getRolesByUser($this->getViewerId());
    if (array_key_exists('partner', $userRoles)) {
      $query
        ->andWhere('st.user_id = :userId')
        ->addParams([':userId' => $this->getViewerId()])
      ;
    }

    foreach ($this->group as $field) {
      if ($groupField = $this->getMappedGroupField($field)) {

        $groupField == 'group' && $groupField = $this->groupFields[$field];

        // Инвестору группируем по его источникам и потокам
        if ($groupField == 'source_id' && $this->isInvestorQuery($query)) {
          $groupField = 'from_source_id';
        }
        if ($groupField == 'stream_id' && $this->isInvestorQuery($query)) {
          $groupField = 'from_stream_id';
        }
        $query->addGroupBy($groupField);
      }
    }
  }

  public function getRatio(array $row)
  {
    $rightPart = (int) ArrayHelper::getValue($row, 'count_ons', 0);
    $rightPart += (int) ArrayHelper::getValue($row, 'count_onetime', 0);
    $rightPart += (int) ArrayHelper::getValue($row, 'count_sold', 0);
    $rightRatioValue = $rightPart == 0
      ? 0
      : round($this->getAcceptedValue($row, null, true) / $rightPart, 1)
    ;
    return sprintf('1:%d', $rightRatioValue);
  }

  public function getCPAAdminSum(array $row, $currency)
  {
    return (float) ArrayHelper::getValue($row, 'onetime_real_profit_' . $currency, 0) +
      (float) ArrayHelper::getValue($row, 'sold_investor_price_' . $currency, 0);
  }

  public function getCPAResellerSum(array $row, $currency)
  {
    return (float) ArrayHelper::getValue($row, 'onetime_reseller_profit_' . $currency, 0) +
      (float) ArrayHelper::getValue($row, 'sold_reseller_price_' . $currency, 0);
  }

  /**
   * Получить сумму профита с ребиллов выкупленных и отклоненных подписок. Грубо говоря оборот реса с ЦПА
   * @param array $row
   * @param $currency
   * @return float
   */
  public function getCpaProfit(array $row, $currency)
  {
    return (float) ArrayHelper::getValue($row, 'sold_cpa_profit_' . $currency, 0) +
      (float) ArrayHelper::getValue($row, 'rejected_cpa_profit_' . $currency, 0);
  }

  /**
   * @param array $row
   * @param string $currency
   * @return float
   */
  public function getResellerTotalProfit(array $row, $currency)
  {
    $resellerProfit = 0;
    $resellerProfit += (float) ArrayHelper::getValue($row, 'sum_reseller_profit_' . $currency, 0);
    $resellerProfit += $this->getCpaProfit($row, $currency);
    $resellerProfit += (float) ArrayHelper::getValue($row, 'onetime_reseller_profit_' . $currency, 0);
      $resellerProfit += (float) ArrayHelper::getValue($row, 'sold_tb_reseller_profit_' . $currency, 0);

    return round($resellerProfit, 3);
  }

  /**
   * Оборот реселлера
   * @param array $row
   * @param string $currency
   * @return float[]
   */
  public function getResellerTurnover($row, $currency)
  {
    // TRICKY При добавлении новых параметров нужно учитывать их в total (если надо)
    $data = [
      'revshare' => (float)ArrayHelper::getValue($row, 'sum_reseller_profit_' . $currency, 0),
      'cpa_sold' => (float)ArrayHelper::getValue($row, 'sold_cpa_profit_' . $currency, 0),
      'cpa_rejected' => (float)ArrayHelper::getValue($row, 'rejected_cpa_profit_' . $currency, 0),
      'onetime' => (float)ArrayHelper::getValue($row, 'onetime_reseller_profit_' . $currency, 0),
    ];
    $data['cpa'] = round($data['cpa_sold'] + $data['cpa_rejected'], 3);
    $data['total'] = round($data['revshare'] + $data['cpa_sold'] + $data['cpa_rejected'] + $data['onetime'], 3);

    return $data;
  }

  private function getRealTotalProfit(array $row, $currency)
  {
    $realProfit = 0;
    $realProfit += (float) ArrayHelper::getValue($row, 'onetime_real_profit_' . $currency, 0);
    $realProfit += (float) ArrayHelper::getValue($row, 'sold_investor_price_' . $currency, 0);
    $realProfit += (float) ArrayHelper::getValue($row, 'sum_real_profit_' . $currency, 0);
    $realProfit += (float) ArrayHelper::getValue($row, 'sold_tb_real_profit_' . $currency, 0);
    return round($realProfit, 3);
  }

  private function getRealNetProfit(array $row, $currency)
  {
    return $this->getRealTotalProfit($row, $currency) - $this->getResellerTotalProfit($row, $currency);
  }

  private function getResellerNetProfit(array $row, $currency)
  {
    return $this->getResellerTotalProfit($row, $currency) - $this->getPartnerTotalProfit($row, $currency);
  }

  public function getPartnerTotalProfit(array $row, $currency)
  {
    $totalProfit = 0;
    $totalProfit += $this->revshareOrCPA === self::REVSHARE ? 0 : (float) ArrayHelper::getValue($row, 'onetime_profit_' . $currency, 0);
    $totalProfit += $this->revshareOrCPA === self::REVSHARE ? 0 : (float) ArrayHelper::getValue($row, 'sold_partner_profit_' . $currency, 0);
    $totalProfit += $this->revshareOrCPA === self::CPA ? 0 : (float) ArrayHelper::getValue($row, 'sum_profit_' . $currency, 0);

    return round($totalProfit, 3);
  }

  /**
   * Расходы на выкупленные подписки за всё время
   * @return float|int
   */
  public function getInvestorProfit(array $row, $currency)
  {
    $soldSum = ArrayHelper::getValue($row, 'buyout_investor_price_' . $currency, 0);
    return round($this->getPartnerTotalProfit($row, $currency) - $soldSum, 3);
  }

  /**
   * canViewAdminProfit
   * @param array $row
   * @return float
   */
  public function getTotalProfitAdmin(array $row, $currency = null)
  {
    if (!$currency) $currency = $this->getUserCurrency();
    return $this->getRealTotalProfit($row, $currency);
  }

  /**
   * canViewAdminProfit
   * @param array $row
   * @return float
   */
  public function getNetProfitAdmin(array $row, $currency = null)
  {
    if (!$currency) $currency = $this->getUserCurrency();
    return $this->getRealNetProfit($row, $currency);
  }

  /**
   * canViewAdminProfit
   * @param array $row
   * @return float
   */
  public function getNetProfitReseller(array $row, $currency = null)
  {
    if (!$currency) $currency = $this->getUserCurrency();
    return $this->getResellerNetProfit($row, $currency);
  }

  /**
   * @param array $row
   * @param null $currency
   * @return float
   */
  public function getInvestorBuyoutPrice(array $row, $currency = null)
  {
    if (!$currency) $currency = $this->getUserCurrency();
    return ArrayHelper::getValue($row, 'buyout_investor_price_' . $currency, 0);
  }

  public function handleFilters(Query &$query)
  {
    /** @var $query StatisticQuery */
    !$this->isGroupingBy('hour')
      ? $query
      ->andFilterWhere(['>=', 'st.date', $this->formatDateDB($this->start_date)])
      ->andFilterWhere(['<=', 'st.date', $this->formatDateDB($this->end_date)])
      : $query->andFilterWhere(['=', 'st.date', $this->formatDateDB($this->end_date)])
    ;

    if ($this->canFilterByLandings()) {
      $query->andFilterWhere(['st.landing_id' => $this->landings]);
    }

    if ($this->canFilterByLandingCategories()) {
      // Джойн специально для фильтрации по категориям лендингов
      if ($this->addJoinGroupTable('landings l', $query)) {
        /** @var \mcms\promo\Module $module */
        $module = Yii::$app->getModule('promo');
        /** @var \mcms\promo\components\api\LandingList $api */
        $api = $module->api('landings');
        $api->join(
          new JoinQuery(
            $query,
            'st',
            ['LEFT JOIN', 'st.landing_id', '=', 'l'],
            [
              'landing_id' => 'l.id'
            ]
          )
        );
      }
      $query->andFilterWhere(['l.category_id' => $this->landingCategories]);
    }

    if ($this->canFilterByOperators()) {
      $query->andFilterWhere(['st.operator_id' => $this->operators]);
    }

    if ($this->canFilterBySources()) {
      $sourceIds = array_merge(
        (empty($this->sources) ? [] : (is_array($this->sources) ? $this->sources : [$this->sources])),
        (empty($this->webmasterSources) ? [] : (is_array($this->webmasterSources) ? $this->webmasterSources : [$this->webmasterSources])),
        (empty($this->arbitraryLinks) ? [] : (is_array($this->arbitraryLinks) ? $this->arbitraryLinks : [$this->arbitraryLinks]))
      );

      $query->andFilterWhere(['st.source_id' => $sourceIds]);
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

//    $notAvailableUserIds = [];
    if ($this->canFilterByUsers()) {
      $query->andFilterWhere(['st.user_id' => $this->users]);
    }
//      $notAvailableUserIds = Yii::$app->getModule('users')
//        ->api('notAvailableUserIds', [
//          'userId' => $this->getViewerId(),
//        ])
//        ->getResult();

      // Скрытие статистики недоступных пользователей для менеджеров
      UserModel::filterUsersItemsByUser($this->getViewerId(), $query, 'st',
        $this->isInvestorQuery($query) ? 'from_user_id' : 'user_id');
//    } else {
//      $field = $query->getId() == self::BUYOUT_STAT ? 'to_user_id' : 'user_id';
//      $query->andWhere(['st.' . $field => $this->getViewerId()]);
//    }

//    if ($this->excludeUserIds) {
//      $notAvailableUserIds = array_merge($notAvailableUserIds, $this->excludeUserIds);
//    }
//
//    if ($notAvailableUserIds) {
//      $query->andWhere(['not in', 'st.user_id', $notAvailableUserIds]);
//    }

    if ($this->canFilterByFakeRevshare()) {
      if (self::SUBSCRIPTIONS_STAT == $query->getId()) {
        $query->andFilterWhere(['st.is_fake' => $this->isFake]);
      } else if (
        // ЕСЛИ В ФИЛЬТРЕ НАДО ПОКАЗАТЬ ТОЛЬКО ФЕЙКИ, ТО ВЫКУПЫ И ИК НАДО ОБНУЛИТЬ, Т.К. У ЦПА НЕТ ФЕЙКОВ
        // таким же образом прячем хиты и прочее
        $this->isFake &&
        count($this->isFake) == 1 &&
        reset($this->isFake) == 1 &&
        self::SUBSCRIPTIONS_STAT != $query->getId()
      ) {
        $query->andWhere('0 = 1');
      }
    }

    if ($this->canFilterByProviders()) {
      $query->andFilterWhere(['st.provider_id' => $this->providers]);
    }

    if ($this->canFilterByLandingPayTypes()) {
      $query->andFilterWhere(['st.landing_pay_type_id' => $this->landing_pay_types]);
    }
  }

  protected function filterByCurrency(Query &$query, $orUndefined = false)
  {
    if (!$this->isFilterByCurrency()) return;

    if ($orUndefined) {
      if ($this->groupByCurrency) {
        $query->addSelect('st.currency_id')->addGroupBy('st.currency_id');
      } else {
        $query->andWhere([
          'or',
          ['st.currency_id' => 0],
          ['st.currency_id' => $this->allCurrencies[$this->currency]]
        ]);
      }
      return;
    }
    if ($this->groupByCurrency) {
      $query->addSelect('st.currency_id')->andWhere('st.currency_id > 0')->addGroupBy('st.currency_id');
    } else {
      $query->andWhere(['st.currency_id' => $this->allCurrencies[$this->currency]]);
    }
  }

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
      'webmasterSources',
      'arbitraryLinks',
      'revshareOrCPA',
    ];
  }

  public function getMappedGroupField($field = null)
  {
    $field === null && $field = $this->group[0];
    return ArrayHelper::getValue($this->groupFieldsMap, $field);
  }

  public function isGroupingBy($group)
  {
    return in_array($group, $this->group);
  }

  public function setGroup($field)
  {
    $this->_group = is_array($field) ? $field : [$field];
  }

  public function getGroup()
  {
    return $this->_group;
  }

  public function updateGroup($from, $to)
  {
    $group = $this->group;
    for ($i = 0; $i < count($this->group); $i++) {
      if ($group[$i] == $from) {
        $group[$i] = $to;
      }
    }
    $this->group = $group;
  }

  public function isGroupingByHour($field = null)
  {
    return $field ? $field == 'hour' : $this->isGroupingBy('hour');
  }

  public function isGroupingByDate($field = null)
  {
    return $field ? $field == 'date' : $this->isGroupingBy('date');
  }

  public function isGroupingByMonth($field = null)
  {
    return $field ? $field == 'month_number' : $this->isGroupingBy('month_number');
  }

  public function isGroupingByWeek($field = null)
  {
    return $field ? $field == 'week_number' : $this->isGroupingBy('week_number');
  }

  public function isGroupingByArbitraryLink($field = null)
  {
    return $field ? $field == 'arbitraryLinks' : $this->isGroupingBy('arbitraryLinks');
  }

  public function isGroupingByLandings($field = null)
  {
    return $field ? $field == 'landings' : $this->isGroupingBy('landings');
  }

  public function isGroupingBySources($field = null)
  {
    return $field ? $field == 'webmasterSources' : $this->isGroupingBy('webmasterSources');
  }

  public function isGroupingByStreams($field = null)
  {
    return $field ? $field == 'streams' : $this->isGroupingBy('streams');
  }

  public function getIndexField($row)
  {
    $fields = [];
    foreach ($this->group as $field) {
      $groupIndexField = $this->groupIndexBy[$field];
      $groupIndexField == 'group' && $groupIndexField = $this->getGroupFieldName($field);
      $fields[] = strtr($groupIndexField, $row);
    }

    return implode('-', $fields);
  }

  /**
   * @param array $statArray
   * @param bool $groupByCurrency Производить ли группировку по валюте при индексации
   * (данный флаг нужно поставить в false, если запрос статистики не зависит от валюты)
   * @return array
   */
  protected function indexBy(yii\db\BatchQueryResult $statArray, $groupByCurrency = true)
  {
    $result = [];
    foreach ($statArray as $item) {
      if ($this->groupByCurrency) {
        // хак чтобы для хитов и жалоб валюта подставлялась (по хитам и жалобам нет группировки)
        $currencyKey = $groupByCurrency ? $item['currency_id'] : $this->currency;
        $result[$currencyKey][$this->getIndexField($item)] = $item;
      } else {
        $result[$this->getIndexField($item)] = $item;
      }
    }
    return $result;
  }

  public function formatGroup($row, $group)
  {
    $groupField = $this->groupFields[$group];
    switch ($group) {
      case 'landings':
        $link = Yii::$app->getModule('promo')->api('landingById', [
          'landingId' => ArrayHelper::getValue($row, 'landing_id')
        ])->getUrlParam();

        return Html::a(
          sprintf('#%s. %s', ArrayHelper::getValue($row, 'landing_id'), ArrayHelper::getValue($row, $groupField)),
          $link,
          ['data-pjax' => 0, 'target' => '_blank'],
          [],
          false
        );

        break;
      case 'webmasterSources':
      case 'arbitraryLinks':
        $link = Yii::$app->getModule('promo')->api('sourceById', [
          'source_id' => ArrayHelper::getValue($row, 'source_id')
        ])->getUrlParam();

        return Html::a(
          sprintf('#%s. %s', ArrayHelper::getValue($row, 'source_id'), ArrayHelper::getValue($row, $groupField)),
          $link,
          ['data-pjax' => 0, 'target' => '_blank'],
          [],
          false
        );
        break;
      case 'streams':
        $link = Yii::$app->getModule('promo')->api('stream', [
          'streamId' => ArrayHelper::getValue($row, 'stream_id')
        ])->getGridViewUrlParam();

        return Html::a(
          sprintf('#%s. %s', ArrayHelper::getValue($row, 'stream_id'), ArrayHelper::getValue($row, $groupField)),
          $link,
          ['data-pjax' => 0, 'target' => '_blank'],
          [],
          false
        );
        break;
      case 'platforms':
        $link = Yii::$app->getModule('promo')->api('platformId', [
          'platformId' => ArrayHelper::getValue($row, 'platform_id')
        ])->getGridViewUrlParam();

        return Html::a(
          sprintf('#%s. %s', ArrayHelper::getValue($row, 'platform_id'), ArrayHelper::getValue($row, $groupField)),
          $link,
          ['data-pjax' => 0, 'target' => '_blank'],
          [],
          false
        );
        break;
      case 'users':
      case 'from_users':
        $link = Yii::$app->getModule('users')->api('getOneUser', [
          'user_id' => ArrayHelper::getValue($row, 'user_id')
        ])->getUrlParam();

        return Html::a(
          sprintf('#%s. %s', ArrayHelper::getValue($row, 'user_id'), ArrayHelper::getValue($row, $groupField)),
          $link,
          ['data-pjax' => 0, 'target' => '_blank'],
          [],
          false
        );
        break;
      case 'managers':
        $link = Yii::$app->getModule('users')->api('getOneUser', [
          'user_id' => ArrayHelper::getValue($row, 'manager_id')
        ])->getUrlParam();

        return Html::a(
          sprintf('#%s. %s', ArrayHelper::getValue($row, 'manager_id'), ArrayHelper::getValue($row, $groupField)),
          $link,
          ['data-pjax' => 0, 'target' => '_blank'],
          [],
          false
        );
        break;
    }

    $format = strtr($this->groupFormat[$group], ['group' => $this->groupFields[$group]]);

    return strtr($format, $row);
  }


  /**
   * Расчёт итогового ратио (в футере) в зависимости от типа ревшар|цпа. Вычисляется из результата CR.
   * @param $type
   * @return float|int
   */
  protected function getResultRatio($type, $visibleToPartnerOnly = false)
  {
    $cr = $this->getResultCr($type, $visibleToPartnerOnly);

    if ($cr == 0) return 0;

    return 1 / ($cr / 100);
  }


  /**
   * Расчёт итогового количества принятого трафика (в футере) в зависимости от типа ревшар|цпа,
   * а также от того разрешено ли использовать уники вместо хитов.
   * @param $type
   * @param bool $allowUniques
   * @return float|int
   */
  protected function getResultAccepted($type = null, $allowUniques = false)
  {
    $sum = 0;
    foreach ($this->_statData as $item) {
      $sum += $this->getAcceptedValue($item, $type, $allowUniques);
    }
    return $sum;
  }

  /**
   * Расчёт итогового (в футере) количества конверсий CPA.
   * @return float|int
   */
  protected function getResultCpaCount()
  {
    return $this->getResultValue('count_sold') + $this->getResultValue('count_onetime');
  }

  /**
   * Расчёт итогового CR (в футере) в зависимости от типа ревшар|цпа.
   * @param $type
   * @param bool $visibleToPartnerOnly только для видимых партнеру подписок
   * @return float|int
   */
  protected function getResultCr($type, $visibleToPartnerOnly = false)
  {
    $cr = new Cr();
    if ($type == self::REVSHARE) {
      $cr->convertionsCount = $this->getResultValue('count_ons');
    }
    if ($type == self::CPA) {
      $cr->convertionsCount = $visibleToPartnerOnly
        ? $this->getResultValue('partner_visible_count_cpa_ons')
        : $this->getResultValue('count_cpa_ons');
    }
    if ($type == self::ONETIME) {
      $cr->convertionsCount = $visibleToPartnerOnly
        ? $this->getResultValue('partner_visible_count_onetime')
        : $this->getResultValue('count_onetime');
    }
    $cr->fullCount = $this->getResultAccepted($type, true);

    return $cr->getRate() * 100;
  }

  /**
   * @param $field
   * @return float|int
   */
  public function getResultValue($field)
  {
    if (isset($this->_fieldResults[$field])) return $this->_fieldResults[$field];

    if (is_null($this->_statData)) $this->getStatisticGroup();

    $sum = 0;

    switch ($field) {
      case 'count_accepted':
        foreach ($this->_statData as $row) {
          $sum += $this->getAcceptedValue($row);
        }
        break;
      case 'revshare_count_accepted':
        foreach ($this->_statData as $row) {
          $sum += $this->getAcceptedValue($row, self::REVSHARE);
        }
        break;
      case 'cpa_count_accepted':
        foreach ($this->_statData as $row) {
          $sum += $this->getAcceptedValue($row, self::CPA);
        }
        break;
      case 'onetime_count_accepted':
        foreach ($this->_statData as $row) {
          $sum += $this->getAcceptedValue($row, self::ONETIME);
        }
        break;
      case 'revshare_ratio':
        $sum = $this->getResultRatio(self::REVSHARE);
        break;
      case 'cr_revshare_ratio' :
        $sum = $this->getResultCr(self::REVSHARE);
        break;
      case 'onetime_ratio' :
        $sum = $this->getResultRatio(self::ONETIME);
        break;
      case 'cr_onetime_ratio' :
        $sum = $this->getResultCr(self::ONETIME);
        break;
      case 'cr_partner_visible_onetime_ratio' :
        $sum = $this->getResultCr(self::ONETIME, true);
        break;
      case 'cpa_ratio':
        $sum = $this->getResultRatio(self::CPA);
        break;
      case 'cr_cpa_ratio':
        $sum = $this->getResultCr(self::CPA);
        break;
      case 'cr_partner_visible_cpa_ratio':
        $sum = $this->getResultCr(self::CPA, true);
        break;
      case 'partner_visible_cpa_ratio':
        $sum = $this->getResultRatio(self::CPA, true);
        break;
      case 'cpa_count':
        $sum = $this->getResultCpaCount();
        break;
      case 'count_cpa_ons':
        foreach ($this->_statData as $row) {
          $sum += $this->getCountCpaOns($row);
        }
        break;
      case 'partner_visible_count_cpa_ons':
        foreach ($this->_statData as $row) {
          $sum += $this->getCountCpaOns($row, true);
        }
        break;
      case 'count_cpa_offs':
        foreach ($this->_statData as $row) {
          $sum += $this->getCountCpaOffs($row);
        }
        break;
      case 'cpa_cr_sold':
        $sum = $this->getResultCpaCrSold();
        break;
      case 'onetime_reseller_net_profit_rub':
      case 'onetime_reseller_net_profit_usd':
      case 'onetime_reseller_net_profit_eur':
      $currency = str_replace('onetime_reseller_net_profit_', '', $field);
        foreach ($this->_statData as $row) {
          $sum += $this->getOnetimeResellerNetProfit($row, $currency);
        }
        break;
      case 'cpa_reseller_net_profit_rub':
      case 'cpa_reseller_net_profit_usd':
      case 'cpa_reseller_net_profit_eur':
        $currency = str_replace('cpa_reseller_net_profit_', '', $field);
        foreach ($this->_statData as $row) {
          $sum += $this->getCpaResellerNetProfit($row, $currency);
        }
        break;
      case 'revshare_reseller_net_profit_rub':
      case 'revshare_reseller_net_profit_usd':
      case 'revshare_reseller_net_profit_eur':
        $currency = str_replace('revshare_reseller_net_profit_', '', $field);
        foreach ($this->_statData as $row) {
          $sum += $this->getRevshareResellerNetProfit($row, $currency);
        }
        break;
      case 'cpa_count_scope_offs':
        foreach ($this->_statData as $row) {
          $sum += $this->getCpaOff24($row);
        }
        break;
      case 'cpa_count_rebills_date_by_date':
        foreach ($this->_statData as $row) {
          $sum += $this->getCpaRebillsDateByDate($row);
        }
        break;
      case 'cpa_count_rebills':
        foreach ($this->_statData as $row) {
          $sum += $this->getCpaRebills($row);
        }
        break;
      case 'cpa_count_rebills_sold':
        foreach ($this->_statData as $row) {
          $sum += $this->getSoldRebills($row);
        }
        break;
      case 'cpa_ecpm_rub':
      case 'cpa_ecpm_usd':
      case 'cpa_ecpm_eur':
        $sum = $this->getResultECPM($field);
        break;
      case 'revshare_ecpc_rub':
      case 'revshare_ecpc_usd':
      case 'revshare_ecpc_eur':
        $sum = $this->getResultEcpcRevshare($field);
        break;
      case 'cpa_ecpc_rub':
      case 'cpa_ecpc_usd':
      case 'cpa_ecpc_eur':
        $sum = $this->getResultEcpcCpa($field);
        break;
      case 'onetime_ecpc_rub':
      case 'onetime_ecpc_usd':
      case 'onetime_ecpc_eur':
        $sum = $this->getResultEcpcOnetime($field);
        break;
      case 'cpr_rub':
      case 'cpr_usd':
      case 'cpr_eur':
        $sum = $this->getResultCPR($field);
        break;
      case 'avg_cpa_rub':
      case 'avg_cpa_usd':
      case 'avg_cpa_eur':
        $sum = $this->getResultAvgCPA($field);
        break;
      case 'cpa_sum_rub':
      case 'cpa_sum_usd':
      case 'cpa_sum_eur':
        $sum = $this->getResultPartnerCPASum($field);
        break;
      case 'total_sum_rub':
      case 'total_sum_usd':
      case 'total_sum_eur':
        foreach ($this->_statData as $row) {
          $sum += $this->getPartnerTotalProfit($row, str_replace('total_sum_', '', $field));
        }
        break;
      case 'sold_total_sum_rub':
      case 'sold_total_sum_usd':
      case 'sold_total_sum_eur':
        foreach ($this->_statData as $row) {
          $sum += ArrayHelper::getValue($row, 'sold_price_' . str_replace('sold_total_sum_', '', $field), 0);
        }
        break;
      case 'admin_total_profit_rub':
      case 'admin_total_profit_usd':
      case 'admin_total_profit_eur':
        $sum = $this->getResultAdminSum($field);
        break;
      case 'admin_net_profit_rub':
      case 'admin_net_profit_usd':
      case 'admin_net_profit_eur':
        $sum = $this->getResultAdminNetSum($field);
        break;
      case 'reseller_total_profit_rub':
      case 'reseller_total_profit_usd':
      case 'reseller_total_profit_eur':
        foreach ($this->_statData as $row) {
          $sum += $this->getResellerTotalProfit($row, str_replace('reseller_total_profit_', '', $field));
        }
        break;
      case 'reseller_net_profit_rub':
      case 'reseller_net_profit_usd':
      case 'reseller_net_profit_eur':
        $sum = $this->getResultResellerNetSum($field);
        break;
      case 'investor_buyout_price_rub':
      case 'investor_buyout_price_eur':
      case 'investor_buyout_price_usd':
        $sum = $this->getResultInvestorBuyoutPrice($field);
        break;
      case 'cpa_profit_rub':
      case 'cpa_profit_eur':
      case 'cpa_profit_usd':
        $currency = str_replace('cpa_profit_', '', $field);
        foreach ($this->_statData as $row) {
          $sum += $this->getCpaProfit($row, $currency);
        }
        break;
      case 'cpr2_rub':
      case 'cpr2_usd':
      case 'cpr2_eur':
        $sum = $this->getResultCPR2($field);
        break;
      case 'charge_ratio':
        $sum = $this->getResultChargeRatio($field);
        break;
      case 'cpa_charge_ratio':
        $ons = $this->getResultValue('count_cpa_ons');

        if ($ons == 0) {
          return 0;
        }

        $chargesOnDate = $this->getResultValue('cpa_count_rebills_date_by_date');

        $sum = $chargesOnDate / $ons;
        break;
      case 'cpa_sum_profit_date_by_date_rub':
      case 'cpa_sum_profit_date_by_date_usd':
      case 'cpa_sum_profit_date_by_date_eur':
        foreach ($this->_statData as $row) {
          $sum += $this->getCpaSumOnDate($row, str_replace('cpa_sum_profit_date_by_date_', '', $field));
        }
        break;
      case 'rev_sub_rub':
      case 'rev_sub_usd':
      case 'rev_sub_eur':
        $sum = $this->getResultRevSub($field);
        break;
      case 'roi_on_date_rub':
      case 'roi_on_date_usd':
      case 'roi_on_date_eur':
        $sum = $this->getResultRoiOnDate($field);
        break;
      case 'visible_subscriptions':
        $sum = $this->getResultValue('sold_visible_subscriptions');
        break;
      case 'partner_complains':
        $sum = $this->getPartnerComplainsCount();
        break;
      case 'ecpc_rub':
      case 'ecpc_usd':
      case 'ecpc_eur':
        $cr = new Cr();
        $cr->convertionsCount = $this->getResultValue('total_sum_' . str_replace('ecpc_', '', $field));
        $cr->fullCount = $this->getResultValue('count_hits');
        $sum = $cr->getRate();
        break;
      case 'ecp_rub':
      case 'ecp_usd':
      case 'ecp_eur':
        $cr = new Cr();
        $cr->convertionsCount = $this->getResultValue('sold_total_sum_' . str_replace('ecp_', '', $field));
        $cr->fullCount = $this->getResultValue('count_hits');
        $sum = $cr->getRate();
        break;
      case 'complains_rate':
        $cr = new Cr();
        $cr->convertionsCount = $this->getResultValue('count_calls')
          + $this->getResultValue('count_complains')
          + $this->getResultValue('count_calls_mno');
        $cr->fullCount = $this->getResultValue('count_ons')
          + $this->getResultValue('count_cpa_ons')
          + $this->getResultValue('count_onetime');
        $sum = $cr->getRate();
        break;
      default:
        foreach ($this->_statData as $row) {
          $sum += floatval(ArrayHelper::getValue($row, $field, 0));
        }
    }

    return $this->_fieldResults[$field] = $sum;
  }



  /**
   * @param $field
   * @return float|null
   */
  public function getResultECPM($field)
  {
    $currency = str_replace('cpa_ecpm_', '', $field);

    $cpaSum = 0;
    $accepted = 0;
    foreach ($this->_statData as $row) {
      $cpaSum += $this->getCPASum($row, $currency);
      $accepted += $this->getAcceptedValue($row, self::CPA);
    }

    if (!$accepted) return 0;

    return $cpaSum / ($accepted / 1000);
  }


  /**
   * @param $field
   * @return float|null
   */
  public function getResultCPR($field)
  {
    $currency = str_replace('cpr_', '', $field);

    $profitSum = 0;
    $countSoldSum = 0;
    foreach ($this->_statData as $row) {
      $profitSum +=  ArrayHelper::getValue($row, 'sold_partner_price_' . $currency, 0);
      $countSoldSum += (int) ArrayHelper::getValue($row, 'partner_visible_count_sold', 0);
    }

    if (!$countSoldSum) return 0;

    return $profitSum / $countSoldSum;
  }

  /**
   * @param $field
   * @return float|null
   */
  public function getResultAvgCPA($field)
  {
    $currency = str_replace('avg_cpa_', '', $field);

    $profitSum = 0;
    $countSoldSum = 0;
    foreach ($this->_statData as $row) {
      $profitSum +=  ArrayHelper::getValue($row, 'sold_partner_profit_' . $currency, 0);
      $countSoldSum += (int) ArrayHelper::getValue($row, 'count_sold', 0);
    }

    if (!$countSoldSum) return 0;

    return $profitSum / $countSoldSum;
  }

  public function getResultCPR2($field)
  {
    $currency = str_replace('cpr2_', '', $field);

    $ons = 0;
    $investorBuyout = 0;
    foreach ($this->_statData as $row) {
      $ons +=  (int) ArrayHelper::getValue($row, 'count_ons', 0);
      $investorBuyout += (int) ArrayHelper::getValue($row, 'buyout_investor_price_' . $currency, 0);
    }

    if (!$ons) return 0;

    return $investorBuyout / $ons;
  }

  public function getResultChargeRatio($field)
  {
    $ons = 0;
    foreach ($this->_statData as $row) {
      $ons +=  (int) ArrayHelper::getValue($row, 'count_ons', 0);
    }

    if (!$ons) return 0;

    return $this->getResultValue('count_rebills_date_by_date') / $ons;
  }

  /**
   * @param array $row
   * @param $currency
   * @return float|null
   */
  public function getRevSub(array $row, $currency)
  {
    $ons = (int) ArrayHelper::getValue($row, self::CPA . '_count_ons', 0) -
      ArrayHelper::getValue($row, self::CPA . '_rejected_count_ons', 0);

    if ($ons == 0) {
      return null;
    }

    $sumOnDate = ArrayHelper::getValue($row, 'sold_sum_profit_date_by_date_' . $currency, 0);

    return $sumOnDate / $ons;
  }

  /**
   * @param array $row
   * @param $currency
   * @return float|null
   */
  public function getRoiOnDate(array $row, $currency)
  {
    $investorBuyout = (float)ArrayHelper::getValue($row, 'buyout_investor_price_' . $currency, 0);
    $sumOnDate = (float)ArrayHelper::getValue($row, 'sold_sum_profit_date_by_date_' . $currency, 0);

    if ($investorBuyout == 0 || $sumOnDate == 0) {
      return 0;
    }

    return (($sumOnDate / $investorBuyout) - 1) * 100;
  }

  /**
   * @param $field
   * @return float|int|null
   */
  public function getResultRevSub($field)
  {
    $currency = str_replace('rev_sub_', '', $field);

    $ons = 0;
    foreach ($this->_statData as $row) {
      $ons +=  (int) ArrayHelper::getValue($row, self::CPA . '_count_ons', 0) -
        ArrayHelper::getValue($row, self::CPA . '_rejected_count_ons', 0);
    }

    if ($ons === 0) {
      return null;
    }

    return $this->getResultValue('sold_sum_profit_date_by_date_' . $currency) / $ons;
  }

  /**
   * @param $field
   * @return float|int
   */
  public function getResultRoiOnDate($field)
  {
    $currency = str_replace('roi_on_date_', '', $field);

    $investorBuyout = 0;
    $sumOnDate = 0;
    foreach ($this->_statData as $row) {
      $investorBuyout += (float)ArrayHelper::getValue($row, 'buyout_investor_price_' . $currency, 0);
      $sumOnDate += (float)ArrayHelper::getValue($row, 'sold_sum_profit_date_by_date_' . $currency, 0);
    }

    if ($investorBuyout == 0 || $sumOnDate == 0) {
      return 0;
    }

    return (($sumOnDate / $investorBuyout) - 1) * 100;
  }

  /**
   * @param $field
   * @return float|int
   */
  public function getResultPartnerCPASum($field)
  {
    $currency = str_replace('cpa_sum_', '', $field);

    $cpaSum = 0;
    foreach ($this->_statData as $row) {
      $cpaSum += $this->getCPASum($row, $currency);
    }

    return $cpaSum;
  }

  /**
   * @param $field
   * @return float|int
   */
  public function getResultAdminSum($field)
  {
    $currency = str_replace('admin_total_profit_', '', $field);

    $sum = 0;
    foreach ($this->_statData as $row) {
      $sum += $this->getTotalProfitAdmin($row, $currency);
    }

    return $sum;
  }

  /**
   * @param $field
   * @return float|int
   */
  public function getResultAdminNetSum($field)
  {
    $currency = str_replace('admin_net_profit_', '', $field);

    $sum = 0;
    foreach ($this->_statData as $row) {
      $sum += $this->getNetProfitAdmin($row, $currency);
    }

    return $sum;
  }

  /**
   * @param $field
   * @return float|int
   */
  public function getResultResellerSum($field)
  {
    $currency = str_replace('reseller_total_profit_', '', $field);

    $sum = 0;
    foreach ($this->_statData as $row) {
      $sum += $this->getTotalProfitReseller($row, $currency);
    }

    return $sum;
  }

  /**
   * @param $field
   * @return float|int
   */
  public function getResultResellerNetSum($field)
  {
    $currency = str_replace('reseller_net_profit_', '', $field);

    $sum = 0;
    foreach ($this->_statData as $row) {
      $sum += $this->getNetProfitReseller($row, $currency);
    }

    return $sum;
  }

  /**
   * @param $field
   * @return float
   */
  public function getResultInvestorBuyoutPrice($field)
  {
    $currency = str_replace('investor_buyout_price_', '', $field);

    $sum = 0;
    foreach ($this->_statData as $row) {
      $sum += $this->getInvestorBuyoutPrice($row, $currency);
    }

    return $sum;
  }

  /**
   * Получение массива параметров для подстановки в урл для строки статы
   * @param $item
   * @return array
   */
  public function getRowFilterArray($item)
  {
    $fields = $this->getFilterFields();
    $urlParams = [];
    foreach ($fields AS $field) {
      if (!$this->$field) continue;
      $urlParams[$field] = $this->$field;
    }

    // Передаем фильтр для строки, по которой группируем
    foreach ($this->group as $group) {
      switch ($group) {
        case 'date':
          $urlParams['start_date'] = $urlParams['end_date'] = ArrayHelper::getValue($item, $this->groupFields[$group]);
          break;
        case 'hour':
          $urlParams['start_date'] = $urlParams['end_date'] = $this->end_date;
          break;
        default:
          $urlParams[$group] = [ArrayHelper::getValue($item, $this->getMappedGroupField($group))];
          $urlParams['start_date'] = $this->start_date;
          $urlParams['end_date'] = $this->end_date;
      }
    }

    return $urlParams;
  }

  /**
   * @return bool
   */
  protected function isFilterByCurrency()
  {
    if (!$this->canFilterByCurrency()) return false;

    return !$this->ignoreCurrencyFilter;
  }

  /**
   * @return bool
   */
  protected function isHourTables()
  {
    return !!count(array_intersect($this->group, ['hour', 'date_hour']));
  }

  /**
   * tricky Подмена данных статистики. Нужна для получения данных от лица нескольких пользователей (см. mcms\statistic\components\StatisticByRoles)
   * @param array $statData
   */
  public function setStatData($statData)
  {
    $this->_statData = $statData;
  }

  /**
   * Считает общее число жалоб для строки доступное для просмотра партнеру
   * @param $row
   * @return int|mixed
   */
  public function getComplainsCount($row)
  {
    $sum = 0;
    if ($this->statisticModule->canPartnerViewComplainText()) {
      $sum += ArrayHelper::getValue($row, 'count_complains', 0);
    }
    if ($this->statisticModule->canPartnerViewComplainCall()) {
      $sum += ArrayHelper::getValue($row, 'count_calls', 0);
    }
    if ($this->statisticModule->canPartnerViewComplainAuto24()) {
      $sum += ArrayHelper::getValue($row, 'count_auto24', 0);
    }
    if ($this->statisticModule->canPartnerViewComplainAutoMoment()) {
      $sum += ArrayHelper::getValue($row, 'count_auto_moment', 0);
    }
    if ($this->statisticModule->canPartnerViewComplainAutoDuplicate()) {
      $sum += ArrayHelper::getValue($row, 'count_auto_duplicate', 0);
    }
    if ($this->statisticModule->canPartnerViewComplainCallMno()) {
      $sum += ArrayHelper::getValue($row, 'count_calls_mno', 0);
    }

    return $sum;
  }

  /**
   * Считает общее число жалоб по всему столбцу доступное для просмотра партнеру
   * @return float|int
   */
  public function getPartnerComplainsCount()
  {
    $sum = 0;
    if ($this->statisticModule->canPartnerViewComplainText()) {
      $sum += $this->getResultValue('count_complains');
    }
    if ($this->statisticModule->canPartnerViewComplainCall()) {
      $sum += $this->getResultValue('count_calls');
    }
    if ($this->statisticModule->canPartnerViewComplainAuto24()) {
      $sum += $this->getResultValue('count_auto24');
    }
    if ($this->statisticModule->canPartnerViewComplainAutoMoment()) {
      $sum += $this->getResultValue('count_auto_moment');
    }
    if ($this->statisticModule->canPartnerViewComplainAutoDuplicate()) {
      $sum += $this->getResultValue('count_auto_duplicate');
    }
    if ($this->statisticModule->canPartnerViewComplainCall()) {
      $sum += $this->getResultValue('count_calls_mno');
    }

    return $sum;
  }

  /**
   * TRICKY Тут весьма специфический расчёт для Принятого ревшар и ЦПА трафика,
   * т.к. выкупленный ЦПА становится с флагом is_cpa=0. А также ИК траф записываем с флагом is_cpa=0.
   *
   * @param array $row
   * @param string|null $type
   * @param bool $allowUnique - не используется. Для совместимости с родительским методом.
   * @return int
   */
  public function getAcceptedValue(array $row, $type = null, $allowUnique = false)
  {
    $typePrefix = in_array($type, [self::REVSHARE, self::CPA, self::ONETIME]) ? $type . '_' : '';

    $hits = (int)ArrayHelper::getValue($row, $typePrefix . 'count_hits', 0);
    $tb = (int)ArrayHelper::getValue($row, $typePrefix . 'count_tb', 0);

    switch ($type) {
      case self::CPA:
        /**
         * Принято CPA = cpa_count_hits + выкупленные хиты - cpa_count_tb - ИК хиты + ИК ТБ
         */
        return $hits
          - $tb
          - (int)ArrayHelper::getValue($row, self::ONETIME . '_count_hits', 0)
          + (int)ArrayHelper::getValue($row, self::ONETIME . '_count_tb', 0);
    }

    return $hits - $tb;
  }

  /**
   * Сколько всего подписок по ЦПА (проданные и отклоненные в сумме)
   * @param $item
   * @param bool $visibleToPartnerOnly вернуть только видимые партнеру
   * @return int
   */
  public function getCountCpaOns($item, $visibleToPartnerOnly = false)
  {
    $countSold = ArrayHelper::getValue($item, self::CPA . '_count_ons', 0);

    if ($visibleToPartnerOnly) {
      $countSold = ArrayHelper::getValue($item, 'partner_visible_count_sold', 0);
    }

    return (int)$countSold;
  }

  /**
   * Сколько всего первичного профита с подписок по ЦПА (проданные и отклоненные в сумме)
   * @param $item
   * @param $currency
   * @return int
   */
  public function getCpaSumOnDate($item, $currency)
  {
    return ArrayHelper::getValue($item, 'sold_sum_profit_date_by_date_' . $currency, 0)
      + ArrayHelper::getValue($item, 'cpa_rejected_sum_profit_date_by_date_' . $currency, 0);
  }

  /**
   * Сколько всего отписок по ЦПА (проданные и отклоненные в сумме)
   * @param $item
   * @return int
   */
  public function getCountCpaOffs($item)
  {
    return (int)ArrayHelper::getValue($item, self::CPA . '_rejected_count_offs', 0)
      + (int)ArrayHelper::getValue($item, 'count_offs_sold', 0);
  }

  /**
   * Сколько всего ребиллов по ЦПА (проданные и отклоненные в сумме)
   * @param $item
   * @return int
   */
  public function getCpaRebills($item)
  {
    return $this->getSoldRebills($item) + $this->getRejectedRebills($item);
  }

  /**
   * Сколько проданных ребиллов
   * @param $item
   * @return int
   */
  public function getSoldRebills($item)
  {
    return (int)ArrayHelper::getValue($item, 'count_rebills_sold', 0);
  }

  /**
   * Сколько отклоненных ЦПА ребиллов
   * @param $item
   * @return int
   */
  public function getRejectedRebills($item)
  {
    return (int)ArrayHelper::getValue($item, self::CPA . '_rejected_count_rebills', 0);
  }

  /**
   * Сколько всего отписок24 по ЦПА (проданные и отклоненные в сумме)
   * @param $item
   * @return int
   */
  public function getCpaOff24($item)
  {
    return (int)ArrayHelper::getValue($item, self::CPA . '_rejected_count_scope_offs', 0)
      + (int)ArrayHelper::getValue($item, 'sold_count_scope_offs', 0);
  }

  /**
   * Сколько всего первичных ребиллов по ЦПА (проданные и отклоненные в сумме)
   * @param array $row
   * @return int
   */
  public function getCpaRebillsDateByDate(array $row)
  {
    return (int) ArrayHelper::getValue($row, 'sold_count_rebills_date_by_date', 0)
      + (int) ArrayHelper::getValue($row, 'cpa_rejected_count_rebills_date_by_date', 0);
  }

  /**
   * @param array $row
   * @return float|int
   */
  public function getCpaChargeRatio(array $row)
  {
    $ons = $this->getCountCpaOns($row);

    if ($ons == 0) {
      return 0;
    }

    $chargesOnDate = $this->getCpaRebillsDateByDate($row);

    return $chargesOnDate / $ons;
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

    return sprintf($format, Yii::$app->formatter->asDecimal($rightRatioValue, 1));
  }

  /**
   * @param array $row
   * @param string $format
   * @param bool $useFormatter Использовать asPercent для форматирования процентов
   * @return string
   */
  public function getCPARatio(array $row, $format = '1:%d', $useFormatter = false)
  {
    $rightPart = $this->getCountCpaOns($row);
    $accepted = $this->getAcceptedValue($row, self::CPA, true);

    $rightRatioValue = $rightPart == 0 ? 0 : round($accepted / $rightPart, 1);

    return sprintf($format, Yii::$app->formatter->asDecimal($rightRatioValue, 1));
  }

  /**
   * @param array $row
   * @param $currency
   * @return float|int
   */
  public function getCPR(array $row, $currency)
  {
    $profit = ArrayHelper::getValue($row, 'sold_partner_price_' . $currency, 0);
    $countSold = (int)ArrayHelper::getValue($row, 'partner_visible_count_sold', 0);

    if ($countSold == 0) {
      return 0;
    }

    return $profit / $countSold;
  }

  /**
   * @param array $row
   * @param $currency
   * @return float|int
   */
  public function getAvgCPA(array $row, $currency)
  {
    $profit = ArrayHelper::getValue($row, 'sold_partner_profit_' . $currency, 0);
    $countSold = (int)ArrayHelper::getValue($row, 'count_sold', 0);

    if ($countSold == 0) {
      return 0;
    }

    return $profit / $countSold;
  }

  /**
   * @param array $row
   * @param bool $useFormatter Использовать asPercent для форматирования процентов
   * @return string
   */
  public function getRevshareCr(array $row, $useFormatter = false)
  {
    $accepted = $this->getAcceptedValue($row, self::REVSHARE, true);

    $crValue = $accepted > 0 // исключаем возможность деления на ноль
      ? round(ArrayHelper::getValue($row, 'count_ons', 0) / $accepted * 100, 3)
      : 0
    ;

    if ($useFormatter) $crValue = Yii::$app->formatter->asPercent($crValue / 100, 2);

    return $crValue;
  }

  /**
   * @param array $row
   * @param bool $visibleToPartnerOnly вернуть только видимые партнеру
   * @param bool $useFormatter Использовать asPercent для форматирования процентов
   * @return string
   */
  public function getCpaCr(array $row, $visibleToPartnerOnly = false, $useFormatter = false)
  {
    $accepted = $this->getAcceptedValue($row, self::CPA, true);

    $crValue = $accepted > 0 // исключаем возможность деления на ноль
      ? round($this->getCountCpaOns($row, $visibleToPartnerOnly) / $accepted * 100, 3)
      : 0
    ;

    if ($useFormatter) $crValue = Yii::$app->formatter->asPercent($crValue / 100, 2);

    return $crValue;
  }

  /**
   * CR% в зависимости от проданный подписок
   * @param array $row
   * @param bool $useFormatter Использовать asPercent для форматирования процентов
   * @return string
   */
  public function getCpaCrSold(array $row, $useFormatter = false)
  {
    $accepted = $this->getAcceptedValue($row, self::CPA, true);

    $crValue = $accepted > 0 // исключаем возможность деления на ноль
      ? round(ArrayHelper::getValue($row, 'count_sold', 0) / $accepted * 100, 3)
      : 0
    ;

    if ($useFormatter) $crValue = Yii::$app->formatter->asPercent($crValue / 100, 2);

    return $crValue;
  }

  /**
   * CR% в зависимости от проданный подписок итого
   * @param bool $useFormatter Использовать asPercent для форматирования процентов
   * @return string
   */
  public function getResultCpaCrSold($useFormatter = false)
  {
    $accepted = $this->getResultAccepted(self::CPA, true);

    $crValue = $accepted > 0 // исключаем возможность деления на ноль
      ? round($this->getResultValue('count_sold') / $accepted * 100, 3)
      : 0
    ;

    if ($useFormatter) $crValue = Yii::$app->formatter->asPercent($crValue / 100, 2);

    return $crValue;
  }

  /**
   * @param array $row
   * @param string $format
   * @return string
   */
  public function getOnetimeRatio(array $row, $format = '1:%d')
  {
    $rightPart = (int) ArrayHelper::getValue($row, 'count_onetime', 0);
    $accepted = $this->getAcceptedValue($row, self::ONETIME, true);

    $rightRatioValue = $rightPart == 0 ? 0 : $accepted / $rightPart;

    return sprintf($format, Yii::$app->formatter->asDecimal($rightRatioValue, 1));
  }

  /**
   * @param array $row
   * @param bool $visibleToPartnerOnly вернуть только видимые партнеру
   * @param bool $useFormatter Использовать asPercent для форматирования процентов
   * @return string
   */
  public function getOnetimeCr(array $row, $visibleToPartnerOnly = false, $useFormatter = false)
  {
    $accepted = $this->getAcceptedValue($row, self::ONETIME, true);

    $countOnetime = $visibleToPartnerOnly
      ? ArrayHelper::getValue($row, 'partner_visible_count_onetime', 0)
      : ArrayHelper::getValue($row, 'count_onetime', 0);

    $crValue = $accepted > 0 // исключаем возможность деления на ноль
      ? round($countOnetime / $accepted * 100, 3)
      : 0
    ;

    if ($useFormatter) $crValue = Yii::$app->formatter->asPercent($crValue / 100, 2);

    return $crValue;
  }

  /**
   * @param $item
   * @return float
   */
  public function getComplainsRate($item)
  {
    $cr = new Cr();
    $cr->convertionsCount = (int)ArrayHelper::getValue($item, 'count_calls')
      + (int)ArrayHelper::getValue($item, 'count_complains')
      + (int)ArrayHelper::getValue($item, 'count_calls_mno');
    $cr->fullCount = (int) ArrayHelper::getValue($item, 'count_ons', 0)
      + $this->getCountCpaOns($item)
      + (int) ArrayHelper::getValue($item, 'count_onetime', 0);
    return $cr->getRate();
  }

  /**
   * @param array $row
   * @return float|int
   */
  public function getChargeRatio(array $row)
  {
    $ons = (int) ArrayHelper::getValue($row, 'count_ons', 0);

    if ($ons == 0) {
      return 0;
    }

    $chargesOnDate = (int) ArrayHelper::getValue($row, 'count_rebills_date_by_date', 0);

    return $chargesOnDate / $ons;
  }

  /**
   * @param $item
   * @param string $currency
   * @return float
   */
  public function getEcpc($item, $currency)
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getPartnerTotalProfit($item, $currency);
    $cr->fullCount = (int) ArrayHelper::getValue($item, 'count_hits', 0);
    return $cr->getRate();
  }

  /**
   * eCPC Revshare
   * @param $item
   * @param $currency
   * @return float
   */
  public function getEcpcRevshare($item, $currency)
  {
    $cr = new Cr();
    $cr->convertionsCount = (float) ArrayHelper::getValue($item, 'sum_profit_' . $currency, 0);
    $cr->fullCount = (int) $this->getAcceptedValue($item, self::REVSHARE);
    return $cr->getRate();
  }

  /**
   * Среднее значение eCPC Revshare
   * @param $field
   * @return float
   */
  public function getResultEcpcRevshare($field)
  {
    $currency = str_replace('revshare_ecpc_', '', $field);

    $sum = 0;
    $hits = 0;
    foreach ($this->_statData as $row) {
      $sum += (float) ArrayHelper::getValue($row, 'sum_profit_' . $currency, 0);
      $hits += (int) $this->getAcceptedValue($row, self::REVSHARE);
    }
    $cr = new Cr();
    $cr->convertionsCount = $sum;
    $cr->fullCount = $hits;
    return $cr->getRate();
  }

  /**
   * eCPC CPA
   * @param $item
   * @param $currency
   * @return float
   */
  public function getEcpcCpa($item, $currency)
  {
    $cr = new Cr();
    $cr->convertionsCount = (float) ArrayHelper::getValue($item, 'sold_partner_profit_' . $currency, 0);
    $cr->fullCount = (int) $this->getAcceptedValue($item, self::CPA);
    return $cr->getRate();
  }

  /**
   * Среднее значение eCPC CPA
   * @param $field
   * @return float
   */
  public function getResultEcpcCpa($field)
  {
    $currency = str_replace('cpa_ecpc_', '', $field);

    $sum = 0;
    $hits = 0;
    foreach ($this->_statData as $row) {
      $sum += (float) ArrayHelper::getValue($row, 'sold_partner_profit_' . $currency, 0);
      $hits += (int) $this->getAcceptedValue($row, self::CPA);
    }
    $cr = new Cr();
    $cr->convertionsCount = $sum;
    $cr->fullCount = $hits;
    return $cr->getRate();
  }

  /**
   * eCPC Onetime
   * @param $item
   * @param $currency
   * @return float
   */
  public function getEcpcOnetime($item, $currency)
  {
    $cr = new Cr();
    $cr->convertionsCount = (float) ArrayHelper::getValue($item, 'onetime_profit_' . $currency, 0);
    $cr->fullCount = (int) $this->getAcceptedValue($item, self::ONETIME);
    return $cr->getRate();
  }

  /**
   * Среднее значение eCPC Onetime
   * @param $field
   * @return float
   */
  public function getResultEcpcOnetime($field)
  {
    $currency = str_replace('onetime_ecpc_', '', $field);

    $sum = 0;
    $hits = 0;
    foreach ($this->_statData as $row) {
      $sum += (float) ArrayHelper::getValue($row, 'onetime_profit_' . $currency, 0);
      $hits += (int) $this->getAcceptedValue($row, self::ONETIME);
    }
    $cr = new Cr();
    $cr->convertionsCount = $sum;
    $cr->fullCount = $hits;
    return $cr->getRate();
  }

  /**
   * @param $item
   * @param string $currency
   * @return float
   */
  public function getEcp($item, $currency)
  {
    $cr = new Cr();
    $cr->convertionsCount = ArrayHelper::getValue($item, 'sold_price_' . $currency, 0);
    $cr->fullCount = (int) ArrayHelper::getValue($item, 'count_hits', 0);
    return $cr->getRate();
  }

  public function getVisibleSubscriptions(array $row)
  {
    return ArrayHelper::getValue($row, 'sold_visible_subscriptions', 0);
  }

  /**
   * Профит реселлера (NET) ИК
   * @param $item
   * @param $currency
   * @return float
   */
  public function getOnetimeResellerNetProfit($item, $currency)
  {
    $resProfit = (float) ArrayHelper::getValue($item, 'onetime_reseller_profit_' . $currency, 0);
    $partnerProfit = (float) ArrayHelper::getValue($item, 'onetime_profit_' . $currency, 0);
    return $resProfit - $partnerProfit;
  }

  /**
   * Профит реселлера (NET) Revshare
   * @param $item
   * @param $currency
   * @return float
   */
  public function getRevshareResellerNetProfit($item, $currency)
  {
    $resProfit = (float) ArrayHelper::getValue($item, 'sum_reseller_profit_' . $currency, 0);
    $partnerProfit = (float) ArrayHelper::getValue($item, 'sum_profit_' . $currency, 0);
    return $resProfit - $partnerProfit;
  }

  /**
   * Профит реселлера (NET) Cpa
   * @param $item
   * @param $currency
   * @return float
   */
  public function getCpaResellerNetProfit($item, $currency)
  {
    $resProfit = (float) ArrayHelper::getValue($item, 'sold_cpa_profit_' . $currency, 0);
    $partnerProfit = (float) ArrayHelper::getValue($item, 'sold_partner_profit_' . $currency, 0);
    return $resProfit - $partnerProfit;
  }

  /**
   * @param StatisticQuery $query
   * @return bool
   */
  private function isInvestorQuery($query)
  {
    return in_array($query->getId(), [self::INVESTOR_STAT, self::INVESTOR_SCOPE_OFFS], true);
  }

  /**
   * canViewResellerProfit
   * @param array $row
   * @param null $currency
   * @return float
   */
  public function getTotalProfitReseller(array $row, $currency = null)
  {
    if (!$currency) $currency = $this->getUserCurrency();
    return $this->getResellerTotalProfit($row, $currency);
  }
}
