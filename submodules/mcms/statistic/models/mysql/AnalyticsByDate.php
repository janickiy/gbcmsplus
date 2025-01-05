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
class AnalyticsByDate extends AnalyticsLtv
{
  const COUNT_ONS_QUERY = 'count_ons';

  const STATISTIC_NAME = 'analytics-by-dadte';

  public $date_on_from;
  public $date_on_to;

  /**
   * кэш статистики
   * @var
   */
  protected $_statData;

  protected $_countOns = [];

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
  public function init()
  {
    parent::init();

    if (!$this->date_on_from) {
      $this->date_on_from = $this->start_date;
    }

    if (!$this->date_on_to) {
      $this->date_on_to = $this->end_date;
    }
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return array_merge([
      [['is_visible_to_partner'], 'integer'],
      [['type', 'date_on_from', 'date_on_to', 'landings', 'sources', 'operators', 'platforms', 'streams', 'providers', 'countries', 'users', 'landing_pay_types'], 'safe']
    ], parent::rules());
  }

  /**
   * @return array
   */
  public function attributeLabels()
  {
    return array_merge(parent::attributeLabels(), [
      'date_on_from' => Yii::_t('statistic.analytics.date_on_from'),
      'date_on_to' => Yii::_t('statistic.analytics.date_on_to'),
    ]);
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

    $this->handleFilters($query);
    $this->handleFilters($countOnsQuery);

    $data = $this->indexBy($query->each());
    $this->_statData = $data;
    $this->_countOns = $this->indexBy($countOnsQuery->each());

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
   * @param $currency
   * @return StatisticQuery
   */
  public function getStatisticQuery($currency)
  {
    return (new StatisticQuery())
      ->setId(self::MAIN_QUERY)
      ->select([
        'date' => 'st.date',
        'count_ons_revshare' => 'SUM(count_ons_revshare)',
        'count_ons_sold' => 'SUM(count_ons_sold)',
        'count_ons_rejected' => 'SUM(count_ons_rejected)',
        'count_offs_revshare' => 'SUM(count_offs_revshare)',
        'count_offs_sold' => 'SUM(count_offs_sold)',
        'count_offs_rejected' => 'SUM(count_offs_rejected)',
        'cumulative_offs_revshare' => 'SUM(cumulative_offs_revshare)',
        'cumulative_offs_sold' => 'SUM(cumulative_offs_sold)',
        'cumulative_offs_rejected' => 'SUM(cumulative_offs_rejected)',
        'count_rebills_revshare' => 'SUM(count_rebills_revshare)',
        'count_rebills_sold' => 'SUM(count_rebills_sold)',
        'count_rebills_rejected' => 'SUM(count_rebills_rejected)',
        "sum_profit_revshare" => "SUM(profit_{$currency}_revshare)",
        "sum_profit_sold" => "SUM(profit_{$currency}_sold)",
        "sum_profit_rejected" => "SUM(profit_{$currency}_rejected)",
        'date_diff',
      ])
      ->from('statistic_analytics st')
//      ->having('count_rebills_revshare > 0 || count_rebills_sold > 0 || count_rebills_rejected > 0')
      ->groupBy('date');
  }

  /**
   * @return StatisticQuery
   */
  public function getCountOnsQuery()
  {
    return (new StatisticQuery())
      ->setId(self::COUNT_ONS_QUERY)
      ->select([
        'date' => 'date_on',
        'count_ons_revshare' => 'SUM(count_ons_revshare)',
        'count_ons_sold' => 'SUM(count_ons_sold)',
        'count_ons_rejected' => 'SUM(count_ons_rejected)',
      ])
      ->from('statistic_analytics st')
      ->where('date_on = date')
      ->groupBy('date_on');
  }

  /**
   * @inheritdoc
   */
  public function handleFilters(Query &$query)
  {
    /** @var $query StatisticQuery */
    $alias = self::getQueryAlias($query);

    $dateOnFrom = $this->date_on_from ? $this->formatDateDB($this->date_on_from) : null;
    $dateOnTo = $this->date_on_to
      ? $this->formatDateDB($this->date_on_to)
      : $this->formatDateDB($this->end_date);

    $query
      ->andFilterWhere(['>=', "$alias.date_on", $dateOnFrom])
      ->andFilterWhere(['<=', "$alias.date_on", $dateOnTo]);

    if ($query->getId() !== self::COUNT_ONS_QUERY) {
      $query
        ->andFilterWhere(['>=', "$alias.date", $this->formatDateDB($this->start_date)])
        ->andFilterWhere(['<=', "$alias.date", $this->formatDateDB($this->end_date)]);
    }


    $this->handleBaseFilters($query, $alias);
  }

  /**
   * @param $row
   * @return int
   */
  public function getCountOns($row)
  {
    return isset($this->_countOns[$row['date']])
      ? $this->getSumType($row, 'count_ons')
      : 0;
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
        foreach ($this->_countOns as $row) {
          $sum += $this->getSumType($row, 'count_ons');
        }
        break;
      case 'count_offs':
        foreach ($this->_statData as $row) {
          $sum += $this->getCountOffs($row);
        }
        break;
      case 'alive_ons':
        $row = end($this->_statData);
        $sum = $this->getAliveOns($row);
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
}
