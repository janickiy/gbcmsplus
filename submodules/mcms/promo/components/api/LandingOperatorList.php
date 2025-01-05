<?php

namespace mcms\promo\components\api;

use mcms\common\module\api\ApiResult;
use mcms\common\helpers\ArrayHelper;
use mcms\promo\models\search\LandingOperatorSearch;
use Yii;

class LandingOperatorList extends ApiResult
{

  public function init($params = [])
  {
    $this->setResultTypeDataProvider();
    $model = new LandingOperatorSearch();

    $statFilters = ArrayHelper::getValue($params, 'statFilters');

    if ($statFilters) {
      $model->scenario = $model::SCENARIO_STAT_FILTERS;
    }

    if (in_array('orderByCountry', $params)) {
      $model->orderByCountry = true;
    }

    if (isset($params['isOrderLandingsDirectionDesc'])) {
      $model->isOrderLandingsDirectionDesc = $params['isOrderLandingsDirectionDesc'];
    }

    if (isset($params['isOrderLandingsOpenFirst'])) {
      $model->isOrderLandingsOpenFirst = $params['isOrderLandingsOpenFirst'];
    }

    if (isset($params['onlyActive'])) {
      $model->onlyActive = $params['onlyActive'];
    }

    $this->prepareDataProvider($model, $params);

    if ($statFilters) {
      // убираем пагинацию, чтобы не было лишнего запроса COUNT(*)
      $this->dataProvider->setPagination(false);
      Yii::$app->getModule('statistic')->api('statFilters')->filterLandingOperator($this->dataProvider->query, ArrayHelper::getValue($params, 'statFiltersUser'));
    }
  }
}