<?php

namespace mcms\promo\components\api;

use mcms\common\module\api\ApiResult;
use mcms\promo\models\search\LandingCategorySearch;

class LandingCategoryList extends ApiResult
{

  public function init($params = [])
  {
    $this->setResultTypeDataProvider();
    $this->prepareDataProvider(new LandingCategorySearch(), $params);
  }
}