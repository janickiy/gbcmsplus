<?php

namespace mcms\statistic\models\mysql;

use mcms\common\helpers\ArrayHelper;
use mcms\statistic\components\AbstractDetailStatistic;
use mcms\statistic\components\StatisticQuery;
use Yii;
use yii\data\ArrayDataProvider;
use yii\db\Expression;
use yii\db\Query;

/**
 * Class Analytics
 * @package mcms\statistic\models\mysql
 */
class Analytics extends AbstractDetailStatistic
{

  const STATISTIC_NAME = 'analytics';
  const CONCAT_DELIMITER = '|';
  const CONCAT_PARAM = 'scope_concat';
  const CONCAT_ATTRIBUTES = ['scope_count_rebills', 'scope_sum_profit'];
  const SCOPE_OFF_PARAM = 'scope_count_offs';

  const ALL = 'all';
  const REVSHARE = 'revshare';
  const CPA = 'cpa';
  const REJECTED = 'rejected';
  const SOLD = 'sold';

  const SCOPE_QUERY = 'scope_query';
  const SCOPE_SOLD_QUERY = 'scope_sold_query';
  const SCOPE_CPA_QUERY = 'scope_cpa_query';
  const MAIN_QUERY = 'main_query';
  const SOLD_QUERY = 'sold_query';
  const REBILLS_SOLD_QUERY = 'rebills_sold_query';
  const OFFS_SOLD_QUERY = 'offs_sold_query';

  /** @var string Выбранный вариант профита (Revshare | CPA | Sold | Rejected) */
  public $type = self::ALL;

  /**
   * @var
   */
  public $landings;
  /**
   * @var
   */
  public $sources;
  /**
   * @var
   */
  public $operators;
  /**
   * @var
   */
  public $platforms;
  /**
   * @var
   */
  public $streams;
  /**
   * @var
   */
  public $providers;
  /**
   * @var
   */
  public $countries;
  /**
   * @var
   */
  public $users;
  /**
   * @var
   */
  public $landing_pay_types;

  public $date_ltv;
  /** @var int Видимость партнеру */
  public $is_visible_to_partner;

  /**
   * кэш статистики
   * @var
   */
  protected $_statData;

  /** @var  array кэш для каждой ячейки в строке Итого, чтобы не ситать каждый раз заново */
  private $_fieldResults;


  /**
   * @inheritdoc
   */
  public function rules()
  {
    return array_merge([
      [['is_visible_to_partner'], 'integer'],
      [['type', 'date_ltv', 'landings', 'sources', 'operators', 'platforms', 'streams', 'providers', 'countries', 'users', 'landing_pay_types'], 'safe']
    ], parent::rules());
  }

  /**
   * Перевод для колонок грида
   * @return array
   */
  public function gridColumnLabels()
  {
    return [
      'date' => Yii::_t('statistic.statistic.date'),
      'count_ons' => Yii::_t('statistic.statistic.count_ons'),
      'count_offs' => Yii::_t('statistic.statistic.count_offs'),
      'count_rebills' => Yii::_t('statistic.statistic.count_longs'),
      'sum_profit' => Yii::_t('statistic.analytics.sum_profit', ['currency' => strtoupper($this->getCurrency())]),
      'scope_count_rebills' => Yii::_t('statistic.analytics.scope_count_rebills'),
      'scope_sum_profit' => Yii::_t('statistic.analytics.scope_sum_profit', ['currency' => strtoupper($this->getCurrency())]),
      'average_rebills' => Yii::_t('statistic.analytics.average_rebills'),
      'average_profit' => Yii::_t('statistic.analytics.average_profit', ['currency' => strtoupper($this->getCurrency())]),
      'cpr' => Yii::_t('statistic.analytics.cpr'),
      'sold_partner_profit' => Yii::_t('statistic.analytics.sold_partner_profit'),
      'count_sold' => Yii::_t('statistic.analytics.count_sold'),
      'scope_count_offs' => Yii::_t('statistic.analytics.scope_count_offs'),
      'active_database' => Yii::_t('statistic.analytics.active_database'),
    ];
  }

  /**
   * @inheritdoc
   */
  public function handleFilters(Query &$query)
  {
    /** @var $query StatisticQuery */
    $alias = self::getQueryAlias($query);

    if (in_array($query->getId(), [self::MAIN_QUERY, self::OFFS_SOLD_QUERY, self::REBILLS_SOLD_QUERY], true)) {
      $query
        ->andFilterWhere(['>=', "$alias.date", $this->formatDateDB($this->start_date)])
        ->andFilterWhere(['<=', "$alias.date", $this->formatDateDB($this->end_date)]);
    }

    if ($this->canFilterByLandings()) {
      $query->andFilterWhere(["$alias.landing_id" => $this->landings]);
    }

    if ($this->canFilterByProviders()) {
      $query->andFilterWhere(["$alias.provider_id" => $this->providers]);
    }

    if ($this->canFilterByOperators()) {
      $query->andFilterWhere(["$alias.operator_id" => $this->operators]);
    }

    if ($this->canFilterBySources()) {
      $query->andFilterWhere([
        in_array($query->getId(), [self::REBILLS_SOLD_QUERY, self::SCOPE_SOLD_QUERY, self::OFFS_SOLD_QUERY], true)
          ? 'sold.source_id'
          : "$alias.source_id" => $this->sources
      ]);
    }

    if ($this->canFilterByPlatform()) {
      $query->andFilterWhere(["$alias.platform_id" => $this->platforms]);
    }

    if ($this->canFilterByStreams()) {
      $query->andFilterWhere([
        in_array($query->getId(), [self::REBILLS_SOLD_QUERY, self::SCOPE_SOLD_QUERY, self::OFFS_SOLD_QUERY], true)
          ? 'sold.stream_id'
          : "$alias.stream_id" => $this->streams
      ]);
    }

    if ($this->canFilterByCountries()) {
      $query->andFilterWhere([
        in_array($query->getId(), [self::REBILLS_SOLD_QUERY, self::OFFS_SOLD_QUERY], true)
          ? 'sold.country_id'
          : "$alias.country_id" => $this->countries]);
    }

    if ($this->canFilterByUsers()) {
      $query->andFilterWhere([
        in_array($query->getId(), [self::REBILLS_SOLD_QUERY, self::SCOPE_SOLD_QUERY, self::SCOPE_CPA_QUERY, self::OFFS_SOLD_QUERY], true)
          ? ($query->getId() === self::SCOPE_CPA_QUERY ? 'sources.user_id' : 'sold.user_id')
          : "$alias.user_id" => $this->users
      ]);
    }

    if ($this->canFilterByLandingPayTypes()) {
      $query->andFilterWhere(["$alias.landing_pay_type_id" => $this->landing_pay_types]);
    }
    // у нас все ребиллы пишутся в евро, поэтому вся стата есть только по евро
    // если закомментить, то будет показывать всё, независимо от валюты в которой было записано
//    if ($this->canFilterByCurrency()) {
//      $query->andWhere(["$alias.currency_id" => $this->allCurrencies[$this->currency]]);
//    }
    // Фильтруем по видимости только солды
    if ($this->type === self::SOLD) {
      $where = in_array($query->getId(), [self::REBILLS_SOLD_QUERY, self::SCOPE_SOLD_QUERY, self::OFFS_SOLD_QUERY], true)
        ? ['sold.is_visible_to_partner' => $this->is_visible_to_partner]
        : ["$alias.is_visible_to_partner" => $this->is_visible_to_partner];
      $query->andFilterWhere($where);
    }
  }

  /**
   * @inheritdoc
   */
  public function getStatisticGroup()
  {
    $this->handleOpenCloseFilters();

    $currency = $this->getCurrency();

    list($query, $scopeQuery, $scopeOffsQuery) = $this->getCommonStatisticQuery($currency);


    if ($this->type === self::ALL) {
      return $this->getAllStatisticGroup($scopeQuery, $scopeOffsQuery, $currency);
    }

    if ($this->type === self::CPA) {
      return $this->getCPAStatisticGroup($scopeQuery, $scopeOffsQuery, $currency);
    }

    if ($this->type === self::REVSHARE) {
      list($query, $scopeQuery, $scopeOffsQuery) = $this->getRevshareStatisticQuery($query, $scopeQuery, $scopeOffsQuery);
    }

    if ($this->type === self::REJECTED) {
      return $this->getRejectedStatisticQuery($scopeQuery, $scopeOffsQuery, $currency);
    }

    if ($this->type === self::SOLD) {
      return  $this->getSoldStatisticGroup($scopeQuery, $scopeOffsQuery, $currency);
    }

    $this->handleFilters($scopeQuery);
    $this->handleFilters($scopeOffsQuery);
    $this->handleFilters($query);

    $query->addSelect([
      self::CONCAT_PARAM => $scopeQuery
    ]);
    $query->addSelect([
      self::SCOPE_OFF_PARAM => $scopeOffsQuery
    ]);

    $data = $this->indexBy($query->each());
    $data = $this->mergeStatisticArrays($data);

    $this->_statData = $data;


    return new ArrayDataProvider([
      'allModels' => $data,
      'sort' => [
        'defaultOrder' => ['date' => SORT_DESC],
        'attributes' => [
          'date',
          'count_ons',
          'count_offs',
          'count_rebills',
          'sum_profit',
        ]
      ],
    ]);
  }

  /**
   * @param $currency string
   * @return array
   */
  private function getCommonStatisticQuery($currency)
  {
    $delimiter = self::CONCAT_DELIMITER;
    $trialOperators = Yii::$app->getModule('promo')->api('trialOperators')->getResult();
    $scopeQuery = (new StatisticQuery())
      ->setId(self::SCOPE_QUERY)
      ->select(new Expression("CONCAT(COUNT(sr.id), '$delimiter', SUM(reseller_profit_$currency))"))
      ->from('subscriptions s')
      ->innerJoin('search_subscriptions ss', 'ss.hit_id = s.hit_id')
      ->leftJoin('subscription_rebills sr', 'ss.hit_id = sr.hit_id')
      ->where([
        'and',
        ['IN', 'ss.operator_id', $trialOperators],
        ['<=', 'sr.date', date('Y-m-d', strtotime(
          $this->date_ltv ?: $this->end_date .
            ($this->start_date == $this->end_date ? '+1 day' : '')
        ))]
      ])
      ->orWhere([
        'and',
        ['NOT IN', 'ss.operator_id', $trialOperators],
        ['<=', 'sr.date', $this->date_ltv ?: $this->end_date]
      ]);

    $scopeOffsQuery = (new StatisticQuery())
      ->setId(self::SCOPE_QUERY)
      ->select('COUNT(so.id)')
      ->from('subscriptions s')
      ->innerJoin('search_subscriptions ss', 'ss.hit_id = s.hit_id')
      ->innerJoin('subscription_offs so', 'so.hit_id = s.hit_id')
      ->where(['<=', 'so.date', $this->date_ltv ?: $this->end_date]);

    $query = (new StatisticQuery())
      ->setId(self::MAIN_QUERY)
      ->select([
        'date' => 'st.date',
        'count_ons' => 'SUM(count_ons_revshare)',
        'count_offs' => 'SUM(count_offs_revshare)',
        'count_rebills' => 'SUM(count_rebills_revshare)',
        'sum_profit' => "SUM(res_revshare_profit_$currency)",
      ])
      ->from('statistic st')
      ->having('count_ons > 0 || count_offs > 0 || count_rebills > 0')
      ->groupBy('date');

    return [$query, $scopeQuery, $scopeOffsQuery];
  }

  /**
   * @param $query StatisticQuery
   * @param $scopeQuery StatisticQuery
   * @param $scopeOffsQuery StatisticQuery
   * @return array
   */
  private function getRevshareStatisticQuery($query, $scopeQuery, $scopeOffsQuery)
  {
    $scopeQuery->leftJoin('sold_subscriptions sold', 'sold.hit_id = s.hit_id');
    $scopeQuery->andWhere('sold.id IS NULL AND s.is_cpa = 0');
    $scopeQuery->andWhere([
      'and',
      ['=', 's.date', new Expression('st.date')],
    ]);
    $scopeOffsQuery->leftJoin('sold_subscriptions sold', 'sold.hit_id = s.hit_id');
    $scopeOffsQuery->andWhere('sold.id IS NULL AND s.is_cpa = 0');
    $scopeOffsQuery->andWhere([
      'and',
      ['=', 's.date', new Expression('st.date')],
    ]);

    return [$query, $scopeQuery, $scopeOffsQuery];
  }

  /**
   * @param $scopeQuery StatisticQuery
   * @param $scopeOffsQuery StatisticQuery
   * @param $currency string
   * @return ArrayDataProvider
   */
  private function getRejectedStatisticQuery($scopeQuery, $scopeOffsQuery, $currency)
  {
    $scopeQuery->leftJoin('sold_subscriptions sold', 'sold.hit_id = s.hit_id');
    $scopeQuery->andWhere('sold.id IS NULL AND s.is_cpa = 1');
    $scopeQuery->andWhere([
      'and',
      ['=', 's.date', new Expression('st.date')],
    ]);

    $scopeOffsQuery->leftJoin('sold_subscriptions sold', 'sold.hit_id = s.hit_id');
    $scopeOffsQuery->andWhere('sold.id IS NULL AND s.is_cpa = 1');
    $scopeOffsQuery->andWhere([
      'and',
      ['=', 's.date', new Expression('st.date')],
    ]);

    $query = (new StatisticQuery())
      ->setId(self::MAIN_QUERY)
      ->select([
        'date' => 'st.date',
        'count_ons' => 'SUM(count_ons_rejected)',
        'count_offs' => 'SUM(count_offs_rejected)',
        'count_rebills' => 'SUM(count_rebills_rejected)',
        'sum_profit' => "SUM(res_rejected_profit_$currency)",
      ])
      ->from('statistic st')
      ->having('count_ons > 0 || count_offs > 0 || count_rebills > 0')
      ->groupBy('date');

    $this->handleFilters($scopeQuery);
    $this->handleFilters($scopeOffsQuery);
    $this->handleFilters($query);

    $query->addSelect([self::CONCAT_PARAM => $scopeQuery]);
    $query->addSelect([self::SCOPE_OFF_PARAM => $scopeOffsQuery]);
    $data = $this->indexBy($query->each());

    $data = $this->mergeStatisticArrays(
      $data,
      $this->getCPRData()
    );
    $this->_statData = $data;

    return new ArrayDataProvider([
      'allModels' => $data,
      'sort' => [
        'defaultOrder' => ['date' => SORT_DESC],
        'attributes' => [
          'date',
          'count_ons',
          'count_offs',
          'count_rebills',
          'sum_profit',
        ]
      ],
    ]);
  }

  /**
   * @param $scopeQuery StatisticQuery
   * @param $scopeOffsQuery StatisticQuery
   * @param $currency string
   * @return ArrayDataProvider
   */
  private function getSoldStatisticGroup($scopeQuery, $scopeOffsQuery, $currency)
  {
    $scopeQuery->setId(self::SCOPE_SOLD_QUERY);
    $scopeQuery->innerJoin('sold_subscriptions sold', 'sold.hit_id = s.hit_id');
    $scopeQuery->andWhere([
      'and',
      ['=', 'sold.date', new Expression('st.date')],
    ]);

    $scopeOffsQuery->setId(self::SCOPE_SOLD_QUERY);
    $scopeOffsQuery->innerJoin('sold_subscriptions sold', 'sold.hit_id = s.hit_id');
    $scopeOffsQuery->andWhere([
      'and',
      ['=', 'sold.date', new Expression('st.date')],
    ]);

    $soldOffsQuery = (new StatisticQuery())
      ->setId(self::OFFS_SOLD_QUERY)
      ->select([
        'count_offs' => 'COUNT(ss.id)',
        'date' => 'ss.date',
      ])
      ->from('sold_subscriptions sold')
      ->innerJoin('subscription_offs ss', 'sold.hit_id = ss.hit_id')
      ->groupBy('ss.date');
    $this->handleFilters($soldOffsQuery);

    $soldRebillsQuery = (new StatisticQuery())
      ->setId(self::REBILLS_SOLD_QUERY)
      ->select([
        'count_rebills' => 'COUNT(ss.id)',
        'sum_profit' => "SUM(ss.reseller_profit_$currency)",
        'date' => 'ss.date',
      ])
      ->from('sold_subscriptions sold')
      ->innerJoin('subscription_rebills ss', 'sold.hit_id = ss.hit_id')
      ->groupBy('ss.date');
    $this->handleFilters($soldRebillsQuery);

    $query = (new StatisticQuery())
      ->setId(self::MAIN_QUERY)
      ->select([
        'date',
        'count_ons' => 'COUNT(id)',
      ])
      ->from('sold_subscriptions st')
      ->groupBy('date');

    $this->handleFilters($query);
    $this->handleFilters($scopeQuery);
    $this->handleFilters($scopeOffsQuery);

    $query->addSelect([self::CONCAT_PARAM => $scopeQuery]);
    $query->addSelect([self::SCOPE_OFF_PARAM => $scopeOffsQuery]);
    $soldData = $this->indexBy($query->each());
    $soldOffsData = $this->indexBy($soldOffsQuery->each());
    $soldRebillsData = $this->indexBy($soldRebillsQuery->each());

    $data = $this->mergeStatisticArrays(
      $this->getCPRData(),
      $soldData,
      $soldOffsData,
      $soldRebillsData
    );
    $this->_statData = $data;

    return new ArrayDataProvider([
      'allModels' => $data,
      'sort' => [
        'defaultOrder' => ['date' => SORT_DESC],
        'attributes' => [
          'date',
          'count_ons',
          'count_offs',
          'count_rebills',
          'sum_profit',
        ]
      ],
    ]);
  }

  /**
   * @param $scopeQuery StatisticQuery
   * @param $scopeOffsQuery StatisticQuery
   * @param $currency string
   * @return ArrayDataProvider
   */
  private function getCPAStatisticGroup($scopeQuery, $scopeOffsQuery, $currency)
  {
    $scopeQuery->setId(self::SCOPE_CPA_QUERY);
    $scopeQuery->innerJoin('sources', 'sources.id = s.source_id');
    $scopeQuery->andWhere('s.is_cpa = 1');
    $scopeQuery->andWhere([
      'and',
      ['=', 's.date', new Expression('st.date')],
    ]);

    $scopeOffsQuery->setId(self::SCOPE_CPA_QUERY);
    $scopeOffsQuery->innerJoin('sources', 'sources.id = s.source_id');
    $scopeOffsQuery->andWhere('s.is_cpa = 1');
    $scopeOffsQuery->andWhere([
      'and',
      ['=', 's.date', new Expression('st.date')],
    ]);

    $cpaQuery = (new StatisticQuery())
      ->setId(self::MAIN_QUERY)
      ->select([
        'date' => 'st.date',
        'count_ons' => 'SUM(count_ons_cpa)',
        'count_offs' => 'SUM(count_offs_rejected) + SUM(count_offs_sold)',
        'count_rebills' => 'SUM(count_rebills_rejected) + SUM(count_rebills_sold)',
        'sum_profit' => "SUM(res_sold_profit_$currency) + SUM(res_rejected_profit_$currency)",
      ])
      ->from('statistic st')
      ->having('count_ons > 0 || count_offs > 0 || count_rebills > 0')
      ->groupBy('date');


    $this->handleFilters($scopeQuery);
    $this->handleFilters($scopeOffsQuery);
    $this->handleFilters($cpaQuery);

    $cpaQuery->addSelect([self::CONCAT_PARAM => $scopeQuery]);
    $cpaQuery->addSelect([self::SCOPE_OFF_PARAM => $scopeOffsQuery]);

    $cpaData = $this->indexBy($cpaQuery->each());

    $data = $this->mergeStatisticArrays(
      $cpaData,
      $this->getCPRData()
    );
    $this->_statData = $data;

    return new ArrayDataProvider([
      'allModels' => $data,
      'sort' => [
        'defaultOrder' => ['date' => SORT_DESC],
        'attributes' => [
          'date',
          'count_ons',
          'count_offs',
          'count_rebills',
          'sum_profit',
        ]
      ],
    ]);
  }

  /**
   * @param $scopeQuery StatisticQuery
   * @param $scopeOffsQuery StatisticQuery
   * @param $currency string
   * @return ArrayDataProvider
   */
  private function getAllStatisticGroup($scopeQuery, $scopeOffsQuery, $currency)
  {
    $revshareScopeQuery = clone $scopeQuery;
    $revshareScopeOffQuery = clone $scopeOffsQuery;

    $revshareScopeQuery->setId(self::SCOPE_QUERY);
    $revshareScopeQuery->leftJoin('sold_subscriptions sold', 'sold.hit_id = s.hit_id');
    $revshareScopeQuery->andWhere('sold.id IS NULL AND s.is_cpa = 0');
    $revshareScopeQuery->andWhere([
      'and',
      ['=', 's.date', new Expression('st.date')],
    ]);

    $revshareScopeOffQuery->setId(self::SCOPE_QUERY);
    $revshareScopeOffQuery->leftJoin('sold_subscriptions sold', 'sold.hit_id = s.hit_id');
    $revshareScopeOffQuery->andWhere('sold.id IS NULL AND s.is_cpa = 0');
    $revshareScopeOffQuery->andWhere([
      'and',
      ['=', 's.date', new Expression('st.date')],
    ]);

    $revshareQuery = (new StatisticQuery())
      ->setId(self::MAIN_QUERY)
      ->select([
        'date' => 'st.date',
        'revshare_count_ons' => 'SUM(count_ons_revshare)',
        'revshare_count_offs' => 'SUM(count_offs_revshare)',
        'revshare_count_rebills' => 'SUM(count_rebills_revshare)',
        'revshare_sum_profit' => "SUM(res_revshare_profit_$currency)",
      ])
      ->from('statistic st')
      ->having('revshare_count_ons > 0 || revshare_count_offs > 0 || revshare_count_rebills > 0')
      ->groupBy('date');

    $scopeQuery->setId(self::SCOPE_SOLD_QUERY);
    $scopeQuery->leftJoin('sold_subscriptions sold', 'sold.hit_id = s.hit_id');
    $scopeQuery->andWhere('sold.id IS NOT NULL OR s.is_cpa = 1');
    $scopeQuery->andWhere([
      'and',
      ['=', 's.date', new Expression('st.date')],
    ]);

    $scopeOffsQuery->setId(self::SCOPE_SOLD_QUERY);
    $scopeOffsQuery->leftJoin('sold_subscriptions sold', 'sold.hit_id = s.hit_id');
    $scopeOffsQuery->andWhere('sold.id IS NOT NULL OR s.is_cpa = 1');
    $scopeOffsQuery->andWhere([
      'and',
      ['=', 's.date', new Expression('st.date')],
    ]);

    $cpaQuery = (new StatisticQuery())
      ->setId(self::MAIN_QUERY)
      ->select([
        'date' => 'st.date',
        'count_ons' => 'SUM(count_ons_cpa)',
        'count_offs' => 'SUM(count_offs_rejected) + SUM(count_offs_sold)',
        'count_rebills' => 'SUM(count_rebills_rejected) + SUM(count_rebills_sold)',
        'sum_profit' => "SUM(res_sold_profit_$currency) + SUM(res_rejected_profit_$currency)",
      ])
      ->from('statistic st')
      ->having('count_ons > 0 || count_offs > 0 || count_rebills > 0')
      ->groupBy('date');


    $this->handleFilters($scopeQuery);
    $this->handleFilters($scopeOffsQuery);
    $this->handleFilters($cpaQuery);
    $this->handleFilters($revshareScopeQuery);
    $this->handleFilters($revshareScopeOffQuery);
    $this->handleFilters($revshareQuery);

    $cpaQuery->addSelect([self::CONCAT_PARAM => $scopeQuery]);
    $cpaQuery->addSelect([self::SCOPE_OFF_PARAM => $scopeOffsQuery]);
    $revshareQuery->addSelect([self::CONCAT_PARAM . '_revshare' => $revshareScopeQuery]);
    $revshareQuery->addSelect([self::SCOPE_OFF_PARAM . '_revshare' => $revshareScopeOffQuery]);

    $revshareData = $this->indexBy($revshareQuery->each());
    $cpaData = $this->indexBy($cpaQuery->each());

    $data = $this->mergeStatisticArrays(
      $revshareData,
      $cpaData
    );

    $this->_statData = $data;

    return new ArrayDataProvider([
      'allModels' => $data,
      'sort' => [
        'defaultOrder' => ['date' => SORT_DESC],
        'attributes' => [
          'date',
          'count_ons',
          'count_offs',
          'count_rebills',
          'sum_profit',
        ]
      ],
    ]);
  }

  /**
   * Возвращает данные для вычисления CPR
   * @return array
   */
  private function getCPRData()
  {
    $soldQuery = (new StatisticQuery())
      ->setId(self::SCOPE_QUERY)
      ->select('COUNT(id)')
      ->from('sold_subscriptions ss')
      ->where('ss.date=st.date AND ss.currency_id=st.currency_id');
    $this->handleFilters($soldQuery);

    $partnerSoldQuery = (new StatisticQuery())
      ->setId(self::MAIN_QUERY)
      ->select([
        'sold_count_ons' => $soldQuery,
        'date' => 'st.date',
        'sold_partner_profit_rub' => 'SUM(profit_rub)',
        'sold_partner_profit_eur' => 'SUM(profit_eur)',
        'sold_partner_profit_usd' => 'SUM(profit_usd)',
      ])
      ->from('sold_subscriptions st')
      ->andWhere(['st.is_visible_to_partner' => 1])
      ->groupBy('date');

    $this->handleFilters($partnerSoldQuery);

    return $this->indexBy($partnerSoldQuery->each());
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
      'hit_id',
      'phone_number',
      'users',
      'landing_pay_types',
      'is_visible_to_partner',
    ];
  }

  /**
   * @return array
   */
  public function attributeLabels()
  {
    return [
      'date_ltv' => Yii::_t('statistic.statistic.date_ltv'),
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
    ];
  }

  /**
   * @param $row
   * @param $attribute
   * @param $concatParam
   * @return mixed
   */
  public static function getConcatAttribute($row, $attribute, $concatParam = self::CONCAT_PARAM)
  {
    $concatAttributes = self::CONCAT_ATTRIBUTES;

    $exploded = explode(self::CONCAT_DELIMITER, ArrayHelper::getValue($row, $concatParam, ''));

    if (count($concatAttributes) != count($exploded)) return null;

    return ArrayHelper::getValue(array_combine($concatAttributes, $exploded), $attribute);
  }

  /**
   * @param $row
   * @return float
   */
  public function getAverageRebills($row)
  {
    $countOns = $this->getCountOns($row);
    if (!$countOns) return null;
    return static::getLtvRebills($row) / $countOns;
  }

  /**
   * @return float
   */
  public function getResultAverageRebills()
  {
    $countOns = $this->getResultCountOns();
    if (!$countOns) return null;
    return $this->getResultLtvRebills() / $countOns;
  }

  /**
   * @param $row
   * @return mixed
   */
  public function getCountOns($row)
  {
    $countOns = ArrayHelper::getValue($row, 'count_ons',0);
    $rejectedCountOns = ArrayHelper::getValue($row, 'rejected_count_ons',0);
    $revshareCountOns = ArrayHelper::getValue($row, 'revshare_count_ons',0);

    return $countOns + $rejectedCountOns + $revshareCountOns;
  }

  /**
   * @return mixed
   */
  public function getResultCountOns()
  {
    $sum = 0;
    foreach ($this->_statData as $row) {
      $countOns = ArrayHelper::getValue($row, 'count_ons',0);
      $rejectedCountOns = ArrayHelper::getValue($row, 'rejected_count_ons',0);
      $revshareCountOns = ArrayHelper::getValue($row, 'revshare_count_ons',0);
      $sum +=  $countOns + $rejectedCountOns + $revshareCountOns;
    }
    return $sum;
  }

  /**
   * @param $row
   * @return mixed
   */
  public function getCountOffs($row)
  {
    $countOffs = ArrayHelper::getValue($row, 'count_offs', 0);
    $rejectedCountOffs = ArrayHelper::getValue($row, 'rejected_count_offs', 0);
    $revshareCountOffs = ArrayHelper::getValue($row, 'revshare_count_offs', 0);
    return $countOffs + $revshareCountOffs + $rejectedCountOffs;
  }

  /**
   * @param $row
   * @return mixed
   */
  public function getScopeCountOffs($row)
  {
    $countOffs = ArrayHelper::getValue($row, 'scope_count_offs', 0);
    $rejectedCountOffs = ArrayHelper::getValue($row, 'scope_count_offs_rejected', 0);
    $revshareCountOffs = ArrayHelper::getValue($row, 'scope_count_offs_revshare', 0);
    return $countOffs + $revshareCountOffs + $rejectedCountOffs;
  }

  /**
   * @param $row
   * @return mixed
   */
  public function getCountRebills($row)
  {
    $countRebills = ArrayHelper::getValue($row, 'count_rebills', 0);
    $rejectedCountRebills = ArrayHelper::getValue($row, 'rejected_count_rebills', 0);
    $revshareCountRebills = ArrayHelper::getValue($row, 'revshare_count_rebills', 0);
    return $countRebills + $revshareCountRebills +  $rejectedCountRebills;
  }

  /**
   * @param $row
   * @return mixed
   */
  public function getSumProfit($row)
  {
    $sumProfit = ArrayHelper::getValue($row, 'sum_profit', 0);
    $rejectedSumProfit = ArrayHelper::getValue($row, 'rejected_sum_profit', 0);
    $revshareSumProfit = ArrayHelper::getValue($row, 'revshare_sum_profit', 0);
    return $sumProfit + $revshareSumProfit + $rejectedSumProfit;
  }

  /**
   * @param $row
   * @return float
   */
  public function getAverageProfit($row)
  {
    $countOns = $this->getCountOns($row);
    if (!$countOns) return null;
    return static::getLtvProfit($row) / $countOns;
  }

  /**
   * @return float
   */
  public function getResultAverageProfit()
  {
    $countOns = $this->getResultCountOns();
    if (!$countOns) return null;
    return $this->getResultLtvProfit() / $countOns;
  }

  /**
   * @param StatisticQuery $query
   * @return mixed
   */
  protected static function getQueryAlias(StatisticQuery $query)
  {
    return ArrayHelper::getValue([
      self::MAIN_QUERY => 'st',
      self::SCOPE_QUERY => 'ss',
      self::SCOPE_SOLD_QUERY => 'ss',
      self::SCOPE_CPA_QUERY => 'ss',
      self::REBILLS_SOLD_QUERY => 'ss',
      self::OFFS_SOLD_QUERY => 'ss',
    ], $query->getId());
  }

  /**
   * Считать Aver. CPA относительно всех подписок (Sold+Rejected)?
   * @return bool
   */
  public function getCalcAverCpaAllSubs()
  {
    return $this->type === self::CPA && Yii::$app->getModule('statistic')->getCalcAverCpaAllSubs();
  }

  /**
   * @param array $row
   * @param $currency
   * @return float
   */
  public function getCPR(array $row, $currency)
  {
    $countOnsAttr = $this->getCalcAverCpaAllSubs() ? 'count_ons' : 'sold_count_ons';
    $countOns = ArrayHelper::getValue($row, $countOnsAttr, 0);

    if (!$countOns) {
      return null;
    }
    $soldPartnerProfit = ArrayHelper::getValue($row, 'sold_partner_profit_' . $currency, 0);
    return $soldPartnerProfit / $countOns;
  }

  /**
   * @param $currency
   * @return float
   */
  public function getResultCPR($currency)
  {
    $countOns = 0;
    $countOnsAttr = $this->getCalcAverCpaAllSubs() ? 'count_ons' : 'sold_count_ons';
    foreach ($this->_statData as $row) {
      $countOns += ArrayHelper::getValue($row, $countOnsAttr, 0);
    }

    if (!$countOns) {
      return null;
    }
    $soldPartnerProfit = $this->getResultPartnerProfit('sold_partner_profit_' . $currency);
    return $soldPartnerProfit / $countOns;
  }

  /**
   * @param $field
   * @return float
   */
  public function getResultPartnerProfit($field)
  {
    $currency = str_replace('sold_partner_profit_', '', $field);
    $sum = 0;
    foreach ($this->_statData as $row) {
      $sum += (float) ArrayHelper::getValue($row, 'sold_partner_profit_' . $currency, 0);
    }
    return $sum;
  }

  /**
   * Если пользователь может фильтровать валюты, вернуть текущую. Иначе вернуть валюту пользователя
   * @return string
   */
  protected function getCurrency()
  {
    return $this->canFilterByCurrency() ? $this->currency : self::getUserCurrency();
  }

  /**
   * @inheritdoc
   */
  protected function isCpaVisible($gridRow)
  {
  }

  /**
   * @param $row
   * @return int
   */
  public function getActiveDatabase($row)
  {
    $subs = $this->getCountOns($row);
    $activeSubs = $subs - $this->getScopeCountOffs($row);

    if ($activeSubs > 0 && $subs) {
      return round($activeSubs / $subs, 2);
    }

    return 0;
  }

  /**
   * @return int
   */
  public function getResultActiveDatabase()
  {
    $subs = $this->getResultValue('count_ons');
    $activeSubs = $this->getResultValue('count_ons') - $this->getResultValue('scope_count_offs');

    if ($activeSubs > 0 && $subs) {
      return round($activeSubs / $subs, 2);
    }

    return 0;
  }

  /**
   * @param $row
   * @return int
   */
  public static function getLtvRebills($row)
  {
    return (int)Analytics::getConcatAttribute($row, 'scope_count_rebills', self::CONCAT_PARAM)
      + (int)Analytics::getConcatAttribute($row, 'scope_count_rebills', self::CONCAT_PARAM . '_rejected')
      + (int)Analytics::getConcatAttribute($row, 'scope_count_rebills', self::CONCAT_PARAM . '_revshare');
  }

  /**
   * @return int
   */
  public function getResultLtvRebills()
  {
    $sum = 0;
    foreach ($this->_statData as $row) {
      $sum += (int)Analytics::getConcatAttribute($row, 'scope_count_rebills', self::CONCAT_PARAM)
        + (int)Analytics::getConcatAttribute($row, 'scope_count_rebills', self::CONCAT_PARAM . '_rejected')
        + (int)Analytics::getConcatAttribute($row, 'scope_count_rebills', self::CONCAT_PARAM . '_revshare');
    }
    return $sum;
  }

  /**
   * @param $row
   * @return float
   */
  public static function getLtvProfit($row)
  {
    return (float)Analytics::getConcatAttribute($row, 'scope_sum_profit')
      + (float)Analytics::getConcatAttribute($row, 'scope_sum_profit', self::CONCAT_PARAM . '_rejected')
      + (float)Analytics::getConcatAttribute($row, 'scope_sum_profit', self::CONCAT_PARAM . '_revshare');
  }

  /**
   * @return float
   */
  public function getResultLtvProfit()
  {
    $sum = 0;
    foreach ($this->_statData as $row) {
      $sum += (float)Analytics::getConcatAttribute($row, 'scope_sum_profit')
        + (float)Analytics::getConcatAttribute($row, 'scope_sum_profit', self::CONCAT_PARAM . '_rejected')
        + (float)Analytics::getConcatAttribute($row, 'scope_sum_profit', self::CONCAT_PARAM . '_revshare');
    }
    return $sum;
  }

  /**
   * @param $statArray
   * @return array
   */
  protected function indexBy($statArray)
  {
    return ArrayHelper::index($statArray, function ($statRecord) {
      return strtr('date', $statRecord);
    });
  }

  /**
   * Типы профита для селекта
   * @return array
   */
  public function getProfitTypeFilter()
  {
    return [
      self::ALL => Yii::_t('statistic.statistic.all'),
      self::REVSHARE => Yii::_t('statistic.analytics.revshare'),
      self::CPA => Yii::_t('statistic.analytics.sold') . '+' . Yii::_t('statistic.analytics.rejected'),
      self::SOLD => Yii::_t('statistic.analytics.sold'),
      self::REJECTED => Yii::_t('statistic.analytics.rejected'),
    ];
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
      case 'count_ons':
        foreach ($this->_statData as $row) {
          $sum += $this->getCountOns($row);
        }
        break;
      case 'count_offs':
        foreach ($this->_statData as $row) {
          $sum += $this->getCountOffs($row);
        }
        break;
      case 'count_rebills':
        foreach ($this->_statData as $row) {
          $sum += $this->getCountRebills($row);
        }
        break;
      case 'sum_profit':
        foreach ($this->_statData as $row) {
          $sum += $this->getSumProfit($row);
        }
        break;
      case 'ltv_rebills':
        foreach ($this->_statData as $row) {
          $sum += $this::getLtvRebills($row);
        }
        break;
      case 'active_database':
        $sum = $this->getResultActiveDatabase();
        break;
      case 'scope_count_offs':
        foreach ($this->_statData as $row) {
          $sum += $this->getScopeCountOffs($row);
        }
        break;
      case 'ltv_profit':
        foreach ($this->_statData as $row) {
          $sum += $this::getLtvProfit($row);
        }
        break;
      case 'avg_rebills':
        $sum = $this->getResultAverageRebills();
        break;
      case 'avg_profit':
        $sum = $this->getResultAverageProfit();
        break;
      case 'sold_partner_profit_rub':
      case 'sold_partner_profit_usd':
      case 'sold_partner_profit_eur':
        $sum = $this->getResultPartnerProfit($field);
        break;
      case 'cpr_rub':
      case 'cpr_usd':
      case 'cpr_eur':
        $currency = str_replace('cpr_', '', $field);
        $sum = $this->getResultCPR($currency);
        break;
      case 'roi_rub':
      case 'roi_usd':
      case 'roi_eur':
        $currency = str_replace('roi_', '', $field);
        $sum = $this->calcROI(
          $this->getResultValue('sold_partner_profit_' . $currency),
          $this->getResultValue('ltv_profit')
        );
        break;
      default:
        foreach ($this->_statData as $row) {
          $sum += floatval(ArrayHelper::getValue($row, $field, 0));
        }
    }

    return $this->_fieldResults[$field] = $sum;
  }

  /**
   * @param array $row
   * @param $currency
   * @return float
   */
  public function getROI(array $row, $currency)
  {
    return $this->calcROI(ArrayHelper::getValue($row, 'sold_partner_profit_' . $currency, 0), self::getLtvProfit($row));
  }

  /**
   * @param float $expenses расход
   * @param float $profit доход
   * @return float|int расчитанный рои
   */
  protected function calcROI($expenses, $profit)
  {
    if (!$expenses) {
      return 0;
    }
    return $profit / $expenses - 1;
  }
}
