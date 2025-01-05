<?php

namespace mcms\promo\components\api;

use mcms\common\helpers\ArrayHelper;
use mcms\common\module\api\ApiResult;
use mcms\common\module\api\join\Query;
use mcms\promo\models\search\StreamSearch;
use mcms\promo\models\Stream;
use mcms\statistic\models\mysql\StatFilter;
use Yii;

class StreamList extends ApiResult
{

  /**
   * @param array $params
   */
  public function init($params = [])
  {
    $this->setResultTypeMap();
    $searchModel = new StreamSearch();

    $statFilters = ArrayHelper::getValue($params, 'statFilters');

    if ($statFilters) {
      $searchModel->scenario = $searchModel::SCENARIO_STAT_FILTERS;
    }
    $this->prepareDataProvider($searchModel, $params);

    if ($statFilters) {
      // убираем пагинацию, чтобы не было лишнего запроса COUNT(*)
      $this->dataProvider->setPagination(false);
      Yii::$app->getModule('statistic')->api('statFilters')->filterStreams($this->dataProvider->query, ArrayHelper::getValue($params, 'statFiltersUser'));
    }
  }

  /**
   * @param array $params
   * @return Stream
   */
  public function getModel(array $params = [])
  {
    return new Stream($params);
  }

  /**
   * @param Query $query
   */
  public function join(Query &$query)
  {
    $query
      ->setRightTable('streams')
      ->setRightTableColumn('id')
      ->join()
    ;
  }

}