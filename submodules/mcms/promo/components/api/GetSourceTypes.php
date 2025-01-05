<?php

namespace mcms\promo\components\api;

use mcms\common\module\api\ApiResult;
use mcms\promo\models\Source;

class GetSourceTypes extends ApiResult
{

  public function init($params = [])
  {

  }

  public function getResult()
  {
    return Source::getSourceTypes();
  }

}


