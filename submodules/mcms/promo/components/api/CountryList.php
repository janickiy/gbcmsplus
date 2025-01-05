<?php

namespace mcms\promo\components\api;

use mcms\common\helpers\ArrayHelper;
use mcms\common\module\api\ApiResult;
use mcms\common\module\api\join\Query as JoinQuery;
use mcms\promo\models\Country;
use mcms\promo\models\search\CountrySearch;
use mcms\statistic\models\mysql\StatFilter;
use Yii;
use yii\db\Query;

/**
 * Class CountryList
 * @package mcms\promo\components\api
 */
class CountryList extends ApiResult
{
  /** @var  CountrySearch */
  protected $searchModel;

  /**
   * @inheritdoc
   */
  public function init($params = [])
  {
    $this->searchModel = new CountrySearch();

    $statFilters = ArrayHelper::getValue($params, 'statFilters');
    $currency = ArrayHelper::getValue($params, 'currency');

    if ($statFilters) {
      $this->searchModel->scenario = CountrySearch::SCENARIO_STAT_FILTERS;
    }

    //если передали валюту достаем только страны с этой валютой указанные в конфиге
    $addCountryIds = $this->getCountryIdsFilteredByCurrency($currency);

    if (!empty($addCountryIds)) {
      $params['conditions']['id'] = $addCountryIds;
    }

    $this->prepareDataProvider($this->searchModel, $params);
    $this->setResultTypeDataProvider();

    if ($statFilters) {
      // убираем пагинацию, чтобы не было лишнего запроса COUNT(*)
      $this->dataProvider->setPagination(false);
      Yii::$app->getModule('statistic')->api('statFilters')->filterCountries($this->dataProvider->query, ArrayHelper::getValue($params, 'statFiltersUser'));
    }

  }

  /**
   * @return CountrySearch
   */
  public function getSearchModel()
  {
    return $this->searchModel;
  }

  /**
   * @param JoinQuery $query
   */
  public function join(JoinQuery &$query)
  {
    $query
      ->setRightTable('countries')
      ->setRightTableColumn('id')
      ->join()
    ;
  }

  /**
   * TRICKY такой же код в @see StatFilter
   * Получаем список айдишников стран для валюты.
   * Сюда входят страны у которых непосредственно указана нужная валюта
   * А также страны, у которых эта валюта была когда-то задана (смотрем по таблице country_currency_log)
   * Пример где это понадобилось: в фильтрах статы надо было показать BY и KZ  обеих валютах: в руб и евро
   * @param $currency
   * @return int[]
   */
  private function getCountryIdsFilteredByCurrency($currency)
  {
    if (!$currency) {
      return [];
    }
    $currentCurrencyCountryIds = Country::find()
      ->select('id')
      ->andWhere(['currency' => $currency])
      ->column();

    $oldCurrencyCountryId = (new Query())
      ->select('country_id', 'DISTINCT')
      ->from('country_currency_log')
      ->andWhere(['currency' => $currency])
      ->column();

    return ArrayHelper::merge($currentCurrencyCountryIds, $oldCurrencyCountryId);
  }
}
