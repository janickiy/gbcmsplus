<?php

namespace mcms\payments\components\api;

use mcms\common\module\api\ApiResult;

class UserSettings extends ApiResult {

  function init($params = [])
  {
    $this->prepareWidget(\mcms\payments\components\widgets\UserSettings::class, $params);
  }
}