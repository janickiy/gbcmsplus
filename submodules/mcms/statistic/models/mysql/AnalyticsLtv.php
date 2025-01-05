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
 * Class AnalyticsRebills
 * @package mcms\statistic\models\mysql
 */
class AnalyticsLtv extends Analytics
{
  const CUMULATIVE_OFFS_QUERY = 'cumulative_offs';
  const COUNT_ONS_QUERY = 'count_ons';

  const STATISTIC_NAME = 'analytics-ltv';

  public $ltv_depth_from;
  public $ltv_depth_to;

  /** @var  array кэш для каждой ячейки в строке Итого, чтобы не ситать каждый раз заново */
  private $_fieldResults;

  /**
   * @param StatisticQuery $query
   * @return string
   */
  protected static function getQueryAlias(StatisticQuery $query)
  {
    return 'st';
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return array_merge([
      [['ltv_depth_from', 'ltv_depth_to'], 'safe']
    ], parent::rules());
  }

  /**
   * @return array
   */
  public function attributeLabels()
  {
    return array_merge(parent::attributeLabels(), [
      'ltv_depth_from' => Yii::_t('statistic.analytics.date_on_from'),
      'ltv_depth_to' => Yii::_t('statistic.analytics.date_on_to'),
    ]);
  }

  /**
   * Перевод для колонок грида
   * @return array
   */
  public function gridColumnLabels()
  {
    return [
      'date' => Yii::_t('statistic.statistic.date'),
      'count_ons' => Yii::_t('statistic.analytics.count_ons'),
      'count_offs' => Yii::_t('statistic.analytics.count_offs'),
      'alive_ons' => Yii::_t('statistic.analytics.alive_ons'),
      'count_rebills' => Yii::_t('statistic.analytics.count_rebills'),
      'sum_profit' => Yii::_t('statistic.analytics.sum_profit', ['currency' => strtoupper($this->getCurrency())]),
    ];
  }

  /**
   * @inheritdoc
   */
  public function getStatisticGroup()
  {
    $this->handleOpenCloseFilters();

    $currency = $this->getCurrency();
    $query = $this->getStatisticQuery($currency);
    $countOnsQuery = $this->getCountOnsQuery();
    $cumulativeOffsQuery = $this->getCumulativeOffsQuery();

    $this->handleFilters($query);
    $this->handleFilters($countOnsQuery);
    $this->handleFilters($cumulativeOffsQuery);

    $data = ArrayHelper::merge(
      $this->indexBy($query->each()),
      $this->indexBy($countOnsQuery->each()),
      $this->indexBy($cumulativeOffsQuery->each())
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
          'cumulative_offs',
          'count_rebills',
          'sum_profit',
        ]
      ],
    ]);
  }

  /**
   * @param $currency
   * @return StatisticQuery
   */
  public function getStatisticQuery($currency)
  {
    return (new StatisticQuery())
      ->setId(self::MAIN_QUERY)
      ->select([
        'date' => 'st.date_on',
        'count_offs_revshare' => 'SUM(count_offs_revshare)',
        'count_offs_sold' => 'SUM(count_offs_sold)',
        'count_offs_rejected' => 'SUM(count_offs_rejected)',
        'count_rebills_revshare' => 'SUM(count_rebills_revshare)',
        'count_rebills_sold' => 'SUM(count_rebills_sold)',
        'count_rebills_rejected' => 'SUM(count_rebills_rejected)',
        "sum_profit_revshare" => "SUM(profit_{$currency}_revshare)",
        "sum_profit_sold" => "SUM(profit_{$currency}_sold)",
        "sum_profit_rejected" => "SUM(profit_{$currency}_rejected)",
      ])
      ->from('statistic_analytics st')
      ->groupBy('date_on');
  }

  /**
   * @return StatisticQuery
   */
  public function getCountOnsQuery()
  {
    return (new StatisticQuery())
      ->setId(self::COUNT_ONS_QUERY)
      ->select([
        'date' => 'st.date_on',
        'count_ons_revshare' => 'SUM(count_ons_revshare)',
        'count_ons_sold' => 'SUM(count_ons_sold)',
        'count_ons_rejected' => 'SUM(count_ons_rejected)',
      ])
      ->from('statistic_analytics st')
      ->andWhere('st.date = st.date_on')
      ->groupBy('date_on');
  }

  /**
   * @return StatisticQuery
   */
  public function getCumulativeOffsQuery()
  {
    return (new StatisticQuery())
      ->setId(self::CUMULATIVE_OFFS_QUERY)
      ->select([
        'date' => 'st.date_on',
        'cumulative_offs_revshare' => 'SUM(count_offs_revshare)',
        'cumulative_offs_sold' => 'SUM(count_offs_sold)',
        'cumulative_offs_rejected' => 'SUM(count_offs_rejected)',
      ])
      ->from('statistic_analytics st')
      ->groupBy('date_on');
  }

  /**
   * @inheritdoc
   */
  public function handleFilters(Query &$query)
  {
    /** @var $query StatisticQuery */
    $alias = self::getQueryAlias($query);

    $query
      ->andFilterWhere(['>=', "$alias.date_on", $this->formatDateDB($this->start_date)])
      ->andFilterWhere(['<=', "$alias.date_on", $this->formatDateDB($this->end_date)])
      ->andFilterWhere(['<=', "$alias.date_diff", $this->ltv_depth_to]);

    if ($query->getId() === self::MAIN_QUERY) {
      // для накопительной суммы отписок не используем минимальную глубину
      $query->andFilterWhere(['>=', "$alias.date_diff", $this->ltv_depth_from]);
    }

    $this->handleBaseFilters($query, $alias);
  }

  /**
   * @param StatisticQuery $query
   * @param string $alias
   */
  public function handleBaseFilters($query, $alias)
  {
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
      $query->andFilterWhere(["$alias.source_id" => $this->sources]);
    }

    if ($this->canFilterByPlatform()) {
      $query->andFilterWhere(["$alias.platform_id" => $this->platforms]);
    }

    if ($this->canFilterByStreams()) {
      $query->andFilterWhere(["$alias.stream_id" => $this->streams]);
    }

    if ($this->canFilterByCountries()) {
      $query->andFilterWhere(["$alias.country_id" => $this->countries]);
    }

    if ($this->canFilterByUsers()) {
      $query->andFilterWhere(["$alias.user_id" => $this->users]);
    }

    if ($this->canFilterByLandingPayTypes()) {
      $query->andFilterWhere(["$alias.landing_pay_type_id" => $this->landing_pay_types]);
    }

//    if ($this->canFilterByCurrency()) {
//      $query->andWhere(["$alias.currency_id" => $this->allCurrencies[$this->currency]]);
//    }
  }

  /**
   * @param $row
   * @return int
   */
  public function getCountOns($row)
  {
    return $this->getSumType($row, 'count_ons');
  }

  /**
   * @param $row
   * @return mixed
   */
  public function getCountOffs($row)
  {
    return $this->getSumType($row, 'count_offs');
  }

  /**
   * @param $row
   * @return mixed
   */
  public function getAliveOns($row)
  {
    $sum = $this->getSumType($row, 'cumulative_offs');
    $countOns = $this->getSumType($row, 'count_ons');

    return $countOns ? ($countOns - $sum) / $countOns : 0;
  }

  /**
   * @param $row
   * @param null $field
   * @return mixed
   */
  public function getCountRebills($row, $field = null)
  {
    return $this->getSumType($row, 'count_rebills');
  }

  /**
   * @param $row
   * @param null $field
   * @return mixed
   */
  public function getSumProfit($row, $field = null)
  {
    return $this->getSumType($row, 'sum_profit');
  }

  /**
   * @param $field
   * @return float|int
   */
  public function getResultValue($field)
  {
    if (isset($this->_fieldResults[$field])) {
      return $this->_fieldResults[$field];
    }

    is_null($this->_statData) && $this->getStatisticGroup();

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
      case 'alive_ons':
        foreach ($this->_statData as $row) {
          $sum += $this->getAliveOns($row);
        }
        // получаем среднее
        $sum = count($this->_statData)
          ? round($sum / count($this->_statData), 4)
          : 0;
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
      default:
        foreach ($this->_statData as $row) {
          $sum += floatval(ArrayHelper::getValue($row, $field, 0));
        }
    }

    return $this->_fieldResults[$field] = $sum;
  }

  /**
   * @param array $row
   * @param string $field
   * @return int
   */
  protected function getSumType($row, $field)
  {
    switch ($this->type) {
      case static::REVSHARE:
        $sum = ArrayHelper::getValue($row, $field . '_revshare', 0);
        break;
      case static::CPA:
        $sum = ArrayHelper::getValue($row, $field . '_sold', 0)
          + ArrayHelper::getValue($row, $field . '_rejected', 0);
        break;
      case static::SOLD:
        $sum = ArrayHelper::getValue($row, $field . '_sold', 0);
        break;
      case static::REJECTED:
        $sum = ArrayHelper::getValue($row, $field . '_rejected', 0);
        break;
      default:
        $sum = ArrayHelper::getValue($row, $field . '_revshare', 0)
          + ArrayHelper::getValue($row, $field . '_sold', 0)
          + ArrayHelper::getValue($row, $field . '_rejected', 0);
        break;
    }

    return $sum;
  }
}
