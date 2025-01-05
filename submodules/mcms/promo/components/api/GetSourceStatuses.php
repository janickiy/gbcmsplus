<?php

namespace mcms\promo\components\api;

use Yii;
use mcms\common\module\api\ApiResult;
use mcms\promo\models\Source;


class GetSourceStatuses extends ApiResult
{

  public function init($params = []) { }

  public function getResult()
  {
    return Source::getStatuses();
  }

}