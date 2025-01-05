<?php


namespace mcms\promo\components\api;


use mcms\common\helpers\ArrayHelper;
use mcms\common\module\api\ApiResult;
use mcms\common\module\api\join\Query;
use mcms\promo\models\search\OperatorSearch;
use Yii;

class OperatorList extends ApiResult
{

  public function init($params = [])
  {
    $model = new OperatorSearch();

    $statFilters = ArrayHelper::getValue($params, 'statFilters');

    if ($statFilters) {
      $model->scenario = $model::SCENARIO_STAT_FILTERS;
    }

    if (in_array('orderByCountry', $params)) {
      $model->orderByCountry = true;
    }

    if (isset($params) && array_key_exists('countriesIds', $params)) {
      $model->countriesIds = $params['countriesIds'];
    }

    $this->prepareDataProvider($model, $params);

    if ($statFilters) {
      // убираем пагинацию, чтобы не было лишнего запроса COUNT(*)
      $this->dataProvider->setPagination(false);
      Yii::$app->getModule('statistic')->api('statFilters')->filterOperators($this->dataProvider->query, ArrayHelper::getValue($params, 'statFiltersUser'));
    }
  }

  public function join(Query &$query)
  {
    $query
      ->setRightTable('operators')
      ->setRightTableColumn('id')
      ->join()
    ;
  }
}