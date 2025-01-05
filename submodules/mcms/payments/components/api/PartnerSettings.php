<?php

namespace mcms\payments\components\api;

use mcms\common\module\api\ApiResult;

class PartnerSettings extends ApiResult {

  function init($params = [])
  {
    $this->prepareWidget(\mcms\payments\components\widgets\PartnerSettings::class, $params);
  }
}