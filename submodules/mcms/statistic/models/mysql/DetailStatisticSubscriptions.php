<?php

namespace mcms\statistic\models\mysql;

use mcms\statistic\components\AbstractDetailStatistic;
use mcms\statistic\Module;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\db\Query;
use mcms\statistic\components\StatisticQuery;
use yii\helpers\ArrayHelper;

class DetailStatisticSubscriptions extends AbstractDetailStatistic
{

  const GROUP_NAME = 'subscriptions';

  public $rebillDateFrom;
  public $rebillDateTo;

  public $subscribeDateFrom;
  public $subscribeDateTo;

  public $unsubscribeDateFrom;
  public $unsubscribeDateTo;

  public $debitSumFrom;
  public $debitSumTo;

  public $rebillCountFrom;
  public $rebillCountTo;

  public $hit_id;
  public $phone_number;
  public $landings;
  public $sources;
  public $operators;
  public $platforms;
  public $streams;
  public $providers;
  public $countries;
  public $users;
  public $landing_pay_types;
  public $is_visible_to_partner;
  public $profit_type;
  public $referer;
  /**
   * @var  bool Если включено, то будет сгруппировано по номеру телефона и
   * отображено кол-во пдп по этому номеру в новом столбце.
   */
  public $groupByPhone;


  public function init()
  {
    parent::init();
    $this->group = self::GROUP_NAME;

    // С формы может прийти пустая строка, из-за чего происходит ошибка
    if (empty($this->streams)) $this->streams = null;
  }

  public static function tableName()
  {
    return 'search_subscriptions';
  }

  public function rules()
  {
    return array_merge([
      [
        [
          'rebillDateFrom',
          'rebillDateTo',
          'subscribeDateFrom',
          'subscribeDateTo',
          'unsubscribeDateFrom',
          'unsubscribeDateTo'
        ],
        'date'
      ],
      [['debitSumFrom', 'debitSumTo', 'rebillCountFrom', 'rebillCountTo', 'hit_id', 'phone_number', 'is_visible_to_partner', 'profit_type'], 'integer'],
      [['landings', 'sources', 'operators', 'platforms', 'streams', 'providers', 'countries', 'users', 'landing_pay_types', 'groupByPhone', 'referer'], 'safe']
    ], parent::rules());
  }

  /**
   * Перевод для колонок грида
   * @return array
   */
  public function gridColumnLabels()
  {
    return [
      'hit_id' => Yii::_t('statistic.statistic.detail-transition_id'),
      'phone_number' => Yii::_t('statistic.statistic.detail-phone_number'),
      'ip' => Yii::_t('statistic.statistic.detail-ip'),
      'stream' => Yii::_t('statistic.statistic.detail-stream-name'),
      'url' => Yii::_t('statistic.statistic.detail-url'),
      'source' => Yii::_t('statistic.statistic.sources'),
      'subscribed_at' => Yii::_t('statistic.statistic.detail-subscribed_at'),
      'unsubscribed_at' => Yii::_t('statistic.statistic.detail-unsubscribed_at'),
      'last_rebill_at' => Yii::_t('statistic.statistic.detail-last_rebill_at'),
      'last_update_at' => Yii::_t('statistic.statistic.detail-last_update_at'),
      'rebill_count' => Yii::_t('statistic.statistic.detail-rebill_count'),
      'sum_profit' => Yii::_t('statistic.statistic.detail-sum_profit', [':userCurrency' => strtoupper($this->getUserCurrency())]),
      'platforms' => Yii::_t('statistic.statistic.platforms'),
      'landings' => Yii::_t('statistic.statistic.landings'),
      'operators' => Yii::_t('statistic.statistic.operators'),
      'countries' => Yii::_t('statistic.statistic.countries'),
      'landingPayType' => Yii::_t('statistic.statistic.landing_pay_type_name'),
      'referrer' => Yii::_t('statistic.statistic.referrer'),
      'userAgent' => Yii::_t('statistic.statistic.userAgent'),
      'cid' => Yii::_t('statistic.statistic.cid'),
      'subid1' => Yii::_t('statistic.statistic.subid1'),
      'subid2' => Yii::_t('statistic.statistic.subid2'),
      'sum_profit_rub' => Yii::_t('statistic.statistic.sum_profit', ['currency' => 'RUB']),
      'sum_profit_eur' => Yii::_t('statistic.statistic.sum_profit', ['currency' => 'EUR']),
      'sum_profit_usd' => Yii::_t('statistic.statistic.sum_profit', ['currency' => 'USD']),
      'sum_real_profit_rub' => Yii::_t('statistic.statistic.sum_real_profit', ['currency' => 'RUB']),
      'sum_real_profit_eur' => Yii::_t('statistic.statistic.sum_real_profit', ['currency' => 'EUR']),
      'sum_real_profit_usd' => Yii::_t('statistic.statistic.sum_real_profit', ['currency' => 'USD']),
      'sum_reseller_profit_rub' => Yii::_t('statistic.statistic.sum_reseller_profit', ['currency' => 'RUB']),
      'sum_reseller_profit_eur' => Yii::_t('statistic.statistic.sum_reseller_profit', ['currency' => 'EUR']),
      'sum_reseller_profit_usd' => Yii::_t('statistic.statistic.sum_reseller_profit', ['currency' => 'USD']),
      'email' => Yii::_t('statistic.statistic.email'),
      'is_visible_to_partner' => Yii::_t('statistic.statistic.sold_is_visible_to_partner'),
      'profit_type' => Yii::_t('statistic.statistic.profit_type'),
      'is_sold' => Yii::_t('statistic.statistic.is_sold'),
      'countPhones' => Yii::_t('statistic.statistic.countPhones'),
    ];
  }

  /**
   * TRICKY нужно перенести в getGridColumns
   * @return array
   */
  public function getExportAttributeLabels()
  {
    return [
      'hit_id' => Yii::_t('statistic.statistic.detail-transition_id'),
      'phone' => Yii::_t('statistic.statistic.detail-phone_number'),
      'ip' => Yii::_t('statistic.statistic.detail-ip'),
      'stream_name' => Yii::_t('statistic.statistic.detail-stream-name'),
      'url' => Yii::_t('statistic.statistic.detail-url'),
      'source_name' => Yii::_t('statistic.statistic.sources'),
      'subscribed_at' => Yii::_t('statistic.statistic.detail-subscribed_at'),
      'unsubscribed_at' => Yii::_t('statistic.statistic.detail-unsubscribed_at'),
      'last_rebill_at' => Yii::_t('statistic.statistic.detail-last_rebill_at'),
      'last_update_at' => Yii::_t('statistic.statistic.detail-last_update_at'),
      'count_rebills' => Yii::_t('statistic.statistic.detail-rebill_count'),
      'sum_profit' => Yii::_t('statistic.statistic.detail-sum_profit', [':userCurrency' => strtoupper($this->getUserCurrency())]),
      'platform_name' => Yii::_t('statistic.statistic.platforms'),
      'landing_name' => Yii::_t('statistic.statistic.landings'),
      'operator_name' => Yii::_t('statistic.statistic.operators'),
      'country_name' => Yii::_t('statistic.statistic.countries'),
      'landingPayType' => Yii::_t('statistic.statistic.landing_pay_type_name'),
      'referrer' => Yii::_t('statistic.statistic.referrer'),
      'userAgent' => Yii::_t('statistic.statistic.userAgent'),
      'cid' => Yii::_t('statistic.statistic.cid'),
      'sum_profit_rub' => Yii::_t('statistic.statistic.sum_profit', ['currency' => 'RUB']),
      'sum_profit_eur' => Yii::_t('statistic.statistic.sum_profit', ['currency' => 'EUR']),
      'sum_profit_usd' => Yii::_t('statistic.statistic.sum_profit', ['currency' => 'USD']),
      'sum_real_profit_rub' => Yii::_t('statistic.statistic.sum_real_profit', ['currency' => 'RUB']),
      'sum_real_profit_eur' => Yii::_t('statistic.statistic.sum_real_profit', ['currency' => 'EUR']),
      'sum_real_profit_usd' => Yii::_t('statistic.statistic.sum_real_profit', ['currency' => 'USD']),
      'sum_reseller_profit_rub' => Yii::_t('statistic.statistic.sum_reseller_profit', ['currency' => 'RUB']),
      'sum_reseller_profit_eur' => Yii::_t('statistic.statistic.sum_reseller_profit', ['currency' => 'EUR']),
      'sum_reseller_profit_usd' => Yii::_t('statistic.statistic.sum_reseller_profit', ['currency' => 'USD']),
      'email' => Yii::_t('statistic.statistic.email'),
      'is_visible_to_partner' => Yii::_t('statistic.statistic.sold_is_visible_to_partner'),
      'is_sold' => Yii::_t('statistic.statistic.is_sold'),
      'countPhones' => Yii::_t('statistic.statistic.countPhones'),
    ];
  }

  public function handleFilters(Query &$query)
  {
    /* @var StatisticQuery $query */

    // TRICKY читай таск MCMS-1555
    // При сгруппированной стате фильтры сверху фильтруют по time_on.
    // При обычной стате фильтры сверху фильтруют по last_time.
    // И при клике на ссылку в сгруппированной мы должны выставить фильтры по дате подписки и фильтры по last_time
    if ($this->start_date) {
      $query->andFilterWhere([
        '>=',
        $this->groupByPhone ? 'time_on' : 'last_time',
        strtotime($this->start_date)
      ]);
    }

    if ($this->end_date) {
      $query->andFilterWhere([
        '<=',
        $this->groupByPhone ? 'time_on' : 'last_time',
        strtotime($this->end_date . ' tomorrow') - 1
      ]);
    }

    $query->andFilterWhere(['like', 'hp.referer' , $this->referer]);

    /** @var Module $module */
    $module = Yii::$app->getModule('statistic');
    $canViewFullTime = $module->canViewFullTimeStatistic();
    $minTime = strtotime('-3 months');

    if ($this->rebillDateFrom) {
      $time = strtotime($this->rebillDateFrom);
      $canViewFullTime || $time < $minTime && $time = $minTime;

      $query->andFilterWhere(['>=', 'time_rebill', $time]);
    }

    if ($this->rebillDateTo) {
      $query->andFilterWhere(['<=', 'time_rebill', strtotime($this->rebillDateTo . ' tomorrow') - 1]);
    }

    if ($this->subscribeDateFrom) {
      $time = strtotime($this->subscribeDateFrom);
      $canViewFullTime || $time < $minTime && $time = $minTime;

      $query->andFilterWhere(['>=', 'time_on', $time]);
    }

    if ($this->subscribeDateTo) {
      $query->andFilterWhere(['<=', 'time_on', strtotime($this->subscribeDateTo . ' tomorrow') - 1]);
    }

    if ($this->unsubscribeDateFrom) {
      $time = strtotime($this->unsubscribeDateFrom);
      $canViewFullTime || $time < $minTime && $time = $minTime;

      $query->andFilterWhere(['>=', 'time_off', $time]);
    }

    if ($this->unsubscribeDateTo) {
      $query->andFilterWhere(['<=', 'time_off', strtotime($this->unsubscribeDateTo . ' tomorrow') - 1]);
    }

    if ($this->rebillCountFrom) {
      $query->andFilterWhere(['>=', 'count_rebills', $this->rebillCountFrom]);
    }

    if ($this->rebillCountTo) {
      $query->andFilterWhere(['<=', 'count_rebills', $this->rebillCountTo]);
    }

    if ($this->debitSumFrom) {
      $query->andFilterWhere(['>=', 'sum_profit_' . $this->currency, $this->debitSumFrom]);
    }

    if ($this->debitSumTo) {
      $query->andFilterWhere(['<=', 'sum_profit_' . $this->currency, $this->debitSumTo]);
    }

    if ($this->canFilterByLandings()) {
      $query->andFilterWhere(['st.landing_id' => $this->landings]);
    }

    if ($this->canFilterByOperators()) {
      $query->andFilterWhere(['st.operator_id' => $this->operators]);
    }

    if ($this->canFilterBySources()) {
      $query->andFilterWhere($query->getId() === self::SOLD_STAT
        ? ['or',
          ['st.source_id' => $this->sources],
          ['sold.source_id' => $this->sources]]
        : ['st.source_id' => $this->sources]);
    }

    if ($this->canFilterByPlatform()) {
      $query->andFilterWhere(['st.platform_id' => $this->platforms]);
    }

    if ($this->canFilterByStreams()) {
      $query->andFilterWhere($query->getId() === self::SOLD_STAT
        ? ['or',
          ['st.stream_id' => $this->streams],
          ['sold.stream_id' => $this->streams]]
        : ['st.stream_id' => $this->streams]);
    }

    if ($this->canFilterByCountries()) {
      $query->andFilterWhere(['st.country_id' => $this->countries]);
    }

    if ($this->canFilterByUsers()) {
      $query->andFilterWhere($query->getId() === self::SOLD_STAT
        ? ['or',
          ['st.user_id' => $this->users],
          ['sold.user_id' => $this->users]
        ]
        : ['st.user_id' => $this->users]);
      Yii::$app->user->identity->filterUsersItems($query, 'st', 'user_id');
    }

    if ($this->canFilterByProviders()) {
      $query->andFilterWhere(['st.provider_id' => $this->providers]);
    }

    if ($this->canFilterByLandingPayTypes()) {
      $query->andFilterWhere(['st.landing_pay_type_id' => $this->landing_pay_types]);
    }

    $query->andFilterWhere(['st.hit_id' => $this->hit_id]);

    $query->andFilterWhere(['like', 'st.phone' , $this->phone_number]);
    $query->andWhere(['st.is_fake' => 0]);

    if ($this->groupByPhone) {
      $query->andWhere(['<>', 'st.phone', '']);
    }

    if ($this->canFilterByCurrency()) {
      $query->andWhere(['st.currency_id' => $this->allCurrencies[$this->currency]]);
    }
    $query->andFilterWhere(['is_visible_to_partner' => $this->is_visible_to_partner]);

    if ($this->profit_type === self::REVSHARE) {
      $query->andWhere('sold.id IS NULL AND st.is_cpa = 0');
    }

    if ($this->profit_type === self::REJECTED) {
      $query->andWhere('sold.id IS NULL AND st.is_cpa = 1');
    }

    if ($this->profit_type === self::SOLD) {
      $query->andWhere('sold.id IS NOT NULL');
    }
  }

  /*
   * Формирует запрос для getStatisticGroupQueries и getStatisticGroupQueriesPhoneGroup
   * @return StatisticQuery
   */
  protected function getCommonStatisticGroupQuery()
  {
    $this->handleOpenCloseFilters();
    return (new StatisticQuery())
      ->select([
        'hit_id' => 'st.hit_id',
        'count_rebills' => 'count_rebills',
        'sum_real_profit_rub' => 'sum_real_profit_rub',
        'sum_real_profit_eur' => 'sum_real_profit_eur',
        'sum_real_profit_usd' => 'sum_real_profit_usd',
        'sum_reseller_profit_rub' => 'sum_reseller_profit_rub',
        'sum_reseller_profit_eur' => 'sum_reseller_profit_eur',
        'sum_reseller_profit_usd' => 'sum_reseller_profit_usd',
        'sum_profit_rub' => 'sum_profit_rub',
        'sum_profit_eur' => 'sum_profit_eur',
        'sum_profit_usd' => 'sum_profit_usd',
        'phone' => 'phone',
        'subscribed_at' => 'time_on',
        'unsubscribed_at' => 'time_off',
        'last_rebill_at' => 'time_rebill',
        'last_update_at' => 'last_time',
        'ip' => 'st.ip',
        'user_id' => 'st.user_id',
        'email' => 'u.email', // todo вынести в addQueryJoins()?
        'is_visible_to_partner' => 'sold.is_visible_to_partner',
        'is_sold' => 'IF(sold.id IS NULL, 0, 1)',
      ])->from(['st' => 'search_subscriptions']);
  }

  /**
   * Возвращает два сформированных запроса [query, queryCount] для сгруппированной статистики
   * @return StatisticQuery[]
   */
  protected function getStatisticGroupQuery()
  {
    $query = $this->getCommonStatisticGroupQuery();

    $queryCount = clone $query;
    $subscriptionsSearchQuery = $this->addQueryJoins($query);
    $subscriptionsSearchQueryCount = $this->addFilterJoins($queryCount);

    // tricky: сделано не через датапровайдер для поддержки выгрузки в CSV (сортировка должна быть в запросе)
    if (isset($this->requestData['sort'])) {
      $sortParam = $this->requestData['sort'];
      $order = strncmp($sortParam, '-', 1) === 0 ? SORT_DESC : SORT_ASC;
      $sortAttr = $order === SORT_DESC ? substr($sortParam, 1) : $sortParam;
      if (in_array($sortAttr, $this->getSortAttributes())) {
        $query->orderBy([$sortAttr => $order]);
      }
    } else {
      $query->orderBy([
        'last_time' => SORT_DESC
      ]);
    }

    $this->handleFilters($subscriptionsSearchQuery);
    $this->handleFilters($subscriptionsSearchQueryCount);

    $subscriptionsSearchQueryCount->select(new Expression('1'));
    $subscriptionsSearchQueryCount = (new Query)->select(['COUNT(1)'])
      ->from(['c' => $subscriptionsSearchQueryCount]);

    return [$subscriptionsSearchQuery, $subscriptionsSearchQueryCount];
  }

  /**
   * Возвращает два сформированных запроса [query, queryCount] для сгруппированной статистики с группировкой по телефонам
   * TODO Скорее всего не работает фильтрация по полям из hit_params, например по user_agent
   * @return Query[]
   */
  protected function getStatisticGroupQueriesPhoneGroup()
  {
    $query = $this->getCommonStatisticGroupQuery();
    $query->addSelect(['countPhones' => 'COUNT(1)'])->groupBy(['st.phone']);

    $queryCount = clone $query;
    $subscriptionsSearchQuery = $this->addQueryJoins($query);
    $subscriptionsSearchQueryCount = $this->addFilterJoins($queryCount);

    // tricky: сделано не через датапровайдер для поддержки выгрузки в CSV (сортировка должна быть в запросе)
    if (isset($this->requestData['sort'])) {
      $sortParam = $this->requestData['sort'];
      $order = strncmp($sortParam, '-', 1) === 0 ? SORT_DESC : SORT_ASC;
      $sortAttr = $order === SORT_DESC ? substr($sortParam, 1) : $sortParam;
      if (in_array($sortAttr, $this->getSortAttributes())) {
        $subscriptionsSearchQuery->orderBy([$sortAttr => $order]);
      }
    } else {
      $subscriptionsSearchQuery->orderBy(['countPhones' => SORT_DESC]);
    }

    $this->handleFilters($subscriptionsSearchQuery);
    $this->handleFilters($subscriptionsSearchQueryCount);

    $subscriptionsSearchQueryCount->select(new Expression('1'));
    $subscriptionsSearchQueryCount = (new StatisticQuery)->select(['COUNT(1)'])
      ->from(['c' => $subscriptionsSearchQueryCount]);

    return [$subscriptionsSearchQuery, $subscriptionsSearchQueryCount];
  }

  /**
   * Возвращает два сформированных запроса [query, queryCount] для сгруппированной статистики
   * @return StatisticQuery[]
   */
  public function getStatisticGroupQueries()
  {
    return $this->groupByPhone
      ? $this->getStatisticGroupQueriesPhoneGroup()
      : $this->getStatisticGroupQuery();
  }

  public function getStatisticGroup()
  {
    list($subscriptionsSearchQuery, $subscriptionsSearchQueryCount) = $this->getStatisticGroupQueries();

    $dataProvider = new ActiveDataProvider([
      'query' => $subscriptionsSearchQuery,
      'totalCount' => $subscriptionsSearchQueryCount->scalar(),
      'sort' => [
        'attributes' => $this->getSortAttributes()
      ],
    ]);

    return $dataProvider;
  }

  /**
   * Атрибуты для сортировки
   * @return array
   */
  private function getSortAttributes()
  {
    return [
      'hit_id',
      'count_rebills',
      'sum_real_profit_rub',
      'sum_real_profit_eur',
      'sum_real_profit_usd',
      'sum_reseller_profit_rub',
      'sum_reseller_profit_eur',
      'sum_reseller_profit_usd',
      'sum_profit_rub',
      'sum_profit_eur',
      'sum_profit_usd',
      'subscribed_at',
      'unsubscribed_at',
      'last_time',
      'last_rebill_at',
      'last_update_at',
      'country.name' => 'country_name',
      'source.name' => 'source_name',
      'operator.name' => 'operator_name',
      'stream.name' => 'stream_name',
      'platform.name' => 'platform_name',
      'l.name' => 'landing_name',
      'ip',
      'phone',
      'is_visible_to_partner',
      'is_sold',
      'email',
      'countPhones',
    ];
  }

  function findOne($recordId)
  {
    $query = (new StatisticQuery())
      ->select([
        'hit_id' => 'st.hit_id',
        'count_rebills' => 'st.count_rebills',
        'sum_real_profit_rub' => 'st.sum_real_profit_rub',
        'sum_real_profit_eur' => 'st.sum_real_profit_eur',
        'sum_real_profit_usd' => 'st.sum_real_profit_usd',
        'sum_reseller_profit_rub' => 'st.sum_reseller_profit_rub',
        'sum_reseller_profit_eur' => 'st.sum_reseller_profit_eur',
        'sum_reseller_profit_usd' => 'st.sum_reseller_profit_usd',
        'sum_profit_rub' => 'st.sum_profit_rub',
        'sum_profit_eur' => 'st.sum_profit_eur',
        'sum_profit_usd' => 'st.sum_profit_usd',
        'phone' => 'st.phone',
        'subscribed_at' => 'st.time_on',
        'unsubscribed_at' => 'st.time_off',
        'last_rebill_at' => 'st.time_rebill',
        'last_update_at' => 'st.last_time',
        'ip' => 'st.ip',
      ])
      ->from(['st' => 'search_subscriptions'])
      ->where(['st.hit_id' => $recordId]);

    $query = $this->addQueryJoins($query);

    return $query->one();
  }

  /**
   * @param StatisticQuery $query
   * @return StatisticQuery
   */
  function addFilterJoins($query)
  {
    if (!$query->hitParamsJoined) {
      $query->innerJoin('hit_params hp', 'hp.hit_id = st.hit_id');
      $query->hitParamsJoined = true;
    }
    //TRICKY для подсчета COUNT джойним sold_subscriptions только при фильтрации
    //в addQueryJoins уже есть этот join поэтому добавляем допольнительную проверку
    if ((!$this->isFilterPropEmpty($this->is_visible_to_partner) || !$this->isFilterPropEmpty($this->profit_type)
        || $this->isFilteredBySoldFields())
      && $this->addJoinGroupTable('sold_subscriptions', $query)) {
      $query->setId(self::SOLD_STAT);
      $query->join($this->profit_type === self::SOLD ? 'inner join' : 'left join', 'sold_subscriptions sold', 'sold.hit_id = st.hit_id');
    }

    return $query;
  }

  /**
   * @param Query $query
   * @return Query
   */
  function addQueryJoins(Query $query)
  {
    /* @var $query StatisticQuery*/
    if (!$query->hitParamsJoined) {
      $query->innerJoin('hit_params hp', 'hp.hit_id = st.hit_id');
      $query->hitParamsJoined = true;
    }
    $query->addSelect(['hp.referer AS referrer', 'hp.user_agent AS userAgent', 'hp.subid1 AS subid1', 'hp.subid2 AS subid2', 'hp.get_params AS getParams']);
    $query->setId(self::SOLD_STAT);
    $query->leftJoin( 'sold_subscriptions sold', 'sold.hit_id = st.hit_id');

    $query = $this->addFilterJoins($query);

    return parent::addQueryJoins($query);
  }

  /**
   * Доступны ли инвесторские фильтры и Выбран ли фильтр по одному из полей партнера: источник, поток, партнер.
   * @return bool
   */
  protected function isFilteredBySoldFields()
  {
    return $this->sources || $this->streams || $this->users;
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
      'hit_id',
      'phone_number',
      'users',
      'landing_pay_types',
      'rebillDateFrom',
      'rebillDateTo',
      'subscribeDateFrom',
      'subscribeDateTo',
      'unsubscribeDateFrom',
      'unsubscribeDateTo',
      'debitSumFrom',
      'debitSumTo',
      'rebillCountFrom',
      'rebillCountTo',
      'is_visible_to_partner',
      'is_sold',
    ];
  }

  public function canViewCountRebills()
  {
    return $this->checkPermission('StatisticViewCountRebills');
  }

  public function canViewSumProfit()
  {
    return $this->checkPermission('StatisticViewSumProfit');
  }



  function getAdminProfit(array $gridRow, $currency)
  {
    return ArrayHelper::getValue($gridRow, 'sum_real_profit_' . $currency);
  }

  function getResellerProfit(array $gridRow, $currency)
  {
    return ArrayHelper::getValue($gridRow, 'sum_reseller_profit_' . $currency);
  }

  function getPartnerProfit(array $gridRow, $currency)
  {
    return ArrayHelper::getValue($gridRow, 'sum_profit_' . $currency);
  }

  public function attributeLabels()
  {
    return [
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
      'hit_id' => Yii::_t('statistic.statistic.detail-transition_id'),
      'phone_number' => Yii::_t('statistic.statistic.detail-phone_number'),
      'groupByPhone' => Yii::_t('statistic.statistic.groupByPhone'),
      'is_visible_to_partner' => Yii::_t('statistic.statistic.sold_is_visible_to_partner'),
      'is_sold' => Yii::_t('statistic.statistic.is_sold'),
      'profit_type' => Yii::_t('statistic.statistic.profit_type'),
    ];
  }

  /**
   * @inheritdoc
   */
  protected function isCpaVisible($gridRow){}

  /**
   * Проверяем выбран ли фильтр или нет. Например если значение фильтра 0, то вернёт true.
   * @param $value
   * @return bool
   */
  protected function isFilterPropEmpty($value)
  {
    /** проверку спёр отсюда @see \yii\db\Query::isEmpty() */
    return $value === '' || $value === [] || $value === null || (is_string($value) && trim($value) === '');
  }
}
