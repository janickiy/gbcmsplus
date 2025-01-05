<?php

namespace mcms\statistic\models\mysql;

use mcms\statistic\components\AbstractDetailStatistic;
use mcms\statistic\components\StatisticQuery;
use mcms\statistic\Module;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\db\Query;
use mcms\common\module\api\join\Query as JoinQuery;
use yii\helpers\ArrayHelper;

class DetailStatisticIK extends AbstractDetailStatistic
{

  const GROUP_NAME = 'ik';

  public $rebillSumFrom;
  public $rebillSumTo;

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
  public $email;
  public $user_id;
  public $is_visible_to_partner;
  public $referer;
  /**
   * @var  bool Если включено, то будет сгруппировано по номеру телефона и
   * отображено кол-во пдп по этому номеру в новом столбце.
   */
  public $groupByPhone;

  public function init()
  {
    parent::init();

    // С формы может прийти пустая строка, из-за чего происходит ошибка
    if (empty($this->streams)) $this->streams = null;
  }

  public function rules()
  {
    return array_merge(parent::rules(), [
      [['rebillSumFrom', 'rebillSumTo', 'hit_id', 'phone_number', 'is_visible_to_partner'], 'integer'],
      ['rebillSumFrom', 'validateRebillSum'],
      [['landings', 'sources', 'operators', 'platforms', 'streams', 'providers', 'countries', 'users', 'landing_pay_types', 'groupByPhone', 'referer'], 'safe']
    ]);
  }

  public function validateRebillSum($attribute)
  {
    if ($this->rebillSumTo > $this->rebillSumFrom) {
      $this->addError($attribute, Yii::_t('statistic.incorrect_period'));
    }
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
      'sum_profit' => Yii::_t('statistic.statistic.detail-sum_profit', [':userCurrency' => strtoupper($this->getUserCurrency())]),
      'platforms' => Yii::_t('statistic.statistic.platforms'),
      'landings' => Yii::_t('statistic.statistic.landings'),
      'operators' => Yii::_t('statistic.statistic.operators'),
      'countries' => Yii::_t('statistic.statistic.countries'),
      'landingPayType' => Yii::_t('statistic.statistic.landing_pay_types'),
      'referrer' => Yii::_t('statistic.statistic.referrer'),
      'userAgent' => Yii::_t('statistic.statistic.userAgent'),
      'subid1' => Yii::_t('statistic.statistic.subid1'),
      'subid2' => Yii::_t('statistic.statistic.subid2'),
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
      'is_visible_to_partner' => Yii::_t('statistic.statistic.onetime_is_visible_to_partner'),
      'email' => Yii::_t('statistic.statistic.email'),
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
      'sum_profit' => Yii::_t('statistic.statistic.detail-sum_profit', [':userCurrency' => strtoupper($this->getUserCurrency())]),
      'platform_name' => Yii::_t('statistic.statistic.platforms'),
      'landing_name' => Yii::_t('statistic.statistic.landings'),
      'operator_name' => Yii::_t('statistic.statistic.operators'),
      'country_name' => Yii::_t('statistic.statistic.countries'),
      'landingPayType' => Yii::_t('statistic.statistic.landing_pay_types'),
      'referrer' => Yii::_t('statistic.statistic.referrer'),
      'userAgent' => Yii::_t('statistic.statistic.userAgent'),
      'cid' => Yii::_t('statistic.statistic.cid'),
      'profit_rub' => Yii::_t('statistic.statistic.sum_profit', ['currency' => 'RUB']),
      'profit_eur' => Yii::_t('statistic.statistic.sum_profit', ['currency' => 'EUR']),
      'profit_usd' => Yii::_t('statistic.statistic.sum_profit', ['currency' => 'USD']),
      'real_profit_rub' => Yii::_t('statistic.statistic.sum_real_profit', ['currency' => 'RUB']),
      'real_profit_eur' => Yii::_t('statistic.statistic.sum_real_profit', ['currency' => 'EUR']),
      'real_profit_usd' => Yii::_t('statistic.statistic.sum_real_profit', ['currency' => 'USD']),
      'reseller_profit_rub' => Yii::_t('statistic.statistic.sum_reseller_profit', ['currency' => 'RUB']),
      'reseller_profit_eur' => Yii::_t('statistic.statistic.sum_reseller_profit', ['currency' => 'EUR']),
      'reseller_profit_usd' => Yii::_t('statistic.statistic.sum_reseller_profit', ['currency' => 'USD']),
      'is_visible_to_partner' => Yii::_t('statistic.statistic.onetime_is_visible_to_partner'),
      'email' => Yii::_t('statistic.statistic.email'),
      'countPhones' => Yii::_t('statistic.statistic.countPhones'),
    ];
  }

  public function attributeLabels()
  {
    return [
      'hit_id' => Yii::_t('statistic.statistic.detail-transition_id'),
      'phone_number' => Yii::_t('statistic.statistic.detail-phone_number'),
      'ip' => Yii::_t('statistic.statistic.detail-ip'),
      'stream' => Yii::_t('statistic.statistic.detail-stream-name'),
      'url' => Yii::_t('statistic.statistic.detail-url'),
      'source' => Yii::_t('statistic.statistic.sources'),
      'subscribed_at' => Yii::_t('statistic.statistic.detail-subscribed_at'),
      'sum_profit' => Yii::_t('statistic.statistic.detail-sum_profit', [':userCurrency' => strtoupper($this->getUserCurrency())]),

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
      'is_visible_to_partner' => Yii::_t('statistic.statistic.sold_is_visible_to_partner'),
      'groupByPhone' => Yii::_t('statistic.statistic.groupByPhone'),
    ];
  }


  public function handleFilters(Query &$query)
  {
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

    if ($this->rebillSumFrom) {
      $query->andFilterWhere(['>=', 'profit_' . $this->currency, $this->rebillSumFrom]);
    }

    if ($this->rebillSumTo) {
      $query->andFilterWhere(['<=', 'profit_' . $this->currency, $this->rebillSumTo]);
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

    $query->andFilterWhere(['like', 'st.phone' , $this->phone_number]);

    if ($this->canFilterByCurrency()) {
      $query->andWhere(['st.currency_id' => $this->allCurrencies[$this->currency]]);
    }

    if ($this->groupByPhone) {
      $query->andWhere(['<>', 'st.phone', '']);
    }

    $query->andFilterWhere(['st.is_visible_to_partner' => $this->is_visible_to_partner]);

    $query->andFilterWhere(['like', 'hp.referer' , $this->referer]);
  }

  /**
   * Возвращает два сформированных запроса [query, queryCount] для сгруппированной статистики
   * @return Query[]
   */
  public function getStatisticGroupQueries()
  {
    if ($this->groupByPhone) {
      return $this->getStatisticGroupQueriesPhone();
    }

    $query = $this->getCommonStatisticGroupQuery();

    $this->handleFilters($query);

    if (!isset($this->requestData['sort'])) {
      $query->orderBy(['subscribed_at' => SORT_DESC]);
    }

    return [$query];
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
        'real_profit_rub' => 'real_profit_rub',
        'real_profit_eur' => 'real_profit_eur',
        'real_profit_usd' => 'real_profit_usd',
        'reseller_profit_rub' => 'reseller_profit_rub',
        'reseller_profit_eur' => 'reseller_profit_eur',
        'reseller_profit_usd' => 'reseller_profit_usd',
        'profit_rub' => 'profit_rub',
        'profit_eur' => 'profit_eur',
        'profit_usd' => 'profit_usd',
        'phone' => 'phone',
        'subscribed_at' => 'time',
        'ip' => 'st.ip',
        'is_visible_to_partner' => 'st.is_visible_to_partner',
        'email' => 'u.email',
      ])->from(['st' => 'onetime_subscriptions']);


    $query = $this->addQueryJoins($query);

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

  protected function getStatisticGroupQueriesPhone()
  {
    $query = $this->getCommonStatisticGroupQuery()
      ->addSelect(['countPhones' => 'COUNT(1)'])
      ->groupBy(['phone']);

    $this->handleFilters($query);

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
      'real_profit_rub',
      'real_profit_eur',
      'real_profit_usd',
      'reseller_profit_rub',
      'reseller_profit_eur',
      'reseller_profit_usd',
      'profit_rub',
      'profit_eur',
      'profit_usd',
      'time' => 'subscribed_at',
      'country.name' => 'country_name',
      'source.name' => 'source_name',
      'operator.name' => 'operator_name',
      'stream.name' => 'stream_name',
      'platform.name' => 'platform_name',
      'l.name' => 'landing_name',
      'ip',
      'phone',
      'is_visible_to_partner',
      'email',
      'countPhones',
    ];
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
      'rebillSumFrom',
      'rebillSumTo',
      'hit_id',
      'phone_number',
      'is_visible_to_partner'
    ];
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

  function getAdminProfit(array $gridRow, $currency)
  {
    return ArrayHelper::getValue($gridRow, 'real_profit_' . $currency);
  }

  function getResellerProfit(array $gridRow, $currency)
  {
    return ArrayHelper::getValue($gridRow, 'reseller_profit_' . $currency);
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
        'profit_rub' => 'st.profit_rub',
        'profit_eur' => 'st.profit_eur',
        'profit_usd' => 'st.profit_usd',
        'calc_profit_rub' => 'st.calc_profit_rub',
        'calc_profit_eur' => 'st.calc_profit_eur',
        'calc_profit_usd' => 'st.calc_profit_usd',
        'real_profit_rub' => 'st.real_profit_rub',
        'real_profit_eur' => 'st.real_profit_eur',
        'real_profit_usd' => 'st.real_profit_usd',
        'reseller_profit_rub' => 'st.reseller_profit_rub',
        'reseller_profit_eur' => 'st.reseller_profit_eur',
        'reseller_profit_usd' => 'st.reseller_profit_usd',
        'phone' => 'st.phone',
        'subscribed_at' => 'st.time',
        'ip' => 'st.ip',
        'is_visible_to_partner' => 'st.is_visible_to_partner',
        'email' => 'u.email',
        'user_id' => 'u.id',
      ])
      ->from(['st' => 'onetime_subscriptions'])
      ->where(['st.hit_id' => $recordId]);

    $query = $this->addQueryJoins($query);
    return $query->one();
  }

  public function addQueryJoins(Query $query)
  {
    $query->innerJoin('hit_params hp', 'hp.hit_id = st.hit_id');
    $query->addSelect(['hp.referer AS referrer', 'hp.user_agent AS userAgent', 'hp.subid1 AS subid1', 'hp.subid2 AS subid2', 'hp.get_params AS getParams']);

    return parent::addQueryJoins($query);
  }

  /**
   * Должна ли подписка быть видна партнеру
   * @param $gridRow
   * @return mixed
   */
  protected function isCpaVisible($gridRow)
  {
    /** @var \mcms\payments\Module $paymentsModule */
    $paymentsModule = Yii::$app->getModule('payments');

    $dateFrom = date('Y-m-d', $gridRow['subscribed_at'] - $this->getCpaDiffCalcDays() * 86400);
    $timeTo = $gridRow['subscribed_at'];

    $partnerCurrency = $this->getPartnerCurrency($gridRow['user_id']);

    $query = (new Query())
      ->select([
        'diff' => new Expression("IFNULL(SUM(`calc_profit_$partnerCurrency`) - SUM(`profit_$partnerCurrency`), 0)"),
        ])
      ->from(['st' => 'onetime_subscriptions'])
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