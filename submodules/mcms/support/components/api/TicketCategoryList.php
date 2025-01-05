<?php
namespace mcms\support\components\api;

use mcms\common\module\api\ApiResult;
use mcms\support\models\search\SupportCategorySearch;

class TicketCategoryList extends ApiResult
{

  public function init($params = [])
  {
    $this->setResultTypeMap();
    $this->prepareDataProvider(new SupportCategorySearch(['is_disabled' => 0]), $params);
  }

}