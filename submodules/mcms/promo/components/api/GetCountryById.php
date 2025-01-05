<?php

namespace mcms\promo\components\api;

use mcms\common\module\api\ApiResult;
use mcms\promo\models\Country;
use mcms\common\helpers\ArrayHelper;

class GetCountryById extends ApiResult
{
  protected $countryId;

  public function init($params = [])
  {
    $this->countryId = ArrayHelper::getValue($params, 'countryId');

    if (!$this->countryId) $this->addError('countryId is not set');
  }

  public function getResult()
  {
    return Country::findOne($this->countryId);
  }

}