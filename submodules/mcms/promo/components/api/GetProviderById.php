<?php

namespace mcms\promo\components\api;

use mcms\common\module\api\ApiResult;
use mcms\promo\models\Provider;
use mcms\common\helpers\ArrayHelper;

class GetProviderById extends ApiResult
{
  protected $providerId;

  public function init($params = [])
  {
    $this->providerId = ArrayHelper::getValue($params, 'providerId');

    if (!$this->providerId) $this->addError('providerId is not set');
  }

  public function getResult()
  {
    return Provider::findOne($this->providerId);
  }

}