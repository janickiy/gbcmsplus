<?php


namespace mcms\promo\components\api;


use mcms\common\helpers\ArrayHelper;
use mcms\common\module\api\ApiResult;
use mcms\common\module\api\join\Query;
use mcms\promo\models\search\ProviderSearch;
use Yii;

class ProviderList extends ApiResult
{

  public function init($params = [])
  {
    $this->prepareDataProvider(new ProviderSearch(), $params);

    $statFilters = ArrayHelper::getValue($params, 'statFilters');

    if ($statFilters) {
      // убираем пагинацию, чтобы не было лишнего запроса COUNT(*)
      $this->dataProvider->setPagination(false);
      Yii::$app->getModule('statistic')->api('statFilters')->filterProviders($this->dataProvider->query, ArrayHelper::getValue($params, 'statFiltersUser'));
    }
  }

  public function join(Query &$query)
  {
    $query
      ->setRightTable('providers')
      ->setRightTableColumn('id')
      ->join()
    ;
  }
}