<?php


namespace mcms\promo\components\api;


use mcms\common\helpers\ArrayHelper;
use mcms\common\module\api\ApiResult;
use mcms\common\module\api\join\Query;
use mcms\promo\models\LandingPayType;
use mcms\promo\models\search\LandingPayTypeSearch;
use Yii;

class LandingPayTypeList extends ApiResult
{

  public function init($params = [])
  {
    $model = new LandingPayTypeSearch();

    $statFilters = ArrayHelper::getValue($params, 'statFilters');

    if ($statFilters) {
      $model->scenario = $model::SCENARIO_STAT_FILTERS;
    }
    $this->prepareDataProvider($model, $params);

    if ($statFilters) {
      // убираем пагинацию, чтобы не было лишнего запроса COUNT(*)
      $this->dataProvider->setPagination(false);
      Yii::$app->getModule('statistic')->api('statFilters')->filterPayType($this->dataProvider->query, ArrayHelper::getValue($params, 'statFiltersUser'));
    }
  }

  public function join(Query &$query)
  {
    $query
      ->setRightTable(LandingPayType::tableName())
      ->setRightTableColumn('id')
      ->join()
    ;
  }
}