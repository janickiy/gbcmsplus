<?php
namespace mcms\pages\components\api;

use mcms\common\module\api\ApiResult;
use mcms\pages\models\PageSearch;

class PagesList extends ApiResult
{
  public function init($params = [])
  {
    $this->prepareDataProvider(new PageSearch(), $params);
  }
}