<?php

namespace mcms\statistic\models\resellerStatistic;

use mcms\promo\models\Country;
use mcms\statistic\components\ResellerProfits;
use rgk\utils\components\CurrenciesValues;
use Yii;
use yii\base\Model;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * Модель расхолда для одной строки
 * Class UnholdPlanSearch
 * @package mcms\statistic\models\resellerStatistic
 */
class UnholdPlanSearch extends Model implements UnholdPlanSearchInterface
{
  /**
   * фильтр
   * @var  string
   */
  public $holdDateFrom;
  /**
   * фильтр
   * @var  string
   */
  public $holdDateTo;
  /**
   * фильтр
   * @var  string
   */
  public $unholdDateFrom;
  /**
   * фильтр
   * @var  string
   */
  public $unholdDateTo;
  /** @var string|false Исключить результаты с нулевыми значениями в определенной валюте */
  public $filterEmptyByCurrency = false;

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['holdDateFrom', 'holdDateTo', 'unholdDateFrom', 'unholdDateTo'], 'safe'],
    ];
  }

  /**
   * @param array $requestData
   * @return UnholdPlan
   */
  public function search(array $requestData)
  {
    $model = (new UnholdPlan());

    $this->load($requestData);
    if (!$this->validate()) return $model;

    $query = (new Query())
      ->select([
        'unhold_date',
        'country_id',
        'unhold_rub' => new Expression('SUM(profit_rub)'),
        'unhold_usd' => new Expression('SUM(profit_usd)'),
        'unhold_eur' => new Expression('SUM(profit_eur)'),
      ])
      ->from(['st' => ResellerProfits::tableName()])
      ->groupBy(['unhold_date', 'country_id'])
      ->orderBy(['unhold_date' => SORT_DESC, 'country_id' => SORT_ASC])
      ->andFilterWhere(['>=', 'date', $this->holdDateFrom])
      ->andFilterWhere(['<=', 'date', $this->holdDateTo])
      ->andFilterWhere(['>=', 'unhold_date', $this->unholdDateFrom])
      ->andFilterWhere(['<=', 'unhold_date', $this->unholdDateTo]);

    foreach ($query->all() as $dbItem) {
      // Если нет значения в указанной валюте, не добавляем в результат
      if ($this->filterEmptyByCurrency
        && ArrayHelper::getValue($dbItem, 'unhold_' . strtolower($this->filterEmptyByCurrency), 0) == 0) {
        continue;
      }

      $values = (new CurrenciesValues())
        ->setValue('rub', ArrayHelper::getValue($dbItem, 'unhold_rub', 0))
        ->setValue('usd', ArrayHelper::getValue($dbItem, 'unhold_usd', 0))
        ->setValue('eur', ArrayHelper::getValue($dbItem, 'unhold_eur', 0));
      $model->addValue($dbItem['unhold_date'], Country::findOneCached($dbItem['country_id']), $values);
    }

    return $model;
  }
}