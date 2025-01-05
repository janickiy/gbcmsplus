<?php

namespace mcms\promo\components\api;

use mcms\common\module\api\ApiResult;
use mcms\common\module\api\join\Query;
use mcms\promo\models\search\LandingSearch;

class LandingList extends ApiResult
{

  public function init($params = [])
  {
    $this->setResultTypeDataProvider();
    $this->prepareDataProvider(new LandingSearch(), $params);
  }

  public function join(Query &$query)
  {
    $query
      ->setRightTable('landings')
      ->setRightTableColumn('id')
      ->join()
    ;
  }
}