<?php

namespace mcms\statistic\components\data_providers;

use mcms\statistic\components\StatisticQuery;
use mcms\statistic\models\mysql\Analytics;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\db\QueryInterface;

/**
 * Класс нужен для того, чтобы исключить подзапрос из подсчета кол-ва строк (для пагинации).
 * Иначе этот COUNT() работает долго
 *
 * Class AnalyticsDataProvider
 * @package mcms\statistic\components\data_providers
 */
class AnalyticsDataProvider extends ActiveDataProvider
{
  /** @var  StatisticQuery */
  public $mainQuery;
  /** @var  StatisticQuery */
  public $scopeQuery;
  /** @var  StatisticQuery */
  public $scopeOffsQuery;

  public function init()
  {
    parent::init();
    $mainQuery = clone $this->mainQuery;
    $mainQuery->addSelect([
      Analytics::CONCAT_PARAM => $this->scopeQuery
    ]);
    $mainQuery->addSelect([
      Analytics::SCOPE_OFF_PARAM => $this->scopeOffsQuery
    ]);
    $this->query = $mainQuery;
  }


  /**
   * @inheritdoc
   */
  protected function prepareTotalCount()
  {
    if (!$this->mainQuery instanceof QueryInterface) {
      throw new InvalidConfigException('The "query" property must be an instance of a class that implements the QueryInterface e.g. yii\db\Query or its subclasses.');
    }
    $query = clone $this->mainQuery;
    return (int) $query->select([new Expression('1')])->limit(-1)->offset(-1)->orderBy([])->count('*', $this->db);
  }
}