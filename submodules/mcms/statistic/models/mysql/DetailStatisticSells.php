<?php

namespace mcms\statistic\models\mysql;

use mcms\statistic\components\AbstractDetailStatistic;
use mcms\statistic\components\StatisticQuery;
use mcms\statistic\Module;
use UnexpectedValueException;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class DetailStatisticSells extends AbstractDetailStatistic
{

  const GROUP_NAME = 'sells';

  public $sellSumFrom;
  public $sellSumTo;

  public $landings;
  public $sources;
  public $operators;
  public $platforms;
  public $streams;
  public $providers;
  public $countries;
  public $users;
  public $landing_pay_types;
  public $hit_id;
  public $phone_number;
  public $user_id;
  public $email;
  public $is_visible_to_partner;
  public $referer;
  /**
   * @var  bool Если включено, то будет сгруппировано по номеру телефона и
   * отображено кол-во пдп по этому номеру в новом столбце.
   */
  public $groupByPhone;

  private $_userData;

  public function init()
  {
    parent::init();

    // С формы может прийти пустая строка, из-за чего происходит ошибка
    if (empty($this->streams)) $this->streams = null;
  }

  public function rules()
  {
    return array_merge(parent::rules(), [
      [['sellSumFrom', 'sellSumTo', 'hit_id', 'phone_number', 'is_visible_to_partner'], 'integer'],
      ['sellSumFrom', 'validateSellSum'],
      [['landings', 'sources', 'operators', 'platforms', 'streams', 'providers', 'countries', 'users', 'landing_pay_types', 'groupByPhone', 'referer'], 'safe']

    ]);
  }

  public function validateSellSum($attribute)
  {
    if ($this->sellSumTo > $this->sellSumFrom) {
      $this->addError($attribute, Yii::_t('statistic.incorrect_period'));
    }
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
      'email' => Yii::_t('statistic.statistic.email'),
      'is_visible_to_partner' => Yii::_t('statistic.statistic.sold_is_visible_to_partner'),
      'groupByPhone' => Yii::_t('statistic.statistic.groupByPhone'),
    ];
  }

  /**
   * Перевод для колонок грида
   * @return array
   */
  public function gridColumnLabels()
  {
    return array_filter([
      'hit_id' => Yii::_t('statistic.statistic.detail-transition_id'),
      'phone_number' => Yii::_t('statistic.statistic.detail-phone_number'),
      'ip' => Yii::_t('statistic.statistic.detail-ip'),
      'stream' => Yii::_t('statistic.statistic.detail-stream-name'),
      'url' => Yii::_t('statistic.statistic.detail-url'),
      'source' => Yii::_t('statistic.statistic.sources'),
      'subscribed_at' => Yii::_t('statistic.statistic.detail-subscribed_at'),
      'sold_at' => Yii::_t('statistic.statistic.detail-sold-time'),
      'platforms' => Yii::_t('statistic.statistic.platforms'),
      'landings' => Yii::_t('statistic.statistic.landings'),
      'operators' => Yii::_t('statistic.statistic.operators'),
      'countries' => Yii::_t('statistic.statistic.countries'),
      'landing_pay_type_name' => Yii::_t('statistic.statistic.landing_pay_type_name'),
      'phone' => Yii::_t('statistic.statistic.detail-phone_number'),
      'time' => Yii::_t('statistic.statistic.detail-sold-time'),
      'user_id' => Yii::_t('statistic.statistic.users'),
      'referrer' => Yii::_t('statistic.statistic.referrer'),
      'userAgent' => Yii::_t('statistic.statistic.userAgent'),
      'cid' => Yii::_t('statistic.statistic.cid'),
      'subid1' => Yii::_t('statistic.statistic.subid1'),
      'subid2' => Yii::_t('statistic.statistic.subid2'),
      'partner_profit_rub' => Yii::_t('statistic.statistic.sold_partner_profit', ['currency' => 'RUB']),
      'partner_profit_eur' => Yii::_t('statistic.statistic.sold_partner_profit', ['currency' => 'EUR']),
      'partner_profit_usd' => Yii::_t('statistic.statistic.sold_partner_profit', ['currency' => 'USD']),
      'reseller_price_rub' => Yii::_t('statistic.statistic.sold_reseller_price', ['currency' => 'RUB']),
      'reseller_price_eur' => Yii::_t('statistic.statistic.sold_reseller_price', ['currency' => 'EUR']),
      'reseller_price_usd' => Yii::_t('statistic.statistic.sold_reseller_price', ['currency' => 'USD']),
      'is_visible_to_partner' => Yii::_t('statistic.statistic.sold_is_visible_to_partner'),
      'email' => Yii::_t('statistic.statistic.email'),
      'countPhones' => Yii::_t('statistic.statistic.countPhones'),
    ]);
  }


  /**
   *
   * TRICKY нужно перенести в getGridColumns
   * @return array
   */
  public function getExportAttributeLabels()
  {
    return array_filter([
      'hit_id' => Yii::_t('statistic.statistic.detail-transition_id'),
      'phone_number' => Yii::_t('statistic.statistic.detail-phone_number'),
      'ip' => Yii::_t('statistic.statistic.detail-ip'),
      'stream_name' => Yii::_t('statistic.statistic.detail-stream-name'),
      'url' => Yii::_t('statistic.statistic.detail-url'),
      'source_name' => Yii::_t('statistic.statistic.sources'),
      'subscribed_at' => Yii::_t('statistic.statistic.detail-subscribed_at'),
      'sold_at' => Yii::_t('statistic.statistic.detail-sold-time'),
      'platform_name' => Yii::_t('statistic.statistic.platforms'),
      'landing_name' => Yii::_t('statistic.statistic.landings'),
      'operator_name' => Yii::_t('statistic.statistic.operators'),
      'country_name' => Yii::_t('statistic.statistic.countries'),
      'landing_pay_type_name' => Yii::_t('statistic.statistic.landing_pay_type_name'),
      'phone' => Yii::_t('statistic.statistic.detail-phone_number'),
      'time' => Yii::_t('statistic.statistic.detail-sold-time'),
      'user_id' => Yii::_t('statistic.statistic.users'),
      'referrer' => Yii::_t('statistic.statistic.referrer'),
      'userAgent' => Yii::_t('statistic.statistic.userAgent'),
      'cid' => Yii::_t('statistic.statistic.cid'),
      'profit_rub' => Yii::_t('statistic.statistic.sold_partner_profit', ['currency' => 'RUB']),
      'profit_eur' => Yii::_t('statistic.statistic.sold_partner_profit', ['currency' => 'EUR']),
      'profit_usd' => Yii::_t('statistic.statistic.sold_partner_profit', ['currency' => 'USD']),
      'price_rub' => Yii::_t('statistic.statistic.sold_partner_profit', ['currency' => 'RUB']),
      'price_eur' => Yii::_t('statistic.statistic.sold_partner_profit', ['currency' => 'EUR']),
      'price_usd' => Yii::_t('statistic.statistic.sold_partner_profit', ['currency' => 'USD']),
      'reseller_price_rub' => Yii::_t('statistic.statistic.sold_reseller_price', ['currency' => 'RUB']),
      'reseller_price_eur' => Yii::_t('statistic.statistic.sold_reseller_price', ['currency' => 'EUR']),
      'reseller_price_usd' => Yii::_t('statistic.statistic.sold_reseller_price', ['currency' => 'USD']),
      'is_visible_to_partner' => Yii::_t('statistic.statistic.sold_is_visible_to_partner'),
      'email' => Yii::_t('statistic.statistic.email'),
      'countPhones' => Yii::_t('statistic.statistic.countPhones'),
    ]);
  }

  public function handleFilters(Query &$query)
  {
    $userCurrency = $this->getUserCurrency();

    if (!in_array($userCurrency, ['rub', 'usd', 'eur'], true)) {
      throw new UnexpectedValueException('currency is incorrect');
    }

    if ($this->start_date) {
      /** @var Module $module */
      $module = Yii::$app->getModule('statistic');
      $canViewFullTime = $module->canViewFullTimeStatistic();
      if (!$canViewFullTime) {
        $time = strtotime($this->start_date);
        $minTime = strtotime('-3 months');

        $time < $minTime && $this->start_date = date('Y-m-d', $minTime);
      }

      $query->andFilterWhere(['>=', 'date', $this->formatDateDB($this->start_date)]);
    }

    if ($this->end_date) {
      $query->andFilterWhere(['<=', 'date', $this->formatDateDB($this->end_date)]);
    }

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
      Yii::$app->user->identity->filterUsersItems($query, 'st', 'user_id');
    } else {
      $query->andWhere(['st.user_id' => Yii::$app->user->id]);
    }


    if ($this->canFilterByProviders()) {
      $query->andFilterWhere(['st.provider_id' => $this->providers]);
    }

    if ($this->canFilterByLandingPayTypes()) {
      $query->andFilterWhere(['st.landing_pay_type_id' => $this->landing_pay_types]);
    }

    $query->andFilterWhere(['st.hit_id' => $this->hit_id]);

    $query->andFilterWhere(['like', 'ss.phone', $this->phone_number]);

    if ($this->canFilterByCurrency()) {
      $query->andWhere(['st.currency_id' => $this->allCurrencies[$this->currency]]);
    }

    $query->andFilterWhere(['st.is_visible_to_partner' => $this->is_visible_to_partner]);

    $query->andFilterWhere(['like', 'hp.referer' , $this->referer]);
  }

  function getResellerPrice(array $gridRow, $currency)
  {
    return ArrayHelper::getValue($gridRow, 'reseller_price_' . $currency);
  }

  function getPartnerProfit(array $gridRow, $currency)
  {
    return ArrayHelper::getValue($gridRow, 'profit_' . $currency);
  }

  function findOne($recordId)
  {
    $query = (new StatisticQuery())
      ->select([
        'hit_id' => 'st.hit_id',
        'operator_id' => 'st.operator_id',
        'landing_id' => 'st.landing_id',
        'date' => 'st.date',
        'price_rub' => 'price_rub',
        'price_eur' => 'price_eur',
        'price_usd' => 'price_usd',
        'profit_rub' => 'profit_rub',
        'profit_eur' => 'profit_eur',
        'profit_usd' => 'profit_usd',
        'reseller_price_rub' => 'reseller_price_rub',
        'reseller_price_eur' => 'reseller_price_eur',
        'reseller_price_usd' => 'reseller_price_usd',
        'phone' => 'ss.phone',
        'subscribed_at' => 'ss.time_on',
        'ip' => 'hp.ip',
        'sold_at' => 'st.time',
        'is_visible_to_partner' => 'st.is_visible_to_partner',
        'user_id' => 'u.id',
        'email' => 'u.email',
      ])
      ->from(['st' => 'sold_subscriptions'])
      ->innerJoin('search_subscriptions ss', 'ss.hit_id = st.hit_id')
      ->where(['st.hit_id' => $recordId]);

    $query = $this->addQueryJoins($query);

    return $query->one();
  }

  /*
   * Формирует запрос для getStatisticGroupQueries и getStatisticGroupQueriesPhone
   * @return StatisticQuery
   */
  protected function getCommonStatisticGroupQuery()
  {
    $this->handleOpenCloseFilters();

    $query = (new StatisticQuery())
      ->select([
        'hit_id' => 'st.hit_id',
        'reseller_price_rub' => 'reseller_price_rub',
        'reseller_price_eur' => 'reseller_price_eur',
        'reseller_price_usd' => 'reseller_price_usd',
        'price_rub' => 'price_rub',
        'price_eur' => 'price_eur',
        'price_usd' => 'price_usd',
        'profit_rub' => 'profit_rub',
        'profit_eur' => 'profit_eur',
        'profit_usd' => 'profit_usd',
        'is_visible_to_partner' => 'is_visible_to_partner',
        'phone' => 'ss.phone',
        'sold_at' => 'st.time',
        'ip' => 'ss.ip',
        'subscribed_at' => 'ss.time_on',
        'user_id' => 'u.id',
        'email' => 'u.email',
      ])
      ->innerJoin('search_subscriptions ss', 'ss.hit_id = st.hit_id');

    $query = $this->addQueryJoins($query);

    // tricky: сделано не через датапровайдер для поддержки выгрузки в CSV (сортировка должна быть в запросе)
    if (isset($this->requestData['sort'])) {
      $sortParam = $this->requestData['sort'];
      $order = strncmp($sortParam, '-', 1) === 0 ? SORT_DESC : SORT_ASC;
      $sortAttr = $order === SORT_DESC ? substr($sortParam, 1) : $sortParam;
      if (in_array($sortAttr, $this->getSortAttributes())) {
        $query->orderBy([$sortAttr => $order]);
      }
    }

    return $query;
  }

  /**
   * Возвращает сформированный запрос [query] для сгруппированной статистики
   * @return Query[]
   */
  public function getStatisticGroupQueries()
  {
    if ($this->groupByPhone) {
      return $this->getStatisticGroupQueriesPhone();
    }

    $query = $this->getCommonStatisticGroupQuery()
      ->from(['st' => 'sold_subscriptions']);

    if (!$this->canViewHiddenSoldSubscriptions()) {
      $query->andWhere(['st.is_visible_to_partner' => 1]);
    }

    // tricky: сделано не через датапровайдер для поддержки выгрузки в CSV (сортировка должна быть в запросе)
    if (!isset($this->requestData['sort'])) {
      $query->orderBy(['sold_at' => SORT_DESC]);
    }

    $this->handleFilters($query);

    return [$query];
  }

  /**
   * Возвращает сформированный запрос [query] для сгруппированной статистики с группировкой по телефонам
   * @return Query[]
   */
  protected function getStatisticGroupQueriesPhone()
  {
    $query = $this->getCommonStatisticGroupQuery()->addSelect(['countPhones' => 'st.countPhones']);

    $innerPhoneQuery = (new Query())
      ->select([
        'st.*',
        'countPhones' => 'COUNT(1)'
      ])
      ->from(['st' => 'sold_subscriptions'])
      ->innerJoin(['ss' => 'search_subscriptions'], 'ss.hit_id = st.hit_id')
      ->innerJoin(['hp' => 'hit_params'], 'hp.hit_id = st.hit_id')
      ->groupBy(['ss.phone']);

    $this->handleFilters($innerPhoneQuery);

    $notAvailableUserIds = Yii::$app->getModule('users')
      ->api('notAvailableUserIds', [
        'userId' => Yii::$app->user->id,
      ])
      ->getResult();

    if ($notAvailableUserIds) {
      $innerPhoneQuery->andWhere(['not in', 'st.user_id', $notAvailableUserIds]);
    }

    $query = $query->from(['st' => $innerPhoneQuery]);

    if (!$this->canViewHiddenSoldSubscriptions()) {
      $query->andWhere(['st.is_visible_to_partner' => 1]);
    }

    // tricky: сделано не через датапровайдер для поддержки выгрузки в CSV (сортировка должна быть в запросе)
    if (!isset($this->requestData['sort'])) {
      $query->orderBy(['countPhones' => SORT_DESC]);
    }

    return [$query];
  }

  public function getStatisticGroup()
  {
    list($query) = $this->getStatisticGroupQueries();

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
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
      'ss.time_on' => 'subscribed_at',
      'st.time' => 'sold_at',
      'country.name' => 'country_name',
      'source.name' => 'source_name',
      'operator.name' => 'operator_name',
      'stream.name' => 'stream_name',
      'platform.name' => 'platform_name',
      'l.name' => 'landing_name',
      'ip',
      'phone',
      'pt.name' => 'landing_pay_type_name',
      'reseller_price_rub',
      'reseller_price_eur',
      'reseller_price_usd',
      'price_rub',
      'price_eur',
      'price_usd',
      'profit_rub',
      'profit_eur',
      'profit_usd',
      'is_visible_to_partner',
      'email',
      'countPhones',
    ];
  }

  public function getCounts()
  {
    $query = (new Query())
      ->select([
        'count' => 'count(*)'
      ])
      ->from(['st' => 'sold_subscriptions'])
      ->groupBy($this->group);

    if ($this->group) {
      $query->addSelect($this->group);
    }

    $this->handleFilters($query);

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
      'pagination' => false
    ]);

    return $dataProvider;

  }

  public function getSums()
  {
    $query = (new Query())
      ->select([
        'sum_reseller_price_rub' => 'sum(reseller_price_rub)',
        'sum_reseller_price_eur' => 'sum(reseller_price_eur)',
        'sum_reseller_price_usd' => 'sum(reseller_price_usd)',
        'sum_price_rub' => 'sum(price_rub)',
        'sum_price_eur' => 'sum(price_eur)',
        'sum_price_usd' => 'sum(price_usd)',
      ])
      ->from(['st' => 'sold_subscriptions'])
      ->groupBy($this->group);

    if ($this->group) {
      $query->addSelect($this->group);
    }

    $this->handleFilters($query);

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
      'pagination' => false
    ]);

    return $dataProvider;
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
      'sellSumFrom',
      'sellSumTo',
      'is_visible_to_partner'
    ];
  }

  public function canViewDetailStatistic()
  {
    return parent::checkPermission('StatisticViewDetailStatistic');
  }

  public function canViewPartnerPrice()
  {
    return parent::checkPermission('StatisticViewSellPartnerPrice');
  }

  function addQueryJoins(Query $query)
  {
    $query->innerJoin('hit_params hp', 'hp.hit_id = st.hit_id');
    $query->addSelect(['hp.referer AS referrer', 'hp.user_agent AS userAgent', 'hp.subid1 AS subid1', 'hp.subid2 AS subid2', 'hp.get_params AS getParams']);

    return parent::addQueryJoins($query);
  }

  /**
   * Должна ли подписка быть видна партнеру
   * @param $gridRow
   * @return mixed
   * @throws \UnexpectedValueException
   */
  protected function isCpaVisible($gridRow)
  {
    /** @var \mcms\payments\Module $paymentsModule */
    $paymentsModule = Yii::$app->getModule('payments');

    $dateFrom = date('Y-m-d', $gridRow['sold_at'] - $this->getCpaDiffCalcDays() * 86400);
    $timeTo = $gridRow['sold_at'];

    $partnerCurrency = $this->getPartnerCurrency($gridRow['user_id']);
    if (!in_array($partnerCurrency, ['rub','usd','eur'], true)) {
      throw new UnexpectedValueException('currency is incorrect');
    }
    $query = (new Query())
      ->select([
        'diff' => new Expression("IFNULL(SUM(`price_$partnerCurrency`) - SUM(`profit_$partnerCurrency`), 0)"),
      ])
      ->from(['st' => 'sold_subscriptions'])
      ->where([
        'st.user_id' => $gridRow['user_id'],
        'st.landing_id' => $gridRow['landing_id'],
        'st.operator_id' => $gridRow['operator_id'],
      ])
      ->andWhere(['>=', 'date', $dateFrom])
      ->andWhere(['<', 'time', $timeTo])
      ->scalar();
    return $query;
  }

}